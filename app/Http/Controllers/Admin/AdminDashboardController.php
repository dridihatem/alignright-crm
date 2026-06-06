<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Services\UserService;
use App\Repositories\CaseRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    protected $caseService;
    protected $userService;
    protected $caseRepository;
    protected $userRepository;

    public function __construct(
        CaseService $caseService,
        UserService $userService,
        CaseRepository $caseRepository,
        UserRepository $userRepository
    ) {
        $this->caseService = $caseService;
        $this->userService = $userService;
        $this->caseRepository = $caseRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        try {
            $stats = $this->caseService->getDashboardStats();
            return view('admin.dashboard', $stats);

        } catch (Exception $e) {
            Log::error('Admin dashboard error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load dashboard data');
        }
    }

    /**
     * Get cases for DataTables
     */
    public function getCases(Request $request): JsonResponse
    {
        try {
            $query = $this->caseService->getCasesForDataTable($request);
            
            return DataTables::of($query)
                ->addColumn('checkbox', function ($case) {
                    return '<input type="checkbox" class="case-checkbox" value="' . $case->id . '">';
                })
                ->addColumn('patient_id', function ($case) {
                    return $case->patient ? $case->patient->name . ' ' . $case->patient->surname : 'N/A';
                })
                ->addColumn('doctor_name', function ($case) {
                    return $case->doctor ? $case->doctor->name : 'N/A';
                })
                ->addColumn('status_badge', function ($case) {
                    return $this->getStatusBadge($case->status);
                })
                ->addColumn('date', function ($case) {
                    return $case->created_at ? $case->created_at->format('d-m-Y H:i') : 'N/A';
                })
                ->addColumn('accepted_date', function ($case) {
                    return $case->accepted_date ? \Carbon\Carbon::parse($case->accepted_date)->format('d-m-Y H:i') : 'N/A';
                })
                ->addColumn('rejected_date', function ($case) {
                    return $case->rejected_date ? \Carbon\Carbon::parse($case->rejected_date)->format('d-m-Y H:i') : 'N/A';
                })
                ->addColumn('price_status', function ($case) {
                    return $this->getPriceStatusBadge($case);
                })
                ->addColumn('actions', function ($case) {
                    return $this->getActionButtons($case);
                })
                ->rawColumns(['checkbox', 'status_badge', 'price_status', 'actions'])
                ->make(true);

        } catch (Exception $e) {
            Log::error('Error getting cases for DataTable: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve cases'], 500);
        }
    }

    /**
     * Display cases list view
     */
    public function cases()
    {
        try {
            $doctors = $this->userRepository->getDoctorsWithStats();
            $unassignedCases = $this->caseRepository->getUnassignedCases();

            return view('admin.cases.index', compact('doctors', 'unassignedCases'));

        } catch (Exception $e) {
            Log::error('Error loading cases view: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load cases');
        }
    }

    /**
     * Display cases table view
     */
    public function casesTable()
    {
        try {
            return view('admin.cases.table');

        } catch (Exception $e) {
            Log::error('Error loading cases table view: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load cases table');
        }
    }

    /**
     * Show case details
     */
    public function showCase($id)
    {
        try {
            $case = $this->caseRepository->findById($id);
            return view('admin.cases.show', compact('case'));

        } catch (Exception $e) {
            Log::error('Error showing case: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Case not found');
        }
    }

    /**
     * Edit case form
     */
    public function editCase($id)
    {
        try {
            $case = $this->caseRepository->findById($id);
            $doctors = $this->userRepository->getActiveByRole(2);
            $technicians = $this->userRepository->getActiveByRole(3);
            $laboratories = $this->userRepository->getActiveByRole(4);

            return view('admin.cases.edit', compact('case', 'doctors', 'technicians', 'laboratories'));

        } catch (Exception $e) {
            Log::error('Error loading case edit form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load case edit form');
        }
    }

    /**
     * Update case
     */
    public function updateCase(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:users,id',
                'technician_id' => 'nullable|exists:users,id',
                'laboratory_id' => 'nullable|exists:users,id',
                'treatment_type' => 'nullable|string|max:255',
                'status' => 'required|in:draft,pending,in_planning,approval,in_production,shipped,rejected',
                'priority' => 'nullable|in:low,normal,high,urgent',
                'price' => 'nullable|numeric|min:0',
                'doctor_instruction' => 'nullable|string',
            ]);

            $case = $this->caseService->updateCase($id, $validated);

            return redirect()->route('admin.cases.show', $case->id)
                           ->with('success', 'Case updated successfully');

        } catch (Exception $e) {
            Log::error('Error updating case: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update case: ' . $e->getMessage());
        }
    }

    /**
     * Delete case
     */
    public function deleteCase($id)
    {
        try {
            $this->caseService->deleteCase($id);
            return redirect()->route('admin.cases.list')
                           ->with('success', 'Case deleted successfully');

        } catch (Exception $e) {
            Log::error('Error deleting case: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete case: ' . $e->getMessage());
        }
    }

    /**
     * Change case status
     */
    public function changeCaseStatus($id, $status)
    {
        try {
            $case = $this->caseService->changeCaseStatus($id, $status);
            
            return response()->json([
                'success' => true,
                'message' => 'Case status updated successfully',
                'new_status' => $case->status
            ]);

        } catch (Exception $e) {
            Log::error('Error changing case status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change case status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign technician to case
     */
    public function assignTechnician(Request $request, $id)
    {
        try {
            $request->validate([
                'technician_id' => 'required|exists:users,id'
            ]);

            $case = $this->caseService->assignTechnician($id, $request->technician_id);

            return response()->json([
                'success' => true,
                'message' => 'Technician assigned successfully',
                'technician_name' => $case->technician->name
            ]);

        } catch (Exception $e) {
            Log::error('Error assigning technician: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign technician: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign laboratory to case
     */
    public function assignLaboratory(Request $request, $id)
    {
        try {
            $request->validate([
                'laboratory_id' => 'required|exists:users,id'
            ]);

            $case = $this->caseService->assignLaboratory($id, $request->laboratory_id);

            return response()->json([
                'success' => true,
                'message' => 'Laboratory assigned successfully',
                'laboratory_name' => $case->laboratory->name
            ]);

        } catch (Exception $e) {
            Log::error('Error assigning laboratory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign laboratory: ' . $e->getMessage()
            ], 500);
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
            'approval' => '<span class="badge bg-label-success">Approval</span>',
            'in_production' => '<span class="badge bg-label-primary">In Production</span>',
            'shipped' => '<span class="badge bg-label-success">Shipped</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get price status badge HTML
     */
    private function getPriceStatusBadge($case): string
    {
        if ($case->price_accepted_at) {
            return '<span class="badge bg-label-success"><i class="fas fa-check me-1"></i>Price Accepted</span>';
        } elseif ($case->price_rejected_at) {
            return '<span class="badge bg-label-danger"><i class="fas fa-times me-1"></i>Price Rejected</span>';
        } elseif ($case->price) {
            return '<span class="badge bg-label-warning"><i class="fas fa-clock me-1"></i>Price Pending</span>';
        } else {
            return '<span class="badge bg-label-secondary">No Price</span>';
        }
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons($case): string
    {
        $buttons = '<div class="dropdown">';
        $buttons .= '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">';
        $buttons .= '<i class="fas fa-ellipsis-v"></i>';
        $buttons .= '</button>';
        $buttons .= '<ul class="dropdown-menu">';
        $buttons .= '<li><a class="dropdown-item" href="' . route('admin.cases.show', $case->id) . '">';
        $buttons .= '<i class="fas fa-eye me-2"></i>View</a></li>';
        $buttons .= '<li><a class="dropdown-item" href="' . route('admin.cases.edit', $case->id) . '">';
        $buttons .= '<i class="fas fa-edit me-2"></i>Edit</a></li>';
        $buttons .= '<li><hr class="dropdown-divider"></li>';
        $buttons .= '<li><a class="dropdown-item text-danger" href="' . route('admin.cases.delete', $case->id) . '" ';
        $buttons .= 'onclick="return confirm(\'Are you sure?\')">';
        $buttons .= '<i class="fas fa-trash me-2"></i>Delete</a></li>';
        $buttons .= '</ul></div>';

        return $buttons;
    }
}
