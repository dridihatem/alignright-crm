<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Services\TreatmentPlanService;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DoctorTreatmentPlanController extends Controller
{
    protected $caseService;
    protected $treatmentPlanService;
    protected $userRepository;

    public function __construct(
        CaseService $caseService,
        TreatmentPlanService $treatmentPlanService,
        UserRepository $userRepository
    ) {
        $this->caseService = $caseService;
        $this->treatmentPlanService = $treatmentPlanService;
        $this->userRepository = $userRepository;
    }

    /**
     * Get treatment plans for a case
     */
    public function index($caseId)
    {
        try {
            $case = $this->caseService->getCaseById($caseId);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to case');
            }

            $treatmentPlans = $this->treatmentPlanService->getTreatmentPlansForCase($caseId);
            
            return view('doctor.treatment_plans.index', compact('case', 'treatmentPlans'));
        } catch (Exception $e) {
            Log::error('Error getting treatment plans: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load treatment plans');
        }
    }

    /**
     * Show treatment plan
     */
    public function show($id)
    {
        try {
            $treatmentPlan = $this->treatmentPlanService->getTreatmentPlanById($id);
            $case = $this->caseService->getCaseById($treatmentPlan->case_id);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            return view('doctor.treatment_plans.show', compact('treatmentPlan', 'case'));
        } catch (Exception $e) {
            Log::error('Error showing treatment plan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Treatment plan not found');
        }
    }

    /**
     * Show accept treatment plan form with price review
     */
    public function showAcceptForm($id)
    {
        try {
            $treatmentPlan = $this->treatmentPlanService->getTreatmentPlanById($id);
            $case = $this->caseService->getCaseById($treatmentPlan->case_id);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            // Get technicians and laboratories for assignment
            $technicians = $this->userRepository->getByRole(3); // Technician role
            $laboratories = $this->userRepository->getByRole(4); // Laboratory role

            return view('doctor.treatment_plans.accept', compact('treatmentPlan', 'case', 'technicians', 'laboratories'));
        } catch (Exception $e) {
            Log::error('Error showing accept form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Treatment plan not found');
        }
    }

    /**
     * Accept treatment plan with price
     */
    public function accept(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'technician_id' => 'required|exists:users,id',
                'laboratory_id' => 'required|exists:users,id',
            ]);

            $treatmentPlan = $this->treatmentPlanService->getTreatmentPlanById($id);
            $case = $this->caseService->getCaseById($treatmentPlan->case_id);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            // Accept treatment plan with price
            $this->treatmentPlanService->acceptTreatmentPlan($id, $validated['price']);

            // Update case with technician and laboratory assignments
            $this->caseService->updateCase($case->id, [
                'technician_id' => $validated['technician_id'],
                'laboratory_id' => $validated['laboratory_id'],
            ]);
            
            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Treatment plan accepted, price set, and team assigned successfully');
        } catch (Exception $e) {
            Log::error('Error accepting treatment plan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to accept treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Show reject treatment plan form
     */
    public function showRejectForm($id)
    {
        try {
            $treatmentPlan = $this->treatmentPlanService->getTreatmentPlanById($id);
            $case = $this->caseService->getCaseById($treatmentPlan->case_id);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            return view('doctor.treatment_plans.reject', compact('treatmentPlan', 'case'));
        } catch (Exception $e) {
            Log::error('Error showing reject form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Treatment plan not found');
        }
    }

    /**
     * Reject treatment plan
     */
    public function reject(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $treatmentPlan = $this->treatmentPlanService->getTreatmentPlanById($id);
            $case = $this->caseService->getCaseById($treatmentPlan->case_id);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to treatment plan');
            }

            $this->treatmentPlanService->rejectTreatmentPlan($id, $validated['reason'] ?? null);
            
            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Treatment plan rejected successfully');
        } catch (Exception $e) {
            Log::error('Error rejecting treatment plan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Create treatment plan
     */
    public function create(Request $request, $caseId)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'link' => 'required|url|max:500',
                'description' => 'nullable|string',
                'type_file' => 'nullable|string',
            ]);

            $case = $this->caseService->getCaseById($caseId);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to case');
            }

            $this->treatmentPlanService->createTreatmentPlan($validated, $caseId);
            
            return redirect()->route('doctor.treatment_plans.index', $caseId)
                           ->with('success', 'Treatment plan created successfully');
        } catch (Exception $e) {
            Log::error('Error creating treatment plan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Get treatment plans for DataTable
     */
    public function getTreatmentPlansData($caseId)
    {
        try {
            $case = $this->caseService->getCaseById($caseId);
            
            // Check if the case belongs to the authenticated doctor
            if ($case->doctor_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            $treatmentPlans = $this->treatmentPlanService->getTreatmentPlansForCase($caseId);

            return DataTables::of($treatmentPlans)
                ->addColumn('status_badge', function ($plan) {
                    return $this->getStatusBadge($plan->status);
                })
                ->addColumn('actions', function ($plan) {
                    return view('doctor.treatment_plans.partials.actions', compact('plan'))->render();
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting treatment plans data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve treatment plans'], 500);
        }
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status): string
    {
        $badges = [
            'pending' => '<span class="badge bg-label-warning">Pending Review</span>',
            'accepted' => '<span class="badge bg-label-success">Accepted</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }
}
