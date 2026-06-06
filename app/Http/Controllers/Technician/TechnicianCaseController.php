<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\AddCommentRequest;
use App\Http\Requests\Technician\UpdateCaseStatusRequest;
use App\Services\TechnicianService;
use App\Models\CasePatient;
use App\Models\ToothProblemCase;
use App\Models\FileUpload;
use App\Models\Comment;
use App\Models\TreatmentType;
use App\Models\WeTransferNotification;
use App\Mail\WeTransferNotification as WeTransferNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Concerns\GroupsCasesByPatient;
use Exception;

class TechnicianCaseController extends Controller
{
    use GroupsCasesByPatient;

    protected $technicianService;

    public function __construct(TechnicianService $technicianService)
    {
        $this->technicianService = $technicianService;
    }

    /**
     * Display a listing of cases
     */
    public function index()
    {
        
        try {
            $technicianId = auth()->user()->id;
            $stats = $this->technicianService->getDashboardStats($technicianId);

            // Build patient-grouped cases (admin-style)
            $cases = $this->technicianService->getTechnicianCases($technicianId)->get();
            $patientGroups = $this->buildPatientGroups($cases, ['show' => 'technician.cases.show']);

            return view('technician.cases.index', compact('stats', 'patientGroups'));
        } catch (Exception $e) {
            Log::error('Error loading technician cases index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load cases');
        }
    }

    /**
     * Get cases data for DataTables
     */
    public function getCasesData(Request $request)
    {
        try {
            $technicianId = auth()->user()->id;
            
            // Get filters from DataTables parameters
            $filters = [];
            if ($request->has('status') && !empty($request->get('status'))) {
                $filters['status'] = $request->get('status');
            }
            if ($request->has('search.value') && !empty($request->get('search.value'))) {
                $filters['search'] = $request->get('search.value');
            }
            
            $casesQuery = $this->technicianService->getTechnicianCases($technicianId, $filters);
            $cases = $casesQuery->get();

            return DataTables::of($cases)
                ->addIndexColumn()
                ->addColumn('case_id', function($row) {
                    return $row->case_id;
                })
                ->addColumn('date', function($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y') : 'N/A';
                })
                ->addColumn('status', function($row) {
                    return $this->getStatusBadge($row->status);
                })
                ->addColumn('treatment_type', function($row) {
                    return ucfirst($row->treatment_type ?? 'N/A');
                })
                ->addColumn('doctor_id', function($row) {
                    return $row->doctor ? $row->doctor->name : 'N/A';
                })
                ->addColumn('accepted_date', function($row) {
                    return $row->accepted_date ? $row->accepted_date->format('d-m-Y') : 'N/A';
                })
                ->addColumn('rejected_date', function($row) {
                    return $row->rejected_date ? $row->rejected_date->format('d-m-Y') : 'N/A';
                })
                ->addColumn('action', function($row) {
                    return $this->getActionButtons($row);
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting cases data: ' . $e->getMessage(), [
                'technician_id' => $technicianId ?? null,
                'filters' => $filters ?? [],
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to load cases data'], 500);
        }
    }

    /**
     * Show the specified case
     */
    public function show($id)
    {
        try {
            $case = CasePatient::where('id', $id)
                              ->where('technician_id', auth()->user()->id)
                              ->with(['patient', 'doctor', 'technician', 'laboratory'])
                              ->firstOrFail();

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
            $count_stl_files = $stl_files->count();
            $count_clinical_files = $files_clinical->count();
            $count_radiograph_files = $files_radiographs->count();
            $count_other_files = $other_files->count();

            return view('technician.cases.show', compact(
                'case', 'toothProblemscase', 'comments', 'treatmentTypes',
                'files_clinical', 'files_radiographs',   'other_files',
                'stl_files', 'count_stl_files', 'count_clinical_files', 'count_radiograph_files', 'count_other_files'
            ));
        } catch (Exception $e) {
            Log::error('Error showing case: ' . $e->getMessage());
            return redirect()->route('technician.cases.index')->with('error', 'Case not found');
        }
    }

    /**
     * Scoped header search: only cases assigned to the authenticated technician.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $cases = CasePatient::with(['patient', 'doctor'])
            ->where('technician_id', auth()->id())
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
                'url' => route('technician.cases.show', $case->id),
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Technician uploads finition files + a description for the case.
     */
    public function storeFinition(Request $request, $id)
    {
        try {
            $case = CasePatient::where('id', $id)
                               ->where('technician_id', auth()->id())
                               ->firstOrFail();

            $request->validate([
                'finition_description' => 'nullable|string|max:5000',
                'finition_files'       => 'nullable|array|max:20',
                'finition_files.*'     => 'file|max:51200|mimes:pdf,jpg,jpeg,png,gif,webp,stl,zip,rar,doc,docx',
            ]);

            $folder = "case_files/{$id}/finition";
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($folder)) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($folder);
            }

            $stored = 0;
            if ($request->hasFile('finition_files')) {
                foreach ($request->file('finition_files') as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }

                    $filename = time() . '-' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                    $path = $file->storeAs($folder, $filename, 'public');
                    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);

                    FileUpload::create([
                        'name'          => $file->getClientOriginalName(),
                        'path'          => $path,
                        'url'           => $url,
                        'type'          => $file->getClientOriginalExtension(),
                        'size'          => $file->getSize(),
                        'case_id'       => (string) $id,
                        'patient_id'    => (string) $case->patient_id,
                        'wich_rubrique' => 'finition',
                        'storage_type'  => 'public',
                        'status'        => 'active',
                    ]);

                    $stored++;
                }
            }

