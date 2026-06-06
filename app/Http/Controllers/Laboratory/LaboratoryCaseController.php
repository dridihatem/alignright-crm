<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Laboratory\AddCommentRequest;
use App\Services\LaboratoryService;
use App\Models\CasePatient;
use App\Models\ToothProblemCase;
use App\Models\FileUpload;
use App\Models\Comment;
use App\Models\TreatmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Concerns\GroupsCasesByPatient;
use Exception;

class LaboratoryCaseController extends Controller
{
    use GroupsCasesByPatient;

    protected $laboratoryService;

    public function __construct(LaboratoryService $laboratoryService)
    {
        $this->laboratoryService = $laboratoryService;
    }

    /**
     * Display a listing of cases
     */
    public function index()
    {
        try {
            $laboratoryId = auth()->user()->id;
            $patients = $this->laboratoryService->getPatients($laboratoryId);

            // Build patient-grouped cases (admin-style)
            $cases = $this->laboratoryService->getCases($laboratoryId);
            $patientGroups = $this->buildPatientGroups($cases, ['show' => 'laboratory.cases.show']);

            return view('laboratory.cases.index', compact('patients', 'patientGroups'));
        } catch (Exception $e) {
            Log::error('Error loading laboratory cases index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load cases');
        }
    }

    /**
     * Get cases data for DataTables
     */
    public function getCasesData(Request $request)
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
                ->addColumn('status', function($row) {
                    $status = $row->status;
                    if($status == 'in_production'){
                        return '<span class="badge bg-label-success">'.__('master.in_production').'</span>';
                    }elseif($status == 'shipped'){
                        return '<span class="badge bg-label-success">'.__('master.shipped').'</span>';
                    }
                    return '<span class="badge bg-label-secondary">'.ucfirst($status).'</span>';
                })
                ->addColumn('treatment_type', function($row) {
                    return $row->treatment_type ?? 'N/A';
                })
                ->addColumn('date', function($row) {
                    return $row->created_at->format('d/m/Y');
                })
                ->addColumn('wetransfer_info', function($row) {
                    $weTransfer = $row->latestWeTransferNotification;
                    if($weTransfer) {
                        return '<a href="'.$weTransfer->wetransfer_link.'" target="_blank" class="badge bg-label-success"><i class="icon-base ti tabler-download"></i> WeTransfer</a><br><small class="text-muted">From: '.$weTransfer->technician->name.'</small>';
                    }
                    return '<span class="badge bg-label-secondary">No WeTransfer</span>';
                })
                ->addColumn('accepted_date', function($row) {
                    return $row->accepted_date ? $row->accepted_date->format('d/m/Y') : 'N/A';
                })
                ->addColumn('rejected_date', function($row) {
                    return $row->rejected_date ? $row->rejected_date->format('d/m/Y') : 'N/A';
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
            Log::error('Error getting cases data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load cases data'], 500);
        }
    }

    /**
     * Show the specified case
     */
    public function show($id)
    {
        try {
            $laboratoryId = auth()->user()->id;
            $case = $this->laboratoryService->getCaseDetails($id, $laboratoryId);

            // Get related data
            $toothProblemscase = ToothProblemCase::where('case_id', $id)->with('tooth_problem')->get();
            $comments = Comment::where('case_id', $id)->with('user')->latest()->get();
            $treatmentTypes = TreatmentType::where('case_id', $id)->latest()->get();
            
            // STL files
            $stl_files = \App\Models\FileUpload::where('case_id', $id)->where('wich_rubrique', 'stl_scan')->get();
            
            // Clinical photos
            $files_clinical = \App\Models\FileUpload::where('case_id', $id)->where('wich_rubrique', 'clinical_photo')->get();

            // Radiographs
            $files_radiographs = \App\Models\FileUpload::where('case_id', $id)->where('wich_rubrique', 'radiograph')->get();

            // Other files
            $other_files = \App\Models\FileUpload::where('case_id', $id)->where('wich_rubrique', 'other_file')->get();

            // Count files for convenience
            $count_stl_files = $stl_files->count();
            $count_clinical_files = $files_clinical->count();
            $count_radiograph_files = $files_radiographs->count();
            $count_other_files = $other_files->count();

            return view('laboratory.cases.show', compact(
                'case', 'toothProblemscase', 'comments', 'treatmentTypes',
                'files_clinical', 'files_radiographs', 'other_files',
                'stl_files', 'files_clinical', 'files_radiographs', 'other_files',
                'count_stl_files', 'count_clinical_files', 'count_radiograph_files', 'count_other_files'
            ));
        } catch (Exception $e) {
            Log::error('Error showing case: ' . $e->getMessage());
            return redirect()->route('laboratory.cases.index')->with('error', 'Case not found');
        }
    }

    /**
     * Update case status
     */
    public function updateStatus($id, $status)
    {
        try {
            $laboratoryId = auth()->user()->id;
            $case = $this->laboratoryService->updateCaseStatus($id, $status, $laboratoryId);
            
            return redirect()->back()->with('success', 'Case status updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating case status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update case status');
        }
    }

    /**
     * Scoped header search: only cases assigned to the authenticated laboratory.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $cases = CasePatient::with(['patient', 'doctor'])
            ->where('laboratory_id', auth()->id())
            ->where(function ($q) use ($term) {
                $q->where('case_id', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($p) use ($term) {
                        $p->where('name', 'like', "%{$term}%")
                            ->orWhere('surname', 'like', "%{$term}%")
                            ->orWhere('reference', 'like', "%{$term}%");
                    });
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $results = $cases->map(function ($case) {
            $patientName = $case->patient
                ? trim(($case->patient->name ?? '') . ' ' . ($case->patient->surname ?? ''))
                : __('master.not_available');

            return [
                'id' => $case->id,
                'case_id' => $case->case_id,
                'patient' => $patientName,
                'reference' => $case->patient->reference ?? null,
                'doctor' => $case->doctor->name ?? null,
                'status' => $case->status,
                'url' => route('laboratory.cases.show', $case->id),
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Add comment to case
     */
    public function addComment(Request $request)
    {
        try {
            $request->validate([
                'comment' => 'required|string|max:1000',
                'case_id' => 'required|exists:case_patients,id'
            ]);

            $laboratoryId = auth()->user()->id;
            $comment = $this->laboratoryService->addCaseComment(
                $request->case_id,
                $laboratoryId,
                $request->comment
            );

            if ($request->ajax()) {
                $userPhoto = $comment->user->photo 
                    ? asset('storage/' . $comment->user->photo) 
                    : asset('assets/img/avatars/default.png');

                return response()->json([
                    'success' => true,
                    'comment' => $comment->comment,
                    'user_photo' => $userPhoto,
                    'user' => $comment->user->name,
                    'user_role' => $comment->user->role->name ?? 'laboratory',
                    'date' => $comment->created_at->format('d-m-Y H:i:s')
                ]);
            }

            return redirect()->back()->with('success', 'Comment added successfully');
        } catch (Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to add comment'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to add comment');
        }
    }

  
}
