<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TreatmentPlanService;
use App\Services\CaseService;
use App\Mail\NewCaseForPricingNotification;
use App\Mail\PriceSetNotification;
use App\Mail\PriceAcceptedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminPriceManagerController extends Controller
{
    protected $treatmentPlanService;
    protected $caseService;

    public function __construct(TreatmentPlanService $treatmentPlanService, CaseService $caseService)
    {
        $this->treatmentPlanService = $treatmentPlanService;
        $this->caseService = $caseService;
    }

    /**
     * Show the price manager dashboard
     */
    public function index()
    {
        try {
            $perPage = 10;

            // Cases waiting for price (no price set)
            $pendingPricing = \App\Models\CasePatient::with(['patient', 'doctor', 'technician'])
                ->whereIn('status', ['pending', 'in_planning', 'approval'])
                ->whereNull('price')
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'pending_page')
                ->withQueryString();

            // Cases priced, waiting for doctor acceptance
            $pricedCases = \App\Models\CasePatient::with(['patient', 'doctor', 'admin', 'invoices'])
                ->whereIn('status', ['pending', 'in_planning', 'approval'])
                ->whereNotNull('price')
                ->orderByDesc('price_added_at')
                ->paginate($perPage, ['*'], 'priced_page')
                ->withQueryString();

            // Historical priced and approved cases
            $historicalPricedCases = \App\Models\CasePatient::with(['patient', 'doctor', 'admin', 'invoices'])
                ->whereNotNull('price')
                ->whereIn('status', ['approval', 'in_production', 'shipped', 'in_planning'])
                ->orderByDesc('price_added_at')
                ->paginate($perPage, ['*'], 'history_page')
                ->withQueryString();

            return view('admin.price_manager.index', compact('pendingPricing', 'pricedCases', 'historicalPricedCases'));
        } catch (\Exception $e) {
            Log::error('Error in AdminPriceManagerController@index: ' . $e->getMessage());
            return back()->with('error', 'Failed to load price manager data');
        }
    }

    /**
     * Show form to add price to a case
     */
    public function showAddPrice($caseId)
    {
        try {
            $case = \App\Models\CasePatient::with(['patient', 'doctor', 'technician', 'treatmentType'])->findOrFail($caseId);
            
            if ($case->price) {
                return back()->with('error', 'Price has already been added to this case');
            }

            // No need to check for treatment plans - admin can set price immediately after case creation

            return view('admin.price_manager.add_price', compact('case'));
        } catch (\Exception $e) {
            Log::error('Error in AdminPriceManagerController@showAddPrice: ' . $e->getMessage());
            return back()->with('error', 'Failed to load case');
        }
    }

    /**
     * Add price to a case
     */
    public function addPrice(Request $request, $caseId)
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
                'advance_payment' => 'nullable|numeric|min:0',
                'estimated_completion_date' => 'nullable|date|after:today',
            ]);

            $case = \App\Models\CasePatient::with(['patient', 'doctor'])->findOrFail($caseId);
            
            // Check if case is in pending status
            if ($case->status !== 'pending' && $case->status !== 'in_planning' && $case->status !== 'approval') {
                return back()->with('error', 'Case must be in approval status to add price');
            }

            // Check if price is already set
            if ($case->price) {
                return back()->with('error', 'Price has already been set for this case');
            }

            // Calculate remaining balance
            $remainingBalance = $request->price - ($request->advance_payment ?? 0);

            // Update case with price information
            $case->update([
                'price' => $request->price,
                'advance_payment' => $request->advance_payment,
                'remaining_balance' => $remainingBalance,
                'price_added_by' => auth()->id(),
                'price_added_at' => now(),
                'estimated_completion_date' => $request->estimated_completion_date,
            ]);

            // Send email notification to doctor
            try {
                Mail::to($case->doctor->email)->send(new PriceSetNotification(
                    $case, 
                    $case->doctor, 
                    auth()->user(), 
                    $request->price, 
                    $request->advance_payment
                ));
            } catch (\Exception $emailError) {
                Log::warning('Failed to send price set notification: ' . $emailError->getMessage());
            }

            return redirect()->route('admin.price_manager.index')
                           ->with('success', 'Price added successfully to case ' . $case->case_id . '. Doctor has been notified.');
        } catch (\Exception $e) {
            Log::error('Error in AdminPriceManagerController@addPrice: ' . $e->getMessage());
            return back()->with('error', 'Failed to add price: ' . $e->getMessage());
        }
    }

    /**
     * Show case details
     */
    public function show($caseId)
    {
        try {
            $case = \App\Models\CasePatient::with(['patient', 'doctor', 'technician', 'admin', 'treatmentType', 'invoices'])->findOrFail($caseId);
            return view('admin.price_manager.show', compact('case'));
        } catch (\Exception $e) {
            Log::error('Error in AdminPriceManagerController@show: ' . $e->getMessage());
            return back()->with('error', 'Failed to load case details');
        }
    }

    /**
     * Clean up orphaned treatment plans
     */
    public function cleanupOrphaned()
    {
        try {
            $orphanedCount = $this->treatmentPlanService->cleanupOrphanedTreatmentPlans();
            return back()->with('success', "Successfully cleaned up {$orphanedCount} orphaned treatment plans");
        } catch (\Exception $e) {
            Log::error('Error in AdminPriceManagerController@cleanupOrphaned: ' . $e->getMessage());
            return back()->with('error', 'Failed to clean up orphaned treatment plans: ' . $e->getMessage());
        }
    }

    /**
     * Get cases waiting for pricing (status = pending, no price set)
     */
    private function getCasesWaitingForPricing()
    {
        return \App\Models\CasePatient::with(['patient', 'doctor'])
            ->whereIn('status', ['pending', 'in_planning', 'approval'])
            ->whereNull('price')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get cases with prices waiting for doctor acceptance (status = pending, price set)
     */
    private function getCasesWithPricesWaitingForAcceptance()
    {
        return \App\Models\CasePatient::with(['patient', 'doctor'])
            ->whereIn('status', ['pending', 'in_planning', 'approval'])
            ->whereNotNull('price')
            ->orderBy('price_added_at', 'desc')
            ->get();
    }

    /**
     * Get historical priced and approved cases
     */
    private function getHistoricalPricedCases()
    {
        return \App\Models\CasePatient::with(['patient', 'doctor', 'admin'])
            ->whereNotNull('price')
            ->whereIn('status', ['approval', 'in_production', 'shipped', 'in_planning'])
            ->orderBy('price_added_at', 'desc')
            ->get();
    }

    /**
     * Get cases in approval status (doctor accepted price)
     */
    private function getCasesInApprovalStatus()
    {
        return \App\Models\CasePatient::with(['patient', 'doctor', 'technician'])
            ->whereIn('status', ['approval', 'in_planning'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get cases in production status
     */
    private function getCasesInProduction()
    {
        return \App\Models\CasePatient::with(['patient', 'doctor', 'technician'])
            ->where('status', 'in_production')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get pricing statistics for dashboard
     */
    public function getPricingStats()
    {
        try {
            $stats = [
                'pending_pricing' => $this->getCasesWaitingForPricing()->count(),
                'waiting_acceptance' => $this->getCasesWithPricesWaitingForAcceptance()->count(),
                'in_approval' => $this->getCasesInApprovalStatus()->count(),
                'in_production' => $this->getCasesInProduction()->count(),
                'total_revenue' => \App\Models\CasePatient::whereNotNull('price')->sum('price'),
                'pending_revenue' => \App\Models\CasePatient::where('status', 'pending')->whereNotNull('price')->sum('price'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting pricing stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get pricing statistics'], 500);
        }
    }
}