            $case->finition_description = $request->input('finition_description');
            $case->finition_completed_at = now();
            $case->save();

            // Notify the doctor (best effort).
            try {
                \App\Models\Notification::create([
                    'title' => $case->case_id . ' - ' . __('master.finition_uploaded'),
                    'message' => $case->case_id . ' - ' . __('master.finition_uploaded_message'),
                    'type' => 'finition_uploaded',
                    'status' => 'active',
                    'case_id' => $case->id,
                    'doctor_id' => $case->doctor_id,
                    'technician_id' => $case->technician_id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Finition upload notification failed: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', __('master.finition_saved_successfully'));
        } catch (Exception $e) {
            Log::error('Error saving finition: ' . $e->getMessage(), ['case_id' => $id]);
            return redirect()->back()->with('error', __('master.failed_to_save_finition'));
        }
    }

    /**
     * Update case status
     */
    public function updateStatus($id, $status)
    {
        try {
            $technicianId = auth()->user()->id;
            $case = $this->technicianService->updateCaseStatus($id, $status, $technicianId);
            
            return redirect()->back()->with('success', 'Case status updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating case status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update case status');
        }
    }

    /**
     * Add comment to case
     */
    public function addComment(AddCommentRequest $request)
    {
        try {
            $validated = $request->validated();
            $comment = $this->technicianService->addCaseComment(
                $validated['case_id'],
                auth()->user()->id,
                $validated['comment']
            );

            if ($request->ajax()) {
                $userPhoto = $comment->user->photo 
                    ? asset('storage/' . $comment->user->photo) 
                    : asset('assets/img/avatars/default-avatar.png');
                    
                return response()->json([
                    'success' => true,
                    'comment' => $comment->comment,
                    'user_photo' => $userPhoto,
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

    /**
     * Get comments for a case
     */
    public function getComments($id)
    {
        try {
            $comments = Comment::where('case_id', $id)
                              ->with('user')
                              ->latest()
                              ->get();

            return response()->json(['comments' => $comments]);
        } catch (Exception $e) {
            Log::error('Error getting comments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load comments'], 500);
        }
    }

  

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'draft' => '<span class="badge bg-label-secondary">Draft</span>',
            'in_planning' => '<span class="badge bg-label-info">In Planning</span>',
            'approval' => '<span class="badge bg-label-primary">Approval</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
            'in_production' => '<span class="badge bg-label-success">In Production</span>',
            'shipped' => '<span class="badge bg-label-success">Shipped</span>'
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons($case)
    {
        $buttons = '<div class="dropdown">
            <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="' . route('technician.cases.show', $case->id) . '" class="dropdown-item waves-effect">View</a></li>';

        if ($case->status === 'pending') {
            $buttons .= '<li><a href="' . route('technician.cases.updateStatus', [$case->id, 'approval']) . '" class="dropdown-item waves-effect text-success">Accept</a></li>';
            $buttons .= '<li><a href="' . route('technician.cases.updateStatus', [$case->id, 'rejected']) . '" class="dropdown-item waves-effect text-danger">Reject</a></li>';
        }

        $buttons .= '</ul></div>';

        return $buttons;
    }

    /**
     * Send WeTransfer notification to laboratory
     */
    public function sendWeTransferNotification(Request $request)
    {
        try {
            Log::info('WeTransfer notification request received', [
                'request_data' => $request->all(),
                'technician_id' => auth()->id()
            ]);

            $request->validate([
                'case_id' => 'required|exists:case_patients,id',
                'wetransfer_link' => 'required|url',
                'message' => 'required|string|max:1000'
            ]);

            $technicianId = auth()->user()->id;
            
            // Verify the case belongs to the authenticated technician
            $case = \App\Models\CasePatient::where('id', $request->case_id)
                                          ->where('technician_id', $technicianId)
                                          ->with(['laboratory', 'patient', 'doctor'])
                                          ->first();

            if (!$case) {
                Log::warning('Unauthorized access to case', [
                    'case_id' => $request->case_id,
                    'technician_id' => $technicianId
                ]);
                return response()->json(['error' => 'Unauthorized access to case'], 403);
            }

            if (!$case->laboratory) {
                Log::warning('No laboratory assigned to case', ['case_id' => $case->id]);
                return response()->json(['error' => 'No laboratory assigned to this case'], 400);
            }

            Log::info('Creating WeTransfer notification record');

            // Create WeTransfer notification record
            $notification = WeTransferNotification::create([
                'case_id' => $case->id,
                'technician_id' => $technicianId,
                'laboratory_id' => $case->laboratory_id,
                'wetransfer_link' => $request->wetransfer_link,
                'message' => $request->message,
                'sent_at' => now()
            ]);

            Log::info('WeTransfer notification record created', ['notification_id' => $notification->id]);

            // Prepare email data
            $emailData = [
                'case' => $case,
                'technician' => auth()->user(),
                'wetransfer_link' => $request->wetransfer_link,
                'message' => $request->message,
                'notification_id' => $notification->id
            ];

            // Send email to laboratory
            try {
                Log::info('Attempting to send email to laboratory', [
                    'laboratory_email' => $case->laboratory->email
                ]);
                
                Mail::to($case->laboratory->email)
                    ->send(new WeTransferNotificationMail($emailData));
                    
                Log::info('Email sent successfully to laboratory');
            } catch (Exception $emailException) {
                Log::error('Email sending failed', [
                    'error' => $emailException->getMessage(),
                    'laboratory_email' => $case->laboratory->email,
                    'trace' => $emailException->getTraceAsString()
                ]);
                // Continue without failing - we have the notification record
            }

            // Create system notification for laboratory
            try {
                Log::info('Creating system notification');
                \App\Models\Notification::create([
                    'user_id' => $case->laboratory_id,
                    'message' => "New WeTransfer files available for case #{$case->case_id}",
                    'type' => 'wetransfer_notification',
                    'data' => json_encode([
                        'case_id' => $case->id,
                        'technician_name' => auth()->user()->name,
                        'wetransfer_link' => $request->wetransfer_link
                    ])
                ]);
                Log::info('System notification created successfully');
            } catch (Exception $notificationException) {
                Log::error('System notification creation failed', [
                    'error' => $notificationException->getMessage()
                ]);
            }

            Log::info('WeTransfer notification process completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'WeTransfer notification sent successfully to laboratory',
                'notification_id' => $notification->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error sending WeTransfer notification: ' . $e->getMessage(), [
                'case_id' => $request->case_id ?? null,
                'technician_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent WeTransfer links for a case
     */
    public function getRecentWeTransferLinks($id)
    {
        try {
            $technicianId = auth()->user()->id;
            
            // Verify the case belongs to the authenticated technician
            $case = \App\Models\CasePatient::where('id', $id)
                                          ->where('technician_id', $technicianId)
                                          ->first();

            if (!$case) {
                return response()->json(['error' => 'Unauthorized access to case'], 403);
            }

            $recentLinks = WeTransferNotification::where('case_id', $id)
                                                           ->where('technician_id', $technicianId)
                                                           ->orderBy('created_at', 'desc')
                                                           ->limit(5)
                                                           ->get(['wetransfer_link', 'created_at'])
                                                           ->map(function($item) {
                                                               return [
                                                                   'wetransfer_link' => $item->wetransfer_link,
                                                                   'created_at' => $item->created_at->format('d-m-Y H:i')
                                                               ];
                                                           });

            return response()->json(['links' => $recentLinks]);

        } catch (Exception $e) {
            Log::error('Error getting recent WeTransfer links: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load recent links'], 500);
        }
    }
}
