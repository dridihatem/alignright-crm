<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\PatientService;
use App\Repositories\PatientRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Concerns\GeneratesCaseIdentifiers;

class DoctorPatientController extends Controller
{
    use GeneratesCaseIdentifiers;

    protected $patientService;
    protected $patientRepository;

    public function __construct(
        PatientService $patientService,
        PatientRepository $patientRepository
    ) {
        $this->patientService = $patientService;
        $this->patientRepository = $patientRepository;
    }

    /**
     * Display the patients list
     */
    public function index()
    {
        try {
            return view('doctor.patients.index');
        } catch (Exception $e) {
            Log::error('Error displaying patients index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load patients page');
        }
    }

    /**
     * Get patients for DataTable
     */
    public function getPatients(Request $request): JsonResponse
    {
        try {
            $doctorId = auth()->user()->id;
            $filters = $request->all();
            $filters['doctor_id'] = $doctorId;

            $query = $this->patientRepository->getForDataTable($filters);

            return DataTables::of($query)
                ->addColumn('doctor_name', function ($patient) {
                    return $patient->doctor->name ?? 'N/A';
                })
                ->addColumn('cases_count', function ($patient) {
                    return $patient->cases->count();
                })
                ->addColumn('status_badge', function ($patient) {
                    return $this->getStatusBadge($patient->status);
                })
                ->addColumn('actions', function ($patient) {
                    return view('doctor.patients.partials.actions', compact('patient'))->render();
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting patients: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve patients'], 500);
        }
    }

    /**
     * Show the form for creating a new patient
     */
    public function create()
    {
        try {
            $generatedReference = $this->generatePatientReference();
            return view('doctor.patients.create', compact('generatedReference'));
        } catch (Exception $e) {
            Log::error('Error showing create patient form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load create patient form');
        }
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:patients,email',
                'phone' => 'required|string|max:20',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'address' => 'nullable|string',
                'medical_history' => 'nullable|string',
                'allergies' => 'nullable|string',
                'emergency_contact' => 'nullable|string',
                'emergency_phone' => 'nullable|string|max:20',
                'insurance_provider' => 'nullable|string|max:255',
                'insurance_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            $doctorId = auth()->user()->id;
            $patient = $this->patientService->createPatient($validated, $doctorId);

            return redirect()->route('doctor.patients.show', $patient->reference)
                           ->with('success', 'Patient created successfully');
        } catch (Exception $e) {
            Log::error('Error creating patient: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create patient: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified patient
     */
    public function show($reference)
    {
        try {
            $patient = $this->patientService->getPatientByReference($reference);
            
            // Check if the patient belongs to the authenticated doctor
            if ($patient->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to patient');
            }

            return view('doctor.patients.show', compact('patient'));
        } catch (Exception $e) {
            Log::error('Error showing patient: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Patient not found');
        }
    }

    /**
     * Show the form for editing the specified patient
     */
    public function edit($reference)
    {
        try {
            $patient = $this->patientService->getPatientByReference($reference);
            
            // Check if the patient belongs to the authenticated doctor
            if ($patient->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to patient');
            }

            return view('doctor.patients.edit', compact('patient'));
        } catch (Exception $e) {
            Log::error('Error showing edit patient form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Patient not found');
        }
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, $reference)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:patients,email,' . $reference . ',reference',
                'phone' => 'required|string|max:20',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'address' => 'nullable|string',
                'medical_history' => 'nullable|string',
                'allergies' => 'nullable|string',
                'emergency_contact' => 'nullable|string',
                'emergency_phone' => 'nullable|string|max:20',
                'insurance_provider' => 'nullable|string|max:255',
                'insurance_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);

            $patient = $this->patientService->updatePatient($reference, $validated);

            return redirect()->route('doctor.patients.show', $patient->reference)
                           ->with('success', 'Patient updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating patient: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update patient: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified patient
     */
    public function destroy($reference)
    {
        try {
            $this->patientService->deletePatient($reference);
            return redirect()->route('doctor.patients.index')
                           ->with('success', 'Patient deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting patient: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete patient: ' . $e->getMessage());
        }
    }

    /**
     * Show patient details
     */
    public function details($reference)
    {
        try {
            $patient = $this->patientService->getPatientByReference($reference);
            
            // Check if the patient belongs to the authenticated doctor
            if ($patient->doctor_id !== auth()->user()->id) {
                return redirect()->back()->with('error', 'Unauthorized access to patient');
            }

            return view('doctor.patients.details', compact('patient'));
        } catch (Exception $e) {
            Log::error('Error showing patient details: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Patient not found');
        }
    }

    /**
     * Search patients
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $doctorId = auth()->user()->id;

            $patients = $this->patientService->searchPatients($search, $doctorId);

            return response()->json($patients);
        } catch (Exception $e) {
            Log::error('Error searching patients: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search patients'], 500);
        }
    }

    /**
     * Get patient statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $doctorId = auth()->user()->id;
            $stats = $this->patientService->getPatientStats($doctorId);

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Error getting patient stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve patient statistics'], 500);
        }
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status): string
    {
        $badges = [
            'active' => '<span class="badge bg-label-success">Active</span>',
            'inactive' => '<span class="badge bg-label-secondary">Inactive</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }
}
