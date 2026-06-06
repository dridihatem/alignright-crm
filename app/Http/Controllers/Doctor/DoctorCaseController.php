<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Services\PatientService;
use App\Repositories\CaseRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use App\Jobs\ProcessFileUpload;
use App\Jobs\CreateAndUploadCaseZip;
use App\Models\FileUpload;
use App\Models\CasePatient;
use App\Http\Controllers\Concerns\GroupsCasesByPatient;
use App\Http\Controllers\Concerns\GeneratesCaseIdentifiers;

class DoctorCaseController extends Controller
{
    use GroupsCasesByPatient;
    use GeneratesCaseIdentifiers;

    protected $caseService;
    protected $patientService;
    protected $caseRepository;
    protected $patientRepository;
    protected $userRepository;

    public function __construct(
        CaseService $caseService,
        PatientService $patientService,
        CaseRepository $caseRepository,
        PatientRepository $patientRepository,
        UserRepository $userRepository
    ) {
        $this->caseService = $caseService;
        $this->patientService = $patientService;
        $this->caseRepository = $caseRepository;
        $this->patientRepository = $patientRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display the cases list
     */
    public function index()
    {
        try {
            $doctorId = auth()->user()->id;
            
            // Get case statistics
            $caseStats = $this->getCaseStats($doctorId);
            
            // Get invoice statistics
            $invoiceStats = $this->getInvoiceStats($doctorId);
            
            // Get patients for filter dropdown - using the same approach as existing DoctorsController
            $listpatients_by_doctor = \App\Models\CasePatient::where('doctor_id', $doctorId)->get();
            $listpatients_by_doctor_id = $listpatients_by_doctor->pluck('patient_id');
            $patients = \App\Models\Patient::whereIn('id', $listpatients_by_doctor_id)->get();

            // Build patient-grouped cases (admin-style, collapsible)
            $cases = CasePatient::with('patient')->where('doctor_id', $doctorId)->get();
            $patientGroups = $this->buildPatientGroups($cases, [
                'show'   => 'doctor.cases.show',
                'edit'   => 'doctor.cases.edit',
                'delete' => 'doctor.cases.delete',
            ]);

            return view('doctor.cases.index', compact('caseStats', 'invoiceStats', 'patients', 'patientGroups'));
        } catch (Exception $e) {
            Log::error('Error displaying cases index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load cases page');
        }
    }

    /**
     * Get cases for DataTable
     */
    public function getCases(Request $request): JsonResponse
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
                ->addColumn('date', function ($case) {
                    return $case->date ? date('Y-m-d', strtotime($case->date)) : 'N/A';
                })
                ->addColumn('accepted_date', function ($case) {
                    return $case->accepted_date ? date('Y-m-d', strtotime($case->accepted_date)) : 'N/A';
                })
                ->addColumn('rejected_date', function ($case) {
                    return $case->rejected_date ? date('Y-m-d', strtotime($case->rejected_date)) : 'N/A';
                })
                ->addColumn('status_badge', function ($case) {
                    return $this->getStatusBadge($case->status);
                })
                ->addColumn('actions', function ($case) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" 
                                id="dropdownMenuButton" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item waves-effect" href="'.route('doctor.cases.show', $case->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('doctor.cases.edit', $case->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('doctor.cases.delete', $case->id).'">'.__('master.delete').'</a></li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting cases: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve cases'], 500);
        }
    }

    /**
     * Show the form for creating a new case
     */
    public function create()
    {
        try {
            $doctorId = auth()->user()->id;
            
            // Get patients for this doctor (same approach as DoctorsController)
            $listpatients_by_doctor = \App\Models\CasePatient::where('doctor_id', $doctorId)->get();
            $listpatients_by_doctor_id = $listpatients_by_doctor->pluck('patient_id');
            $patients = \App\Models\Patient::whereIn('id', $listpatients_by_doctor_id)->get();

            // Get tooth problems for the create form
            $toothProblems = \App\Models\ToothProblem::all();
            
            // Get technicians and laboratories for this doctor
            $technicians = \App\Models\User::where('role_id', 3)->where('status', 'active')->where('doctor_id', $doctorId)->get();
            $laboratories = \App\Models\User::where('role_id', 4)->where('status', 'active')->where('doctor_id', $doctorId)->get();

            // Generate reduced, unique identifiers (admin-style: C/P + YYMMDD + -NNN)
            $generatedCaseId = $this->generateCaseId();
            $generatedReference = $this->generatePatientReference();

            return view('doctor.cases.create', compact('patients', 'toothProblems', 'technicians', 'laboratories', 'generatedCaseId', 'generatedReference'));
        } catch (Exception $e) {
            Log::error('Error showing create case form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load create case form');
        }
    }

