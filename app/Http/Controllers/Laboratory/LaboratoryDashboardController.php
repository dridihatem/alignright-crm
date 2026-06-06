<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Services\LaboratoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Concerns\GroupsCasesByPatient;
use Exception;

class LaboratoryDashboardController extends Controller
{
    use GroupsCasesByPatient;

    protected $laboratoryService;

    public function __construct(LaboratoryService $laboratoryService)
    {
        $this->laboratoryService = $laboratoryService;
    }

    /**
     * Display the laboratory dashboard
     */
    public function index()
    {
        try {
            $laboratoryId = auth()->user()->id;
            $stats = $this->laboratoryService->getDashboardStats($laboratoryId);

            $cases = $this->laboratoryService->getCases($laboratoryId);
            $stats['patientGroups'] = $this->buildPatientGroups($cases, ['show' => 'laboratory.cases.show']);

            return view('laboratory.dashboard', $stats);
        } catch (Exception $e) {
            Log::error('Error loading laboratory dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load dashboard');
        }
    }

    /**
     * Get latest cases for dashboard DataTable
     */
    public function getLatestCases(Request $request)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Invalid request'], 400);
            }

            $laboratoryId = auth()->user()->id;
            $filters = [
                'case_id' => $request->case_id,
                'patient_id' => $request->patient_id,
                'treatment_type' => $request->treatment_type,
                'status' => $request->status
            ];

            $cases = $this->laboratoryService->getCases($laboratoryId, array_filter($filters));

            return DataTables::of($cases)
                ->addIndexColumn()
                ->addColumn('case_id', function($row) {
                    return '<a href="'.route('laboratory.cases.show', $row->id).'">'.$row->case_id.'</a>';
                })
                ->addColumn('patient_id', function($row) {
                    return $row->patient ? $row->patient->name : __('master.no_patient');
                })
                ->addColumn('doctor_id', function($row) {
                    return $row->doctor ? $row->doctor->name : __('master.no_doctor');
                })
                ->addColumn('treatment_type', function($row) {
                    return $row->treatment_type ?? 'N/A';
                })
                ->addColumn('date', function($row) {
                    return $row->created_at->format('d/m/Y');
                })
                ->addColumn('status', function($row) {
                    $status = $row->status;
                    if($status == 'in_production'){
                        return '<span class="badge bg-label-success">'.__('master.in_production').'</span>';
                    }elseif($status == 'shipped'){
                        return '<span class="badge bg-label-success">'.__('master.shipped').'</span>';
                    }
                    return '<span class="badge bg-label-secondary">'.ucfirst($status).'</span>';
                })
                ->addColumn('wetransfer_info', function($row) {
                    $weTransfer = $row->latestWeTransferNotification;
                    if($weTransfer) {
                        return '<a href="'.$weTransfer->wetransfer_link.'" target="_blank" class="badge bg-label-success"><i class="icon-base ti tabler-download"></i> WeTransfer</a><br><small class="text-muted">From: '.$weTransfer->technician->name.'</small>';
                    }
                    return '<span class="badge bg-label-secondary">No WeTransfer</span>';
                })
                ->addColumn('action', function($row) {
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('laboratory.cases.show', $row->id).'">'.__('master.view').'</a></li>
                        </ul>
                    </div>';
                    return $button;
                })
                ->rawColumns(['case_id', 'status', 'wetransfer_info', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting latest cases data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load latest cases data'], 500);
        }
    }
}
