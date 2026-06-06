<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TreatmentPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TreatmentType;
use App\Models\CasePatient;

class AdminNewPricingController extends Controller
{
    protected $treatmentPlanService;

    public function __construct(TreatmentPlanService $treatmentPlanService)
    {
        $this->treatmentPlanService = $treatmentPlanService;
    }

    /**
     * Show the new pricing workflow dashboard
     */
    public function index()
    {
        try {
            // Get treatment plans waiting for pricing (pending status)
            $pendingPricing = TreatmentType::with(['case.patient', 'case.doctor', 'case.technician'])
                                          ->where('status', 'pending')
                                          ->whereNull('price')
                                          ->orderBy('created_at', 'desc')
                                          ->get();

            // Get treatment plans with prices waiting for doctor acceptance
            $pricedWaitingAcceptance = TreatmentType::with(['case.patient', 'case.doctor', 'case.technician'])
                                                   ->where('status', 'priced')
                                                   ->whereNotNull('price')
                                                   ->orderBy('price_added_at', 'desc')
                                                   ->get();

            // Get accepted treatment plans (ready for production)
            $acceptedPlans = TreatmentType::with(['case.patient', 'case.doctor', 'case.technician'])
                                        ->where('status', 'accepted')
                                        ->whereNotNull('price')
                                        ->orderBy('accepted_at', 'desc')
                                        ->get();

            return view('admin.new_pricing.index', compact('pendingPricing', 'pricedWaitingAcceptance', 'acceptedPlans'));
        } catch (\Exception $e) {
            Log::error('Error in AdminNewPricingController@index: ' . $e->getMessage());
            return back()->with('error', 'Failed to load pricing workflow data');
        }
    }

    /**
     * Show form to add price to a treatment plan
     */
    public function showAddPrice($treatmentPlanId)
    {
        try {
            $treatmentPlan = TreatmentType::with(['case.patient', 'case.doctor', 'case.technician'])
                                        ->findOrFail($treatmentPlanId);
            
            if ($treatmentPlan->status !== 'pending') {
                return back()->with('error', 'Treatment plan is not in pending status');
            }

            if ($treatmentPlan->price) {
                return back()->with('error', 'Price has already been set for this treatment plan');
            }

            return view('admin.new_pricing.add_price', compact('treatmentPlan'));
        } catch (\Exception $e) {
            Log::error('Error in AdminNewPricingController@showAddPrice: ' . $e->getMessage());
            return back()->with('error', 'Failed to load treatment plan');
        }
    }

    /**
     * Add price to treatment plan
     */
    public function addPrice(Request $request, $treatmentPlanId)
    {
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'advance_payment' => 'nullable|numeric|min:0',
                'estimated_completion_date' => 'nullable|date'
            ]);

            $treatmentPlan = TreatmentType::findOrFail($treatmentPlanId);
            
            if ($treatmentPlan->status !== 'pending') {
                return back()->with('error', 'Treatment plan is not in pending status');
            }

            if ($treatmentPlan->price) {
                return back()->with('error', 'Price has already been set for this treatment plan');
            }

            // Calculate remaining balance
            $remainingBalance = $validated['advance_payment'] ? $validated['price'] - $validated['advance_payment'] : $validated['price'];

            // Update treatment plan with price
            $treatmentPlan->update([
                'price' => $validated['price'],
                'advance_payment' => $validated['advance_payment'],
                'remaining_balance' => $remainingBalance,
                'price_added_by' => auth()->id(),
                'price_added_at' => now(),
                'estimated_completion_date' => $validated['estimated_completion_date'],
                'status' => 'priced' // New status: priced but not yet accepted
            ]);

            // Update case status to 'priced' (waiting for doctor to accept)
            $case = CasePatient::findOrFail($treatmentPlan->case_id);
            $case->update(['status' => 'priced']);

            // Send notification to doctor that price has been set
            $this->treatmentPlanService->sendDoctorNotification($case, $treatmentPlan, 'price_set');

            return redirect()->route('admin.new_pricing.index')
                           ->with('success', 'Price added successfully. Treatment plan is now waiting for doctor acceptance.');

        } catch (\Exception $e) {
            Log::error('Error in AdminNewPricingController@addPrice: ' . $e->getMessage());
            return back()->with('error', 'Failed to add price: ' . $e->getMessage());
        }
    }

    /**
     * View treatment plan details
     */
    public function show($treatmentPlanId)
    {
        try {
            $treatmentPlan = TreatmentType::with(['case.patient', 'case.doctor', 'case.technician', 'admin'])
                                        ->findOrFail($treatmentPlanId);

            return view('admin.new_pricing.show', compact('treatmentPlan'));
        } catch (\Exception $e) {
            Log::error('Error in AdminNewPricingController@show: ' . $e->getMessage());
            return back()->with('error', 'Failed to load treatment plan details');
        }
    }
}