    /**
     * Store a newly created case
     */
    public function store(Request $request)
    {
        try {
            // Set higher limits for this operation
            ini_set('memory_limit', '512M');
            set_time_limit(300); // 5 minutes
            
            // Debug: Log the incoming request data
            Log::info('Case creation request data:', [
                'all_data' => $request->all(),
                'patient_type' => $request->input('patient_type'),
                'patient_id' => $request->input('patient_id'),
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'gender' => $request->input('gender'),
                'phone' => $request->input('phone'),
                'treatment_type' => $request->input('treatment_type'),
                'files_count' => count($request->allFiles()),
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
            ]);
            
            // Create validation rules based on patient type
            $validationRules = [
                'case_id' => 'required|string|max:255',
                'patient_type' => 'required|in:new,existing',
                'treatment_type' => 'required|string|max:255',
                'treatment_overjet' => 'required|string|max:255',
                'treatment_overbite' => 'required|string|max:255',
                'treatment_midline' => 'required|string|max:255',
                'treatment_irp' => 'required|string|max:255',
                'treatment_attachments' => 'required|string|max:255',
                'doctor_instruction' => 'nullable|string',
                'tooth_problems' => 'nullable|array',
                'type_of_scan' => 'required|in:intraoral,desktop,silicone',
            ];

            // Add patient-specific validation rules
            if ($request->input('patient_type') === 'existing') {
                $validationRules['patient_id'] = 'required|exists:patients,id';
                // For existing patients, make new patient fields nullable and prevent them from being filled
                $validationRules['name'] = 'nullable|string|max:255';
                $validationRules['surname'] = 'nullable|string|max:255';
                $validationRules['gender'] = 'nullable|in:male,female,other';
                $validationRules['phone'] = 'nullable|string|max:20';
                $validationRules['email'] = 'nullable|email|max:255';
                $validationRules['reference'] = 'nullable|string|max:255';
                $validationRules['birth_day'] = 'nullable|date';
                $validationRules['address'] = 'nullable|string';
                $validationRules['city'] = 'nullable|string|max:255';
                $validationRules['state'] = 'nullable|string|max:255';
                $validationRules['zip'] = 'nullable|string|max:20';
                $validationRules['country'] = 'nullable|string|max:255';
            } else {
                $validationRules['patient_id'] = 'nullable|not_exists:patients,id';
                $validationRules['name'] = 'required|string|max:255';
                $validationRules['surname'] = 'required|string|max:255';
                $validationRules['gender'] = 'required|in:male,female,other';
                $validationRules['phone'] = 'nullable|string|max:20|unique:patients,phone';
                $validationRules['email'] = 'nullable|email|max:255|unique:patients,email';
                $validationRules['reference'] = 'required|string|max:255|unique:patients,reference';
                $validationRules['birth_day'] = 'nullable|date';
                $validationRules['address'] = 'nullable|string';
                $validationRules['city'] = 'nullable|string|max:255';
                $validationRules['state'] = 'nullable|string|max:255';
                $validationRules['zip'] = 'nullable|string|max:20';
                $validationRules['country'] = 'nullable|string|max:255';
            }

            // Debug: Log validation rules being applied
            Log::info('Validation rules being applied:', [
                'patient_type' => $request->input('patient_type'),
                'validation_rules' => $validationRules
            ]);
            
            $validated = $request->validate($validationRules);
            
            // Additional custom validation
            if ($request->input('patient_type') === 'new' && $request->input('patient_id')) {
                throw ValidationException::withMessages([
                    'patient_id' => ['Cannot select an existing patient when creating a new patient. Please clear the patient selection or choose "Existing Patient".']
                ]);
            }
            
            if ($request->input('patient_type') === 'existing' && !$request->input('patient_id')) {
                throw ValidationException::withMessages([
                    'patient_id' => ['Please select an existing patient.']
                ]);
            }

            $doctorId = auth()->user()->id;
            
            Log::info('About to create case', ['doctor_id' => $doctorId, 'validated_data_keys' => array_keys($validated)]);
            
            $case = $this->caseService->createCase($validated, $doctorId);
            
            Log::info('Case created successfully', ['case_id' => $case->id, 'case_id_string' => $case->case_id]);

            Log::info('Case created successfully - no file processing needed', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id
            ]);

            Log::info('Case creation completed successfully', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'final_memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
            ]);

