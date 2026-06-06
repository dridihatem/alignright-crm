<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\TreatmentPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TreatmentType;
use App\Models\CasePatient;

class DoctorNewTreatmentPlanController extends Controller
{
    protected $treatmentPlanService;

    public function __construct(TreatmentPlanService $treatmentPlanService)
    {
        $this->treatmentPlanService = $treatmentPlanService;
    }

    /**
     * Show treatment plans waiting for doctor acceptance (with prices set)
     */
    public function index()
    {
        try {
            $doctorId = auth()->id();
            
            // Get treatment plans with prices waiting for doctor acceptance
            $pricedWaitingAcceptance = TreatmentType::with(['case.patient', 'case.technician'])
                                                   ->where('status', 'priced')
                                                   ->whereNotNull('price')
                                                   ->whereHas('case', function($query) use ($doctorId) {
                                                       $query->where('doctor_id', $doctorId);
                                                   })
                                                   ->orderBy('price_added_at', 'desc')
                                                   ->get();

            // Get accepted treatment plans
            $acceptedPlans = TreatmentType::with(['case.patient', 'case.technician'])
                                        ->where('status', 'accepted')
                                        ->whereNotNull('price')
                                        ->whereHas('case', function($query) use ($doctorId) {
                                            $query->where('doctor_id', $doctorId);
                                        })
                                        ->orderBy('accepted_at', 'desc')
                                        ->get();

            return view('doctor.new_treatment_plans.index', compact('pricedWaitingAcceptance', 'acceptedPlans'));
        } catch (\Exception $e) {
            Log::error('Error in DoctorNewTreatmentPlanController@index: ' . $e->getMessage());
            return back()->with('error', 'Failed to load treatment plans');
        }
    }

    /**
     * Accept treatment plan with price
     */
    public function accept(Request $request, $treatmentPlanId)
    {
        try {
            $treatmentPlan = TreatmentType::with('case')->findOrFail($treatmentPlanId);
            
            // Check if the case belongs to the authenticated doctor
            if ($treatmentPlan->case->doctor_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            // Check if treatment plan has price set
            if (!$treatmentPlan->price) {
                return redirect()->back()->with('error', 'Treatment plan must have price set before accepting');
            }
            
            // Check if treatment plan is in priced status
            if ($treatmentPlan->status !== 'priced') {
                return redirect()->back()->with('error', 'Treatment plan is not ready for acceptance. Current status: ' . $treatmentPlan->status);
            }

            // Accept the treatment plan
            $treatmentPlan->update([
                'status' => 'accepted',
                'accepted_by' => auth()->id(),
                'accepted_at' => now()
            ]);

            // Update case status to approval (ready for production)
            $case = CasePatient::findOrFail($treatmentPlan->case_id);
            $case->update(['status' => 'approval']);

            // Send notification to admin that treatment plan was accepted
            $this->treatmentPlanService->sendAdminNotification($case, $treatmentPlan, 'accepted');

            return redirect()->route('doctor.new_treatment_plans.index')
                           ->with('success', 'Treatment plan accepted successfully. Case is now ready for production.');

        } catch (\Exception $e) {
            Log::error('Error in DoctorNewTreatmentPlanController@accept: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to accept treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Reject treatment plan with price
     */
    public function reject(Request $request, $treatmentPlanId)
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'nullable|string|max:500'
            ]);

            $treatmentPlan = TreatmentType::with('case')->findOrFail($treatmentPlanId);
            
            // Check if the case belongs to the authenticated doctor
            if ($treatmentPlan->case->doctor_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            // Check if treatment plan is in priced status
            if ($treatmentPlan->status !== 'priced') {
                return redirect()->back()->with('error', 'Treatment plan is not in priced status');
            }

            // Reject the treatment plan
            $treatmentPlan->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason']
            ]);

            // Update case status to rejected
            $case = CasePatient::findOrFail($treatmentPlan->case_id);
            $case->update(['status' => 'rejected']);

            // Send notification to technician that treatment plan was rejected
            $this->treatmentPlanService->sendTechnicianNotification($case, $treatmentPlan, 'rejected');

            return redirect()->route('doctor.new_treatment_plans.index')
                           ->with('success', 'Treatment plan rejected successfully.');

        } catch (\Exception $e) {
            Log::error('Error in DoctorNewTreatmentPlanController@reject: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * View treatment plan details
     */
    public function show($treatmentPlanId)
    {
        try {
            $treatmentPlan = TreatmentType::with(['case.patient', 'case.technician', 'admin'])
                                        ->findOrFail($treatmentPlanId);

            // Check if the case belongs to the authenticated doctor
            if ($treatmentPlan->case->doctor_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            return view('doctor.new_treatment_plans.show', compact('treatmentPlan'));
        } catch (\Exception $e) {
            Log::error('Error in DoctorNewTreatmentPlanController@show: ' . $e->getMessage());
            return back()->with('error', 'Failed to load treatment plan details');
        }
    }
}
