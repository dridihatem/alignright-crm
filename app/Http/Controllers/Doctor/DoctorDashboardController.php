<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Services\PatientService;
use App\Services\TicketService;
use App\Repositories\CaseRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DoctorDashboardController extends Controller
{
    protected $caseService;
    protected $patientService;
    protected $ticketService;
    protected $caseRepository;
    protected $patientRepository;
    protected $userRepository;

    public function __construct(
        CaseService $caseService,
        PatientService $patientService,
        TicketService $ticketService,
        CaseRepository $caseRepository,
        PatientRepository $patientRepository,
        UserRepository $userRepository
    ) {
        $this->caseService = $caseService;
        $this->patientService = $patientService;
        $this->ticketService = $ticketService;
        $this->caseRepository = $caseRepository;
        $this->patientRepository = $patientRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display the doctor dashboard
     */
    public function index()
    {
        try {
            $doctorId = auth()->user()->id;
            
            // Get dashboard statistics
            $caseStats = $this->caseService->getDashboardStats($doctorId);
            $patientStats = $this->patientService->getPatientStats($doctorId);
            $ticketStats = $this->ticketService->getTicketStats($doctorId);

            // Get recent cases
            $recentCases = $this->caseRepository->getByDoctor($doctorId, ['patient'])
                                               ->take(5);

            // Get monthly case totals for the last 30 days
            $monthlyTotals = $this->getMonthlyCaseTotals($doctorId);

            return view('doctor.dashboard', compact(
                'caseStats',
                'patientStats', 
                'ticketStats',
                'recentCases',
                'monthlyTotals'
            ));
        } catch (Exception $e) {
            Log::error('Doctor dashboard error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load dashboard data');
        }
    }

    /**
     * Get latest cases for DataTable
     */
    public function latestCases(Request $request): JsonResponse
    {
        try {
            $doctorId = auth()->user()->id;
            $filters = $request->all();
            $filters['doctor_id'] = $doctorId;

            $query = $this->caseRepository->getWithFilters($filters, ['patient', 'technician', 'laboratory']);

            return DataTables::of($query)
                ->addColumn('patient_name', function ($case) {
                    return $case->patient->name ?? 'N/A';
                })
                ->addColumn('technician_name', function ($case) {
                    return $case->technician->name ?? 'N/A';
                })
                ->addColumn('laboratory_name', function ($case) {
                    return $case->laboratory->name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($case) {
                    return $this->getStatusBadge($case->status);
                })
                ->addColumn('actions', function ($case) {
                    return view('doctor.cases.partials.actions', compact('case'))->render();
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting latest cases: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve cases'], 500);
        }
    }

    /**
     * Check doctor code
     */
    public function checkCodeDoctor(Request $request): JsonResponse
    {
        try {
            $user = $this->userRepository->checkDoctorCode($request->code_parrent);
            
            if ($user) {
                return response()->json(['status' => 'success', 'data' => $user]);
            } else {
                return response()->json(['status' => 'error', 'data' => '']);
            }
        } catch (Exception $e) {
            Log::error('Error checking doctor code: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to check doctor code'], 500);
        }
    }

    /**
     * Get monthly case totals for the last 30 days
     */
    private function getMonthlyCaseTotals($doctorId): array
    {
        try {
            $casesByMonth = $this->caseRepository->getByDoctor($doctorId)
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(function ($case) {
                    return $case->created_at->format('n'); // Month number
                })
                ->map(function ($group) {
                    return $group->count();
                })
                ->toArray();

            // Create array with all months, defaulting to 0
            $monthlyTotals = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlyTotals[] = $casesByMonth[$i] ?? 0;
            }

            return $monthlyTotals;
        } catch (Exception $e) {
            Log::error('Error getting monthly case totals: ' . $e->getMessage());
            return array_fill(0, 12, 0);
        }
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status): string
    {
        $badges = [
            'draft' => '<span class="badge bg-label-secondary">Draft</span>',
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'in_planning' => '<span class="badge bg-label-info">In Planning</span>',
            'approval' => '<span class="badge bg-label-primary">Approval</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
            'in_production' => '<span class="badge bg-label-success">In Production</span>',
            'shipped' => '<span class="badge bg-label-dark">Shipped</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }
}