            return redirect()->route('doctor.cases.upload-files', $case->id)
                           ->with('success', 'Case created successfully! Now upload your files.');
                           
        } catch (ValidationException $e) {
            Log::warning('Case creation validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()
                           ->withErrors($e->errors())
                           ->withInput()
                           ->with('error', 'Please correct the validation errors below');
        } catch (Exception $e) {
            Log::error('Error creating case: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
            ]);
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create case: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified case
     */
    public function show($id)
    {
        try {
            $case = $this->caseRepository->findById($id);
            
            // Check if case exists
            if (!$case) {
                return redirect()->back()->with('error', 'Case not found');
            }
            
            // Check if the case belongs to the authenticated doctor
            $currentUserId = auth()->id();
            if (!$currentUserId || (int)$case->doctor_id !== (int)$currentUserId) {
                Log::warning('Unauthorized access attempt to case', [
                    'case_id' => $id,
                    'case_doctor_id' => $case->doctor_id,
                    'current_user_id' => $currentUserId,
                    'user_authenticated' => auth()->check()
                ]);
                return redirect()->back()->with('error', 'Unauthorized access to case');
            }

            // Get tooth problems for this case
            $toothProblemscase = \App\Models\ToothProblemCase::where('case_id', $id)->with('tooth_problem')->get();
            
            // Get comments for this case
            $comments = \App\Models\Comment::where('case_id', $id)->with('user')->latest()->get();
            
            // Get files for this case organized by rubrique
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

            return view('doctor.cases.show', compact(
                'case', 'toothProblemscase', 'comments', 
                'files_clinical', 'files_radiographs', 'other_files', 'stl_files', 'count_stl_files', 'count_clinical_files', 'count_radiograph_files', 'count_other_files'
            ));
        } catch (Exception $e) {
            Log::error('Error showing case: ' . $e->getMessage(), [
                'case_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Case not found');
        }
    }

    /**
     * Scoped header search: only the authenticated doctor's own cases/patients.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $cases = CasePatient::with(['patient'])
            ->where('doctor_id', auth()->id())
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
                'status' => $case->status,
                'url' => route('doctor.cases.show', $case->id),
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Doctor requests/demands a finition for the case.
     * The assigned technician then uploads finition files + a description.
     */
    public function requestFinition(Request $request, $id)
    {
        try {
            $case = CasePatient::findOrFail($id);

            if ((int) $case->doctor_id !== (int) auth()->id()) {
                return redirect()->back()->with('error', __('master.unauthorized_access_to_case'));
            }

            $validated = $request->validate([
                'finition_request_note' => 'nullable|string|max:2000',
            ]);

            $case->finition_requested_at = now();
            $case->finition_requested_by = auth()->id();
            $case->finition_request_note = $validated['finition_request_note'] ?? null;
            $case->save();

            // Notify the assigned technician (best effort).
            try {
                if ($case->technician_id) {
                    // Build a message that carries the doctor's actual demand text so it
                    // shows directly in the technician's notification bell.
                    $finitionMessage = $case->case_id . ' - ' . __('master.finition_request_message');
                    if (!empty($case->finition_request_note)) {
                        // Keep the bell message within the column limit (varchar 255);
                        // the full note remains visible in the case finition section.
                        $finitionMessage .= ' — ' . \Illuminate\Support\Str::limit($case->finition_request_note, 180);
                    }

                    \App\Models\Notification::create([
                        'title' => $case->case_id . ' - ' . __('master.finition_requested'),
                        'message' => $finitionMessage,
                        'type' => 'finition_request',
                        'status' => 'active',
                        'case_id' => $case->id,
                        'doctor_id' => $case->doctor_id,
                        'technician_id' => $case->technician_id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Finition request notification failed: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', __('master.finition_requested_successfully'));
        } catch (Exception $e) {
            Log::error('Error requesting finition: ' . $e->getMessage(), ['case_id' => $id]);
            return redirect()->back()->with('error', __('master.failed_to_request_finition'));
        }
    }

    /**
     * Show the form for editing the specified case
     */
    public function edit($id)
    {
        try {
            $case = $this->caseRepository->findById($id);
            
            // Check if case exists
            if (!$case) {
                return redirect()->back()->with('error', 'Case not found');
            }
            
            // Check if the case belongs to the authenticated doctor
            $currentUserId = auth()->id();
            if (!$currentUserId || (int)$case->doctor_id !== (int)$currentUserId) {
                Log::warning('Unauthorized access attempt to edit case', [
                    'case_id' => $id,
                    'case_doctor_id' => $case->doctor_id,
                    'current_user_id' => $currentUserId,
                    'user_authenticated' => auth()->check()
                ]);
                return redirect()->back()->with('error', 'Unauthorized access to case');
            }

            $doctorId = auth()->user()->id;
            // Get patients for filter dropdown - using the same approach as existing DoctorsController
            $listpatients_by_doctor = \App\Models\CasePatient::where('doctor_id', $doctorId)->get();
            $listpatients_by_doctor_id = $listpatients_by_doctor->pluck('patient_id');
            $patients = \App\Models\Patient::whereIn('id', $listpatients_by_doctor_id)->get();
            
            $technicians = $this->userRepository->getByRole(3);
            $laboratories = $this->userRepository->getByRole(4);

            // Get tooth problems for this case (required by edit view)
            $toothProblemscase = \App\Models\ToothProblemCase::where('case_id', $id)->with('tooth_problem')->get();
            
            // Get all tooth problems (required by edit view)
            $toothProblems = \App\Models\ToothProblem::all();

            // Pre-built map of existing problems for the edit form's JS state (keyed by tooth number)
            $existingToothProblems = $toothProblemscase->mapWithKeys(function ($tp) {
                return [(string) $tp->tooth_number => [
                    'problem_id'   => (string) $tp->tooth_problem_id,
                    'problem_text' => $tp->tooth_problem->name ?? '',
                    'notes'        => $tp->tooth_notes ?? '',
                ]];
            });

            return view('doctor.cases.edit', compact('case', 'patients', 'technicians', 'laboratories', 'toothProblemscase', 'toothProblems', 'existingToothProblems'));
        } catch (Exception $e) {
            Log::error('Error showing edit case form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Case not found');
        }
    }

    /**
     * Update the specified case
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'treatment_type' => 'required|string|max:255',
                'doctor_instruction' => 'nullable|string',
                'technician_id' => 'nullable|exists:users,id',
                'laboratory_id' => 'nullable|exists:users,id',
            ]);

            // Sync tooth problems when submitted (notes come in a separate array)
            if ($request->has('tooth_problems')) {
                $toothProblems = $request->input('tooth_problems', []);
                $toothNotes = $request->input('tooth_notes', []);
                foreach ($toothProblems as $toothNumber => $problem) {
                    $toothProblems[$toothNumber]['notes'] = $toothNotes[$toothNumber]['notes'] ?? ($problem['notes'] ?? null);
                }
                $validated['tooth_problems'] = $toothProblems;
            }

            $case = $this->caseService->updateCase($id, $validated);

            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Case updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating case: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update case: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified case
     */
    public function destroy($id)
    {
        try {
            $doctorId = auth()->user()->id;
            $this->caseService->deleteCase($id, $doctorId);
            return redirect()->route('doctor.cases')
                           ->with('success', 'Case deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting case: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete case: ' . $e->getMessage());
        }
    }

    /**
     * Export cases to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $doctorId = auth()->user()->id;
            $filters = $request->all();
            $filters['doctor_id'] = $doctorId;

            $cases = $this->caseRepository->getWithFilters($filters, ['patient', 'technician', 'laboratory'])->get();

            $pdf = Pdf::loadView('doctor.cases.pdf', compact('cases'));
            return $pdf->download('cases-' . date('Y-m-d') . '.pdf');
        } catch (Exception $e) {
            Log::error('Error exporting cases to PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export cases');
        }
    }

    /**
     * Get case statistics for doctor
     */
    private function getCaseStats($doctorId): array
    {
        try {
            $cases = $this->caseRepository->getByDoctor($doctorId);
            
            return [
                'total' => $cases->count(),
                'pending' => $cases->whereIn('status', ['pending', 'in_planning', 'approval'])->count(),
                'completed' => $cases->whereIn('status', ['in_production', 'shipped'])->count(),
                'draft' => $cases->where('status', 'draft')->count(),
                'rejected' => $cases->where('status', 'rejected')->count(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting case stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'draft' => 0,
                'rejected' => 0,
            ];
        }
    }

    /**
     * Get invoice statistics for doctor
     */
    private function getInvoiceStats($doctorId): array
    {
        try {
            $cases = $this->caseRepository->getByDoctor($doctorId);
            $caseIds = $cases->pluck('id');
            
            $invoices = \App\Models\Invoice::whereIn('case_id', $caseIds);
            
            return [
                'total' => $invoices->count(),
                'paid' => $invoices->where('status', 'paid')->count(),
                'pending' => $invoices->where('status', 'pending')->count(),
                'overdue' => $invoices->where('status', 'overdue')->count(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting invoice stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'paid' => 0,
                'pending' => 0,
                'overdue' => 0,
            ];
        }
    }

    /**
     * Get invoices for DataTable
     */
    public function getInvoices(Request $request): JsonResponse
    {
        try {
            $doctorId = auth()->user()->id;
            $cases = $this->caseRepository->getByDoctor($doctorId);
            $caseIds = $cases->pluck('id');
            
            $query = \App\Models\Invoice::whereIn('case_id', $caseIds)
                                       ->with(['case.patient']);

            return DataTables::of($query)
                ->addColumn('case_id', function ($invoice) {
                    return $invoice->case->case_id ?? 'N/A';
                })
                ->addColumn('patient_name', function ($invoice) {
                    return $invoice->case->patient->name ?? 'N/A';
                })
                ->addColumn('amount', function ($invoice) {
                    return 'Tnd ' . number_format($invoice->total_amount, 2);
                })
                ->addColumn('due_date', function ($invoice) {
                    return $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : 'N/A';
                })
                ->addColumn('status_badge', function ($invoice) {
                    return $this->getInvoiceStatusBadge($invoice->status);
                })
                ->addColumn('actions', function ($invoice) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" 
                                id="dropdownMenuButton" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item waves-effect" href="'.route('doctor.invoices.show', $invoice->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('doctor.invoices.print', $invoice->id).'">'.__('master.print').'</a></li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting invoices: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve invoices'], 500);
        }
    }

    /**
     * Get invoice status badge HTML
     */
    private function getInvoiceStatusBadge($status): string
    {
        $badges = [
            'paid' => '<span class="badge bg-label-success">Paid</span>',
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'overdue' => '<span class="badge bg-label-danger">Overdue</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }

    /**
     * Show invoice details
     */
    public function showInvoice($id)
    {
        try {
            $doctorId = auth()->user()->id;
            $invoice = \App\Models\Invoice::with(['case.patient', 'case.doctor'])
                                         ->whereHas('case', function($query) use ($doctorId) {
                                             $query->where('doctor_id', $doctorId);
                                         })
                                         ->findOrFail($id);

            return view('doctor.invoices.show', compact('invoice'));
        } catch (Exception $e) {
            Log::error('Error showing invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load invoice details');
        }
    }

    /**
     * Print invoice
     */
    public function printInvoice($id)
    {
        try {
            $doctorId = auth()->user()->id;
            $invoice = \App\Models\Invoice::with(['case.patient', 'case.doctor'])
                                         ->whereHas('case', function($query) use ($doctorId) {
                                             $query->where('doctor_id', $doctorId);
                                         })
                                         ->findOrFail($id);

            $pdf = PDF::loadView('doctor.invoices.print', compact('invoice'));
            return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
        } catch (Exception $e) {
            Log::error('Error printing invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice PDF');
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

            // Verify the case belongs to the authenticated doctor
            $case = \App\Models\CasePatient::where('id', $request->case_id)
                                          ->where('doctor_id', auth()->user()->id)
                                          ->first();

            if (!$case) {
                return response()->json(['error' => 'Unauthorized access to case'], 403);
            }

            $comment = Comment::create([
                'case_id' => $request->case_id,
                'comment' => $request->comment,
                'user_id' => auth()->user()->id,
                'type' => 'doctor_update'
            ]);

            // Load user relationship
            $comment->load('user');

            if ($request->ajax()) {
                $userPhoto = $comment->user->photo 
                    ? asset('storage/' . $comment->user->photo) 
                    : asset('assets/img/avatars/default-avatar.png');

                return response()->json([
                    'success' => true,
                    'comment' => $comment->comment,
                    'user_photo' => $userPhoto,
                    'user_name' => $comment->user->name,
                    'user_role' => $comment->user->role->name ?? 'doctor',
                    'date' => $comment->created_at->format('d-m-Y H:i:s')
                ]);
            }

            return redirect()->back()->with('success', 'Comment added successfully');
        } catch (Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add comment'], 500);
        }
    }

    /**
     * Get comments for a case
     */
    public function getComments($id)
    {
        try {
            // Verify the case belongs to the authenticated doctor
            $case = \App\Models\CasePatient::where('id', $id)
                                          ->where('doctor_id', auth()->user()->id)
                                          ->first();

            if (!$case) {
                return response()->json(['error' => 'Unauthorized access to case'], 403);
            }

            $comments = Comment::where('case_id', $id)
                              ->with('user.role')
                              ->latest()
                              ->get();

            return response()->json(['comments' => $comments]);
        } catch (Exception $e) {
            Log::error('Error getting comments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load comments'], 500);
        }
    }

    /**
     * Accept price for a case
     */
    public function acceptPrice(Request $request, $id)
    {
        try {
            $doctorId = auth()->user()->id;
            $case = $this->caseService->acceptPrice($id, $doctorId);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Price accepted successfully',
                    'case' => $case
                ]);
            }

            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Price accepted successfully. Case is now ready for treatment planning.');
        } catch (Exception $e) {
            Log::error('Error accepting price: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()->with('error', 'Failed to accept price: ' . $e->getMessage());
        }
    }

    /**
     * Reject price for a case
     */
    public function rejectPrice(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);
            
            $doctorId = auth()->user()->id;
            $case = $this->caseService->rejectPrice($id, $doctorId, $request->reason);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Price rejected successfully',
                    'case' => $case
                ]);
            }

            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Price rejected successfully.');
        } catch (Exception $e) {
            Log::error('Error rejecting price: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()->with('error', 'Failed to reject price: ' . $e->getMessage());
        }
    }

    /**
     * Accept treatment plan for a case
     */
    public function acceptTreatmentPlan($id)
    {
        try {
            $doctorId = auth()->user()->id;
            $case = $this->caseService->acceptTreatmentPlan($id, $doctorId);

            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Treatment plan accepted successfully. Case is now in production.');
        } catch (Exception $e) {
            Log::error('Error accepting treatment plan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to accept treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Get cases waiting for price acceptance
     */
    public function getCasesWaitingForPriceAcceptance()
    {
        try {
            $doctorId = auth()->user()->id;
            $cases = $this->caseService->getCasesWaitingForPriceAcceptance($doctorId);

            return response()->json(['cases' => $cases]);
        } catch (Exception $e) {
            Log::error('Error getting cases waiting for price acceptance: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve cases'], 500);
        }
    }

    /**
     * Get cases waiting for treatment plan acceptance
     */
    public function getCasesWaitingForTreatmentPlanAcceptance()
    {
        try {
            $doctorId = auth()->user()->id;
            $cases = $this->caseService->getCasesWaitingForTreatmentPlanAcceptance($doctorId);

            return response()->json(['cases' => $cases]);
        } catch (Exception $e) {
            Log::error('Error getting cases waiting for treatment plan acceptance: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve cases'], 500);
        }
    }

    /**
     * Show price acceptance page
     */
    public function showPriceAcceptance($id)
    {
        try {
            $case = $this->caseRepository->findById($id);
            
            // Check if case exists
            if (!$case) {
                return redirect()->back()->with('error', 'Case not found');
            }
            
            // Check if the case belongs to the authenticated doctor
            $currentUserId = auth()->id();
            if (!$currentUserId || (int)$case->doctor_id !== (int)$currentUserId) {
                return redirect()->back()->with('error', 'Unauthorized access to case');
            }

            // Check if case has price set and is in pending status
            if (!$case->price || $case->status !== 'pending') {
                return redirect()->back()->with('error', 'Case does not have a price set or is not in pending status');
            }

            return view('doctor.cases.accept_price', compact('case'));
        } catch (Exception $e) {
            Log::error('Error showing price acceptance page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load price acceptance page');
        }
    }


    /**
     * Show treatment plan acceptance page
     */
    public function showTreatmentPlanAcceptance($id)
    {
        try {
            $case = $this->caseRepository->findById($id);
            
            // Check if case exists
            if (!$case) {
                return redirect()->back()->with('error', 'Case not found');
            }
            
            // Check if the case belongs to the authenticated doctor
            $currentUserId = auth()->id();
            if (!$currentUserId || (int)$case->doctor_id !== (int)$currentUserId) {
                return redirect()->back()->with('error', 'Unauthorized access to case');
            }

            // Check if case is in approval status
            if ($case->status !== 'approval') {
                return redirect()->back()->with('error', 'Case is not in approval status');
            }

            // Get treatment plans for this case
            $treatmentPlans = $case->treatmentType()->where('status', 'pending')->get();

            if ($treatmentPlans->isEmpty()) {
                return redirect()->back()->with('error', 'No treatment plans available for acceptance');
            }

            return view('doctor.cases.accept_treatment_plan', compact('case', 'treatmentPlans'));
        } catch (Exception $e) {
            Log::error('Error showing treatment plan acceptance page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load treatment plan acceptance page');
        }
    }

    /**
     * Queue files for background upload
     */
    private function queueFilesForBackgroundUpload($caseId, array $files, $userId)
    {
        $queuedFiles = [];
        
        // Create temp_uploads directory if it doesn't exist
        $tempDir = storage_path('app/temp_uploads');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($files as $fieldName => $fileData) {
            if (is_array($fileData)) {
                // Handle multiple files
                foreach ($fileData as $index => $file) {
                    $queuedFiles[] = $this->createFileUploadRecord($file, $caseId, $fieldName . '_' . $index, $userId);
                }
            } else {
                // Handle single file
                $queuedFiles[] = $this->createFileUploadRecord($fileData, $caseId, $fieldName, $userId);
            }
        }

        return $queuedFiles;
    }

    /**
     * Create FileUpload record and queue background job
     */
    private function createFileUploadRecord($file, $caseId, $fieldName, $userId)
    {
        // Generate unique temp filename
        $tempFilename = uniqid() . '_' . $file->getClientOriginalName();
        $tempPath = storage_path('app/temp_uploads/' . $tempFilename);

        // Move file to temp storage
        move_uploaded_file($file->getPathname(), $tempPath);

        // Create FileUpload record
        $fileUpload = FileUpload::create([
            'case_id' => $caseId,
            'wich_rubrique' => $fieldName,
            'file_name' => $file->getClientOriginalName(),
            'temp_filename' => $tempFilename,
            'type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'pending',
            'storage_type' => 'google_drive'
        ]);

        // Dispatch background job
        ProcessFileUpload::dispatch($fileUpload->id, $userId);

        Log::info('File queued for background upload', [
            'file_upload_id' => $fileUpload->id,
            'case_id' => $caseId,
            'field_name' => $fieldName,
            'file_name' => $file->getClientOriginalName(),
            'temp_filename' => $tempFilename
        ]);

        return $fileUpload;
    }

    /**
     * Get upload progress for a case
     */
    public function getUploadProgress($caseId)
    {
        try {
            $case = $this->caseRepository->findById($caseId);
            
            // Check if case belongs to authenticated doctor
            if (!$case || (int)$case->doctor_id !== (int)auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $fileUploads = FileUpload::where('case_id', $caseId)->get();
            
            $progress = [
                'total_files' => $fileUploads->count(),
                'completed_files' => $fileUploads->where('status', 'completed')->count(),
                'processing_files' => $fileUploads->where('status', 'processing')->count(),
                'pending_files' => $fileUploads->where('status', 'pending')->count(),
                'failed_files' => $fileUploads->where('status', 'failed')->count(),
                'files' => $fileUploads->map(function($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->file_name,
                        'rubrique' => $file->wich_rubrique,
                        'status' => $file->status,
                        'error_message' => $file->error_message,
                        'google_drive_link' => $file->google_drive_link,
                        'uploaded_at' => $file->uploaded_at ? $file->uploaded_at->format('Y-m-d H:i:s') : null,
                    ];
                })
            ];

            $progress['percentage'] = $progress['total_files'] > 0 
                ? round(($progress['completed_files'] / $progress['total_files']) * 100) 
                : 100;

            return response()->json($progress);
            
        } catch (Exception $e) {
            Log::error('Error getting upload progress: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get upload progress'], 500);
        }
    }

    /**
     * Show the file upload page for a case
     */
    public function showUploadFiles($id)
    {
        try {
            $doctorId = auth()->id();
            $case = CasePatient::where('doctor_id', $doctorId)->findOrFail($id);

            return view('doctor.cases.upload-files', compact('case'));
            
        } catch (Exception $e) {
            Log::error('Error showing upload files page', [
                'case_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('doctor.cases.index')
                           ->with('error', 'Case not found or access denied.');
        }
    }

    /**
     * Handle file uploads for a case
     */
    public function uploadFiles(Request $request, $id)
    {
        try {
            $doctorId = auth()->id();
            $case = CasePatient::where('doctor_id', $doctorId)->findOrFail($id);

            Log::info('Starting file upload for case', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'doctor_id' => $doctorId,
                'is_ajax' => $request->ajax(),
                'method' => $request->method()
            ]);

            // Handle link inputs and save to FileUpload model
            $this->processFileLinks($request, $case->id);

            // Return appropriate response based on request type
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Files and links saved successfully!'
                ]);
            }

            return redirect()->route('doctor.cases.show', $case->id)
                           ->with('success', 'Files and links saved successfully!');
                           
        } catch (Exception $e) {
            Log::error('Error uploading files', [
                'case_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()
                           ->with('error', 'Error uploading files: ' . $e->getMessage());
        }
    }

    /**
     * Process file links and save to FileUpload model
     */
    private function processFileLinks(Request $request, $caseId)
    {
        // STL Links
        $stlLinks = [
            'stl_upper' => ['name' => $request->input('stl_upper_name'), 'url' => $request->input('stl_upper_url'), 'rubrique' => 'stl_scan'],
            'stl_lower' => ['name' => $request->input('stl_lower_name'), 'url' => $request->input('stl_lower_url'), 'rubrique' => 'stl_scan'],
            'stl_bite' => ['name' => $request->input('stl_bite_name'), 'url' => $request->input('stl_bite_url'), 'rubrique' => 'stl_scan']
        ];

        foreach ($stlLinks as $type => $data) {
            if (!empty($data['name']) && !empty($data['url'])) {
                FileUpload::create([
                    'case_id' => $caseId,
                    'name' => $data['name'],
                    'url' => $data['url'],
                    'wich_rubrique' => $data['rubrique'],
                    'type' => 'link',
                    'status' => 'completed'
                ]);
                
                Log::info('STL link saved', [
                    'case_id' => $caseId,
                    'type' => $type,
                    'name' => $data['name'],
                    'url' => $data['url']
                ]);
            }
        }

        // Clinical Photos Links
        $clinicalNames = $request->input('clinical_names', []);
        $clinicalUrls = $request->input('clinical_urls', []);
        
        for ($i = 0; $i < count($clinicalNames); $i++) {
            if (!empty($clinicalNames[$i]) && !empty($clinicalUrls[$i])) {
                FileUpload::create([
                    'case_id' => $caseId,
                    'name' => $clinicalNames[$i],
                    'url' => $clinicalUrls[$i],
                    'wich_rubrique' => 'clinical_photo',
                    'type' => 'link',
                    'status' => 'completed'
                ]);
                
                Log::info('Clinical photo link saved', [
                    'case_id' => $caseId,
                    'name' => $clinicalNames[$i],
                    'url' => $clinicalUrls[$i]
                ]);
            }
        }

        // Radiographs Links
        $radiographsNames = $request->input('radiographs_names', []);
        $radiographsUrls = $request->input('radiographs_urls', []);
        
        for ($i = 0; $i < count($radiographsNames); $i++) {
            if (!empty($radiographsNames[$i]) && !empty($radiographsUrls[$i])) {
                FileUpload::create([
                    'case_id' => $caseId,
                    'name' => $radiographsNames[$i],
                    'url' => $radiographsUrls[$i],
                    'wich_rubrique' => 'radiograph',
                    'type' => 'link',
                    'status' => 'completed'
                ]);
                
                Log::info('Radiograph link saved', [
                    'case_id' => $caseId,
                    'name' => $radiographsNames[$i],
                    'url' => $radiographsUrls[$i]
                ]);
            }
        }

        // Other Files Links
        $otherNames = $request->input('other_names', []);
        $otherUrls = $request->input('other_urls', []);
        
        for ($i = 0; $i < count($otherNames); $i++) {
            if (!empty($otherNames[$i]) && !empty($otherUrls[$i])) {
                FileUpload::create([
                    'case_id' => $caseId,
                    'name' => $otherNames[$i],
                    'url' => $otherUrls[$i],
                    'wich_rubrique' => 'other_file',
                    'type' => 'link',
                    'status' => 'completed'
                ]);
                
                Log::info('Other file link saved', [
                    'case_id' => $caseId,
                    'name' => $otherNames[$i],
                    'url' => $otherUrls[$i]
                ]);
            }
        }
    }

}
