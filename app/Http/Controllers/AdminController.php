<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\CasePatient;
use App\Models\User;
use App\Models\Patient;
use App\Models\Tickets;
use App\Models\Calendar;
use App\Models\Comment;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\WeTransferNotification;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\CaseAssignedNotification;
use App\Mail\UserCredentials;


class AdminController extends Controller
{
    public function index()
    {
        $status_draft = CasePatient::where('status', 'draft')->count();
        $status_pending = CasePatient::where('status', 'pending')->count();
        $status_in_planning = CasePatient::where('status', 'in_planning')->count();
        $status_in_production = CasePatient::where('status', 'in_production')->count();
        $status_approval = CasePatient::where('status', 'approval')->count();
        $status_shipped = CasePatient::where('status', 'shipped')->count();
        $status_rejected = CasePatient::where('status', 'rejected')->count();
        $new_cases = CasePatient::count();
        $total_cases = CasePatient::count();
        $total_doctors = User::where('role_id', 2)->count();
        $total_technicians = User::where('role_id', 3)->count();
        $total_laboratories = User::where('role_id', 4)->count();
        $count_patient = Patient::count();
        $count_cases = CasePatient::count();
        // Calculate case retarded percentage
        $case_retarded = CasePatient::where('status', 'pending')->count();
        if($count_cases > 0){
            $case_retarded_percentage = number_format(($case_retarded / $count_cases) * 100, 2);
        } else {
            $case_retarded_percentage = 0;
        }

        // Get monthly totals for the last 12 months
        $cases_by_month = CasePatient::where('created_at', '>=', now()->subDays(365))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Create array with all months, defaulting to 0
        $monthly_totals = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthly_totals[] = $cases_by_month[$i] ?? 0;
        }
        
        $patientGroups = $this->buildPatientGroups();

        return view('admin.dashboard', compact('status_draft', 'status_pending', 'status_in_planning', 'status_in_production', 'status_approval', 'status_shipped', 'status_rejected', 'new_cases', 'total_cases',
        'total_doctors', 'total_technicians', 'total_laboratories', 'count_patient', 'count_cases', 'case_retarded_percentage', 'monthly_totals', 'patientGroups'));
    }

 
        
    public function getcases(Request $request)
    {
        if($request->ajax()){
            $query = CasePatient::with(['patient']);

            // Apply filters
            if ($request->filled('case_id')) {
                $query->where('case_id', 'like', '%' . $request->case_id . '%');
            }

            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            if ($request->filled('treatment_type')) {
                $query->where('treatment_type', $request->treatment_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('case_id', 'like', '%' . $searchValue . '%')
                      ->orWhere('treatment_type', 'like', '%' . $searchValue . '%')
                      ->orWhereHas('patient', function($patientQuery) use ($searchValue) {
                          $patientQuery->where('name', 'like', '%' . $searchValue . '%');
                      });
                });
            }

            // Column-specific search
            if ($request->filled('columns.0.search.value')) {
                $query->where('case_id', 'like', '%' . $request->input('columns.0.search.value') . '%');
            }
            if ($request->filled('columns.1.search.value')) {
                $query->where('status', 'like', '%' . $request->input('columns.1.search.value') . '%');
            }
            if ($request->filled('columns.2.search.value')) {
                $query->where('treatment_type', 'like', '%' . $request->input('columns.2.search.value') . '%');
            }
            if ($request->filled('columns.4.search.value')) {
                $query->whereHas('patient', function($patientQuery) use ($request) {
                    $patientQuery->where('name', 'like', '%' . $request->input('columns.4.search.value') . '%');
                });
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function($row){
                    return '<input type="checkbox" class="form-check-input case-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('case_id', function($row){
                    return $row->case_id;
                })
                ->addColumn('status', function($row){
                    $status = $row->status;
                    if($status == 'pending'){
                        return '<span class="badge bg-label-warning">'.__('master.pending').'</span>';
                    }elseif($status == 'draft'){
                        return '<span class="badge bg-label-secondary">'.__('master.draft').'</span>';
                    }elseif($status == 'in_planning'){
                        return '<span class="badge bg-label-info">'.__('master.in_planning').'</span>';
                    }elseif($status == 'approval'){
                        return '<span class="badge bg-label-success">'.__('master.approval').'</span>';
                    }elseif($status == 'in_production'){
                        return '<span class="badge bg-label-success">'.__('master.in_production').'</span>';
                    }elseif($status == 'shipped'){
                        return '<span class="badge bg-label-success">'.__('master.shipped').'</span>';
                    }elseif($status == 'rejected'){
                        return '<span class="badge bg-label-danger">'.__('master.rejected').'</span>';
                    }
                })
                ->addColumn('treatment_type', function($row){
                    return $row->treatment_type;
                })
                ->addColumn('date', function($row){
                    return $row->created_at->format('d/m/Y');
                })
                ->addColumn('patient_id', function($row){
                    if($row->patient){
                        return $row->patient->name;
                    }
                    return __('master.no_patient');
                }) 

               
                ->addColumn('accepted_date', function($row){
                    return $row->accepted_date;
                })
                ->addColumn('rejected_date', function($row){
                    return $row->rejected_date;
                })
                ->addColumn('price_status', function($row){
                    if ($row->price_accepted_at) {
                        return '<span class="badge bg-label-success"><i class="fas fa-check me-1"></i>Price Accepted</span>';
                    } elseif ($row->price_rejected_at) {
                        return '<span class="badge bg-label-danger"><i class="fas fa-times me-1"></i>Price Rejected</span>';
                    } elseif ($row->price) {
                        return '<span class="badge bg-label-warning"><i class="fas fa-clock me-1"></i>Price Pending</span>';
                    } else {
                        return '<span class="badge bg-label-secondary">No Price</span>';
                    }
                })
                ->addColumn('action', function($row){
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('admin.cases.show', $row->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.cases.edit', $row->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('admin.cases.delete', $row->id).'">'.__('master.delete').'</a></li>
                         
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['checkbox', 'action', 'status', 'price_status'])
                ->make(true);
        }
    
    }

    /**
     * Mass delete multiple cases
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cases_mass_delete(Request $request)
    {
        try {
            $request->validate([
                'case_ids' => 'required|array|min:1',
                'case_ids.*' => 'required|integer|exists:case_patients,id'
            ]);

            $caseIds = $request->input('case_ids');
            $deletedCount = 0;
            $failedCases = [];
            
            // Debug logging
            Log::info('Starting mass case deletion', [
                'admin_id' => auth()->user()->id,
                'case_ids' => $caseIds,
                'total_requested' => count($caseIds)
            ]);

            // Process each case independently to handle partial failures gracefully
            foreach ($caseIds as $caseId) {
                try {
                    // Use individual transaction for each case
                    \DB::beginTransaction();
                    
                    $case = CasePatient::findOrFail($caseId);
                    
                    // Store case information for logging
                    $caseIdStr = $case->case_id;
                    $patientId = $case->patient_id;
                    $doctorId = $case->doctor_id;
                    
                    // Delete all related records first (in proper order to avoid foreign key constraints)
                    
                    // 1. Delete tooth problem cases
                    \App\Models\ToothProblemCase::where('case_id', $caseId)->delete();
                    
                    // 2. Delete treatment types
                    \App\Models\TreatmentType::where('case_id', $caseId)->delete();
                    
                    // 3. Delete invoices (this will also handle payments through foreign key constraints)
                    \App\Models\Invoice::where('case_id', $caseId)->delete();
                    
                    // 4. Delete comments
                    \App\Models\Comment::where('case_id', $caseId)->delete();
                    
                    // 5. Delete file uploads
                    \App\Models\FileUpload::where('case_id', $caseId)->delete();
                    
                    // 6. Delete notifications
                    \App\Models\Notification::where('case_id', $caseId)->delete();
                    
                    // 7. Delete WeTransfer notifications
                    \App\Models\WeTransferNotification::where('case_id', $caseId)->delete();

                    
                  
                    
                    // Finally, delete the case itself
                    $case->delete();

                  

                    // 9. Delete invoices
                    \App\Models\Invoice::where('case_id', $caseId)->delete();

                     // 8. Delete payments
                     if(\App\Models\Invoice::where('case_id', $caseId)->exists()){
                        $invoice_id = \App\Models\Invoice::where('case_id', $caseId)->first()->id;
                        if($invoice_id){
                            \App\Models\Payment::where('invoice_id', $invoice_id)->delete();
                        }
                     }
                     // 13. Delete patients
                     \App\Models\Patient::where('id', $patientId)->delete();

                    

                    
                    // 10. Delete comments
                    \App\Models\Comment::where('case_id', $caseId)->delete();

                    // 11. Delete notifications
                    \App\Models\Notification::where('case_id', $caseId)->delete();

                    // 12. Delete WeTransfer notifications
                    \App\Models\WeTransferNotification::where('case_id', $caseId)->delete();

                    
                    
                    
                    // Commit this individual case deletion
                    \DB::commit();
                    
                    $deletedCount++;
                    
                    // Log the successful deletion for audit purposes
                    Log::info('Case deleted by admin during mass deletion', [
                        'admin_id' => auth()->user()->id,
                        'case_id' => $caseIdStr,
                        'patient_id' => $patientId,
                        'doctor_id' => $doctorId,
                        'deleted_at' => now()
                    ]);
                    
                } catch (\Exception $e) {
                    // Rollback this individual case deletion
                    \DB::rollBack();
                    
                    $failedCases[] = [
                        'case_id' => $caseId,
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Error deleting case during mass deletion', [
                        'case_id' => $caseId,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Log the mass deletion operation
            Log::info('Mass case deletion completed by admin', [
                'admin_id' => auth()->user()->id,
                'total_requested' => count($caseIds),
                'successfully_deleted' => $deletedCount,
                'failed_count' => count($failedCases),
                'failed_cases' => $failedCases
            ]);
            
            if ($deletedCount === count($caseIds)) {
                // All cases deleted successfully
                return response()->json([
                    'success' => true,
                    'message' => __('master.mass_delete_success', ['count' => $deletedCount]),
                    'deleted_count' => $deletedCount
                ]);
            } else {
                // Some cases failed to delete
                return response()->json([
                    'success' => false,
                    'message' => __('master.mass_delete_partial_success', [
                        'deleted' => $deletedCount,
                        'total' => count($caseIds),
                        'failed' => count($failedCases)
                    ]),
                    'deleted_count' => $deletedCount,
                    'failed_count' => count($failedCases),
                    'failed_cases' => $failedCases
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error during mass case deletion', [
                'admin_id' => auth()->user()->id,
                'case_ids' => $request->input('case_ids'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => __('master.mass_delete_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }


    public function doctors()
    {
        return view('admin.doctor.index');
    }
    public function getdoctors(Request $request)
    {
        if($request->ajax()){
            $query = User::where('role_id', 2)->with('cases');
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('doctor_name', function($row){
                    return $row->name;
                })
                ->addColumn('doctor_email', function($row){
                    return $row->email;
                })
                ->addColumn('doctor_count_cases', function($row){
                    return $row->cases->count();
                })
                ->addColumn('doctor_photo', function($row){
                    return '<img src="'.$row->photo_url.'" alt="'.__('master.doctor_photo').'" class="rounded-circle" style="width: 30px; height: 30px;">';
                })
                ->addColumn('doctor_status', function($row){
                    if($row->status == 'active'){
                        return '<span class="badge bg-label-success">'.__('master.active').'</span>';
                    }else{
                        return '<span class="badge bg-label-danger">'.__('master.inactive').'</span>';
                    }
                })
               
               

               
               
                ->addColumn('action', function($row){
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('admin.doctors.show', $row->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.doctors.edit', $row->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('admin.doctors.delete', $row->id).'">'.__('master.delete').'</a></li>
                         
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['action', 'doctor_status', 'doctor_photo'])
                ->make(true);
        }
    }


    public function doctors_show($id)
    {
        $doctor = User::findOrFail($id);
        $cases = CasePatient::where('doctor_id', $id)->get();
        return view('admin.doctor.show', compact('doctor', 'cases'));
    }

    public function doctors_cases($id, Request $request)
    {
        if($request->ajax()){
            $query = CasePatient::where('doctor_id', $id)
                ->with(['patient', 'technician', 'laboratory'])
                ->latest();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('case_id', function($row){
                    return '<strong>' . $row->case_id . '</strong>';
                })
                ->addColumn('patient_name', function($row){
                    if($row->patient){
                        return $row->patient->name;
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('treatment_type', function($row){
                    return $row->treatment_type ?? '<span class="text-muted">N/A</span>';
                })
                ->addColumn('status', function($row){
                    $status = $row->status;
                    if($status == 'pending'){
                        return '<span class="badge bg-label-warning">'.__('master.pending').'</span>';
                    }elseif($status == 'draft'){
                        return '<span class="badge bg-label-secondary">'.__('master.draft').'</span>';
                    }elseif($status == 'in_planning'){
                        return '<span class="badge bg-label-info">'.__('master.in_planning').'</span>';
                    }elseif($status == 'approval'){
                        return '<span class="badge bg-label-success">'.__('master.approval').'</span>';
                    }elseif($status == 'in_production'){
                        return '<span class="badge bg-label-success">'.__('master.in_production').'</span>';
                    }elseif($status == 'shipped'){
                        return '<span class="badge bg-label-success">'.__('master.shipped').'</span>';
                    }elseif($status == 'rejected'){
                        return '<span class="badge bg-label-danger">'.__('master.rejected').'</span>';
                    }
                    return '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
                })
                ->addColumn('price', function($row){
                    if($row->price){
                        return '<strong>Tnd ' . number_format($row->price, 2) . '</strong>';
                    }
                    return '<span class="text-muted">'.__('master.not_set').'</span>';
                })
                ->addColumn('created_date', function($row){
                    return $row->created_at->format('d/m/Y');
                })
                ->addColumn('updated_date', function($row){
                    return $row->updated_at->format('d/m/Y');
                })
                ->addColumn('actions', function($row){
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('admin.cases.show', $row->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.cases.edit', $row->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('admin.cases.delete', $row->id).'" onclick="return confirm(\'Are you sure?\')">'.__('master.delete').'</a></li>
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['case_id', 'patient_name', 'treatment_type', 'status', 'price', 'actions', 'created_date', 'updated_date'])
                ->make(true);
        }
    }

    public function doctors_cases_export($id)
    {
        $doctor = User::findOrFail($id);
        $cases = CasePatient::where('doctor_id', $id)
            ->with(['patient'])
            ->latest()
            ->get();

        $filename = 'doctor_' . $doctor->id . '_cases_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($cases, $doctor) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Doctor: ' . $doctor->name,
                'Email: ' . $doctor->email,
                'Total Cases: ' . $cases->count(),
                '',
                'Case ID',
                'Patient Name',
                'Treatment Type',
                'Status',
                'Price',
                'Created Date',
                'Updated Date'
            ]);

            // CSV data
            foreach ($cases as $case) {
                fputcsv($file, [
                    '',
                    '',
                    '',
                    '',
                    $case->case_id,
                    $case->patient ? $case->patient->name : 'N/A',
                    $case->treatment_type ?? 'N/A',
                    $case->status,
                    $case->price ? 'Tnd ' . number_format($case->price, 2) : 'Not set',
                    $case->created_at->format('M d, Y H:i'),
                    $case->updated_at->format('M d, Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function doctors_edit($id)
    {
        $doctor = User::findOrFail($id);
        $cases = CasePatient::where('doctor_id', $id)->get();
        return view('admin.doctor.edit', compact('doctor', 'cases'));
    }

    public function doctors_delete($id)
    {
        $doctor = User::findOrFail($id);
        $doctor->delete();
        return redirect()->route('admin.doctors.list')->with('success', 'Doctor deleted successfully');
    }

    /**
     * Show the form for creating a new doctor
     */
    public function doctors_create()
    {
        return view('admin.doctor.create');
    }

    /**
     * Store a newly created doctor
     */
    public function doctors_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'profile-' . time() . '.' . $photo->getClientOriginalExtension();
                // Store on the public disk (-> public/storage/profile-photos) and keep a full link.
                $storedPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $photoPath = asset('storage/' . $storedPath);
            }

            // Create the doctor
            $doctor = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
                'photo' => $photoPath,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'bio' => $request->bio,
            ]);

            // Send credentials email if requested
            if ($request->has('send_credentials') && $request->filled('password')) {
                try {
                    // Get email settings from cache
                    $emailSettings = Cache::get('email_settings', []);
                    
                    // Configure mail settings if available
                    if (!empty($emailSettings)) {
                        Config::set('mail.mailers.smtp.host', $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $emailSettings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $emailSettings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $emailSettings['mail_from_name'] ?? config('mail.from.name'));
                    }
                    
                    // Send credentials email
                    Mail::to($doctor->email)->send(new UserCredentials($doctor, $request->password, 'doctor'));
                    
                    Log::info('Doctor credentials email sent', [
                        'doctor_id' => $doctor->id,
                        'doctor_email' => $doctor->email,
                        'admin_id' => auth()->user()->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send doctor credentials email', [
                        'doctor_id' => $doctor->id,
                        'doctor_email' => $doctor->email,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Doctor created by admin', [
                'doctor_id' => $doctor->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.doctors.list')
                ->with('success', 'Doctor created successfully');

        } catch (\Exception $e) {
            Log::error('Error creating doctor', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create doctor: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing doctor
     */
    public function doctors_update(Request $request, $id)
    {
        $doctor = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle photo upload
            $photoPath = $doctor->photo; // Keep existing photo by default
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($doctor->photo && Storage::disk('public')->exists($doctor->photo)) {
                    Storage::disk('public')->delete($doctor->photo);
                }
                
                $photo = $request->file('photo');
                $photoName = 'profile-' . time() . '.' . $photo->getClientOriginalExtension();
                // Store on the public disk (-> public/storage/profile-photos) and keep a full link.
                $storedPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $photoPath = asset('storage/' . $storedPath);
            }

            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'role_id' => $request->role_id,
                'photo' => $photoPath,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'bio' => $request->bio,
            ];

            // Update password only if provided
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            // Update the doctor
            $doctor->update($updateData);

            // Send credentials email if requested and password was changed
            if ($request->has('send_credentials') && $request->filled('password')) {
                // TODO: Implement email sending functionality
                try {
                    // Get email settings from cache
                    $emailSettings = Cache::get('email_settings', []);
                    
                    // Configure mail settings if available
                    if (!empty($emailSettings)) {
                        Config::set('mail.mailers.smtp.host', $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $emailSettings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $emailSettings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $emailSettings['mail_from_name'] ?? config('mail.from.name'));
                    }
                    
                    Mail::to($doctor->email)->send(new UserCredentials($doctor, $request->password, 'doctor'));
                    
                    Log::info('Doctor credentials email sent', [
                        'doctor_id' => $doctor->id,
                        'doctor_email' => $doctor->email,
                        'admin_id' => auth()->user()->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send doctor credentials email', [
                        'doctor_id' => $doctor->id,
                        'doctor_email' => $doctor->email,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage()
                    ]);
                }
                // Mail::to($doctor->email)->send(new DoctorCredentialsUpdated($doctor, $request->password));
            }

            Log::info('Doctor updated by admin', [
                'doctor_id' => $doctor->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.doctors.show', $doctor->id)
                ->with('success', 'Doctor updated successfully');

        } catch (\Exception $e) {
            Log::error('Error updating doctor', [
                'doctor_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update doctor: ' . $e->getMessage());
        }
    }

    public function cases()
    {
        $patientGroups = $this->buildPatientGroups();

        return view('admin.cases.list', compact('patientGroups'));
    }

    public function cases_table()
    {
        return $this->cases();
    }

    /**
     * Build cases grouped by patient, including each patient's case list.
     * Used by the cases list page and the admin dashboard.
     */
    private function buildPatientGroups()
    {
        $cases = CasePatient::with('patient')->orderBy('created_at', 'desc')->get();

        return $cases->groupBy('patient_id')->map(function ($group) {
            $latest = $group->first(); // ordered by created_at desc, so first = latest

            return (object) [
                'patient_name'      => $latest->patient
                                        ? trim($latest->patient->name . ' ' . ($latest->patient->surname ?? ''))
                                        : __('master.no_patient'),
                'count'             => $group->count(),
                'latest_status'     => $latest->status,
                'latest_case_id'    => $latest->case_id,
                'latest_case_db_id' => $latest->id,
                'latest_date'       => $latest->created_at,
                'cases'             => $group->map(function ($c) {
                    return [
                        'case_id'        => $c->case_id,
                        'status'         => $c->status,
                        'status_html'    => $this->statusBadge($c->status),
                        'treatment_type' => $c->treatment_type ?: '-',
                        'date'           => $c->created_at ? $c->created_at->format('d/m/Y') : '-',
                        'url'            => route('admin.cases.show', $c->id),
                        'edit_url'       => route('admin.cases.edit', $c->id),
                        'delete_url'     => route('admin.cases.delete', $c->id),
                    ];
                })->values(),
            ];
        })->sortByDesc('count')->values();
    }

    /**
     * Build a status badge HTML snippet.
     */
    private function statusBadge($status): string
    {
        $map = [
            'draft'         => ['bg-label-secondary', __('master.draft')],
            'pending'       => ['bg-label-warning',   __('master.pending')],
            'in_planning'   => ['bg-label-info',      __('master.in_planning')],
            'approval'      => ['bg-label-success',    __('master.approval')],
            'in_production' => ['bg-label-primary',    __('master.in_production')],
            'shipped'       => ['bg-label-success',    __('master.shipped')],
            'rejected'      => ['bg-label-danger',     __('master.rejected')],
        ];

        [$class, $label] = $map[$status] ?? ['bg-label-secondary', ucfirst($status)];

        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
    public function cases_show($id)
    {
        $case = CasePatient::with(['patient', 'doctor', 'technician', 'laboratory', 'treatmentType', 'invoices'])->findOrFail($id);
        
        // Get tooth problems for this case
        $toothProblemscase = \App\Models\ToothProblemCase::where('case_id', $id)->with('tooth_problem')->get();

        // Comments for this case
        $comments = \App\Models\Comment::where('case_id', $id)->with('user.role')->latest()->get();
        
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
        
        return view('admin.cases.show', compact('case', 'toothProblemscase', 'comments', 'files_clinical', 'files_radiographs', 'other_files', 'stl_files', 'count_stl_files', 'count_clinical_files', 'count_radiograph_files', 'count_other_files'));
    }

    /**
     * Admin adds a comment to a case.
     */
    public function addCaseComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $case = CasePatient::findOrFail($id);

        \App\Models\Comment::create([
            'case_id' => $case->id,
            'comment' => $request->comment,
            'user_id' => auth()->id(),
            'type'    => 'admin_update',
        ]);

        return redirect()->back()->with('success', __('master.comment_added_successfully'));
    }
    public function cases_edit($id)
    {
        $case = CasePatient::with(['patient', 'doctor', 'technician', 'laboratory'])->findOrFail($id);
        return view('admin.cases.edit', compact('case'));
    }
    /**
     * Delete a case and all its related data
     * This method ensures complete cleanup of all related records to maintain data integrity
     * 
     * @param int $id The case ID to delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cases_delete($id)
    {
        try {
            $case = CasePatient::findOrFail($id);
            
            // Store case information for logging
            $caseId = $case->case_id;
            $patientId = $case->patient_id;
            $doctorId = $case->doctor_id;
            
            // Use database transaction to ensure data consistency
            \DB::beginTransaction();
            
            try {
                // Delete all related records first (in proper order to avoid foreign key constraints)
                // This ensures complete cleanup and prevents orphaned data
                
                // 1. Delete tooth problem cases (tooth-specific issues for this case)
                \App\Models\ToothProblemCase::where('case_id', $id)->delete();
                
                // 2. Delete treatment types (treatment plans and specifications)
                \App\Models\TreatmentType::where('case_id', $id)->delete();
                
                // 3. Delete invoices (billing information - payments will be handled by foreign key constraints)
                \App\Models\Invoice::where('case_id', $id)->delete();
                
                // 4. Delete comments (case-related discussions and notes)
                \App\Models\Comment::where('case_id', $id)->delete();
                
                // 5. Delete file uploads (clinical photos, scans, documents)
                \App\Models\FileUpload::where('case_id', $id)->delete();
                
                // 6. Delete notifications (case-related alerts and messages)
                \App\Models\Notification::where('case_id', $id)->delete();
                
                // 7. Delete WeTransfer notifications (file transfer records)
                \App\Models\WeTransferNotification::where('case_id', $id)->delete();
                
           
                
                
                // Finally, delete the case itself
                $case->delete();
                
                // Commit the transaction
                \DB::commit();
                
                // Log the deletion for audit purposes
                Log::info('Case deleted by admin with all related data', [
                    'admin_id' => auth()->user()->id,
                    'case_id' => $caseId,
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'deleted_at' => now()
                ]);
                
                return redirect()->route('admin.cases.list')->with('success', __('master.case_deleted'));
                
            } catch (\Exception $e) {
                // Rollback the transaction if any error occurs
                \DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error deleting case and related data', [
                'case_id' => $id,
                'admin_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('admin.cases.list')
                ->with('error', 'Failed to delete case: ' . $e->getMessage());
        }
    }
    public function cases_create()
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = User::where('role_id', 2)->where('status', 'active')->orderBy('name')->get();
        $toothProblems = \App\Models\ToothProblem::all();
        $caseId = $this->generateCaseId();
        $patientReference = $this->generatePatientReference();

        return view('admin.cases.create', compact('patients', 'doctors', 'toothProblems', 'caseId', 'patientReference'));
    }

    /**
     * Generate a patient reference, e.g. PT-1234
     * Format: 'PT-' + random 4-digit number (unique).
     */
    private function generatePatientReference(): string
    {
        do {
            $candidate = 'PT-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('reference', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generate a case identifier, e.g. AR-1234
     * Format: 'AR-' + random 4-digit number (unique).
     */
    private function generateCaseId(): string
    {
        do {
            $candidate = 'AR-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (CasePatient::where('case_id', $candidate)->exists());

        return $candidate;
    }

    public function cases_store(Request $request)
    {
        $rules = [
            'patient_type'           => 'required|in:new,existing',
            'doctor_id'              => 'required|exists:users,id',
            'treatment_type'         => 'nullable|string|max:255',
            'treatment_overjet'      => 'nullable|string|max:255',
            'treatment_overbite'     => 'nullable|string|max:255',
            'treatment_midline'      => 'nullable|string|max:255',
            'treatment_irp'          => 'nullable|string|max:255',
            'treatment_attachments'  => 'nullable|string|max:255',
            'doctor_instruction'     => 'nullable|string',
            'patient_chief_complaint'=> 'nullable|string',
            'type_of_scan'           => 'nullable|in:intraoral,desktop,silicone',
            'tooth_problems'         => 'nullable|array',
        ];

        if ($request->input('patient_type') === 'existing') {
            $rules['patient_id'] = 'required|exists:patients,id';
        } else {
            $rules['name']      = 'required|string|max:255';
            $rules['surname']   = 'required|string|max:255';
            $rules['gender']    = 'required|in:male,female,other';
            $rules['reference'] = 'required|string|max:255|unique:patients,reference';
            $rules['state']     = 'nullable|string|max:255';
            $rules['country']   = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        \DB::beginTransaction();
        try {
            if ($validated['patient_type'] === 'new') {
                $patient = Patient::create([
                    'reference' => $validated['reference'],
                    'name'      => $validated['name'],
                    'surname'   => $validated['surname'],
                    'gender'    => $validated['gender'],
                    'state'     => $validated['state'] ?? null,
                    'country'   => $validated['country'] ?? null,
                ]);
                $patientId = $patient->id;
            } else {
                $patientId = $validated['patient_id'];
            }

            $case = CasePatient::create([
                'case_id'                 => $this->generateCaseId(),
                'patient_id'              => $patientId,
                'doctor_id'               => $validated['doctor_id'],
                'technician_id'           => null,
                'laboratory_id'           => null,
                'status'                  => 'pending',
                'doctor_instruction'      => $request->input('doctor_instruction'),
                'treatment_type'          => $request->input('treatment_type'),
                'treatment_overjet'       => $request->input('treatment_overjet'),
                'treatment_overbite'      => $request->input('treatment_overbite'),
                'treatment_midline'       => $request->input('treatment_midline'),
                'treatment_irp'           => $request->input('treatment_irp'),
                'treatment_attachments'   => $request->input('treatment_attachments'),
                'patient_chief_complaint' => $request->input('patient_chief_complaint'),
                'type_of_scan'            => $request->input('type_of_scan'),
            ]);

            $toothProblems = $request->input('tooth_problems', []);
            if (is_array($toothProblems)) {
                foreach ($toothProblems as $toothNumber => $data) {
                    $problemId = $data['problem_id'] ?? null;
                    if ($problemId) {
                        \App\Models\ToothProblemCase::create([
                            'case_id'          => $case->id,
                            'tooth_number'     => $toothNumber,
                            'tooth_problem_id' => $problemId,
                            'tooth_notes'      => $request->input("tooth_notes.$toothNumber.notes"),
                        ]);
                    }
                }
            }

            \DB::commit();

            return redirect()->route('admin.cases.show', $case->id)
                ->with('success', __('master.case_created'));
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Admin case creation failed: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', __('master.case_creation_failed'));
        }
    }
    public function cases_update($id, Request $request)
    {
        $case = CasePatient::findOrFail($id);
        
        // Validate the request
        $request->validate([
            'case_id' => 'required|string|max:255',
            'status' => 'required|string|in:draft,pending,in_planning,approval,in_production,shipped,rejected',
            'treatment_type' => 'nullable|string|max:255',
            'treatment_treat' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'price' => 'nullable|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'accepted_date' => 'nullable|date',
            'rejected_date' => 'nullable|date',
            'treatment_plan_link' => 'nullable|url',
            'wetransfer_link' => 'nullable|url',
            'patient_id' => 'nullable|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'laboratory_id' => 'nullable|exists:users,id',
        ]);

        // Update the case
        $case->update($request->all());
        
        return redirect()->route('admin.cases.show', $case->id)
            ->with('success', 'Case updated successfully');
    }
    public function cases_change_status($id, $status)
    {
        $case = CasePatient::find($id);
        $case->status = $status;
        $case->save();
        return redirect()->route('admin.cases.list')->with('success', __('master.case_status_changed'));
    }
    public function cases_change_priority($id, $priority)
    {
        $case = CasePatient::find($id);
        $case->priority = $priority;
        $case->save();
        return redirect()->route('admin.cases.list')->with('success', __('master.case_priority_changed'));
    }

    public function assign_technician(Request $request, $id)
    {
        try {
            $request->validate([
                'technician_id' => 'required|exists:users,id',
                'technician_comment' => 'nullable|string|max:2000',
                'send_notification' => 'boolean'
            ]);

            $case = CasePatient::findOrFail($id);
            $oldTechnicianId = $case->technician_id;
            
            $case->technician_id = $request->technician_id;
            $case->technician_comment = $request->technician_comment;
            $case->save();
            $case->status = 'in_planning';
            $case->save();

            // Send notification if requested
            if ($request->has('send_notification') && $request->send_notification) {
                $technician = User::find($request->technician_id);
                if ($technician) {
                    try {
                        // Configure mail settings from database
                        $settings = \App\Models\Setting::all()->pluck('value', 'name')->toArray();
            
            // Configure mail settings dynamically
            Config::set('mail.mailers.smtp.host', $settings['mail_host'] ?? config('mail.mailers.smtp.host'));
            Config::set('mail.mailers.smtp.port', $settings['mail_port'] ?? config('mail.mailers.smtp.port'));
            Config::set('mail.mailers.smtp.username', $settings['mail_username'] ?? config('mail.mailers.smtp.username'));
            Config::set('mail.mailers.smtp.password', $settings['mail_password'] ?? config('mail.mailers.smtp.password'));
            Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
            Config::set('mail.from.address', $settings['mail_from_address'] ?? config('mail.from.address'));
            Config::set('mail.from.name', $settings['mail_from_name'] ?? config('mail.from.name'));

                        
                        // Send case assignment notification
                        Mail::to($technician->email)->send(new CaseAssignedNotification($case, $technician, 'technician'));
                        
                        Log::info('Case assignment notification sent to technician', [
                            'technician_id' => $technician->id,
                            'technician_email' => $technician->email,
                            'case_id' => $case->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send case assignment notification to technician', [
                            'technician_id' => $technician->id,
                            'technician_email' => $technician->email,
                            'case_id' => $case->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info('Technician assigned to case by admin', [
                'case_id' => $case->id,
                'technician_id' => $request->technician_id,
                'admin_id' => auth()->user()->id,
                'old_technician_id' => $oldTechnicianId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Technician assigned successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning technician to case', [
                'case_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign technician: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assign_laboratory(Request $request, $id)
    {
        try {
            $request->validate([
                'laboratory_id' => 'required|exists:users,id',
                'laboratory_comment' => 'nullable|string|max:2000',
                'send_notification' => 'boolean'
            ]);

            $case = CasePatient::findOrFail($id);
            $oldLaboratoryId = $case->laboratory_id;
            
            $case->laboratory_id = $request->laboratory_id;
            $case->laboratory_comment = $request->laboratory_comment;
            $case->save();

            // Send notification if requested
            if ($request->has('send_notification') && $request->send_notification) {
                $laboratory = User::find($request->laboratory_id);
                if ($laboratory) {
                    try {
                        // Configure mail settings from database
                        $settings = \App\Models\Setting::all()->pluck('value', 'name')->toArray();
            
                        // Configure mail settings dynamically
                        Config::set('mail.mailers.smtp.host', $settings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $settings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $settings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $settings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $settings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $settings['mail_from_name'] ?? config('mail.from.name'));
            
                        
                        // Send case assignment notification
                        Mail::to($laboratory->email)->send(new CaseAssignedNotification($case, $laboratory, 'laboratory'));
                        
                        Log::info('Case assignment notification sent to laboratory', [
                            'laboratory_id' => $laboratory->id,
                            'laboratory_email' => $laboratory->email,
                            'case_id' => $case->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send case assignment notification to laboratory', [
                            'laboratory_id' => $laboratory->id,
                            'laboratory_email' => $laboratory->email,
                            'case_id' => $case->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info('Laboratory assigned to case by admin', [
                'case_id' => $case->id,
                'laboratory_id' => $request->laboratory_id,
                'admin_id' => auth()->user()->id,
                'old_laboratory_id' => $oldLaboratoryId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laboratory assigned successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning laboratory to case', [
                'case_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign laboratory: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function settings()
    {
        // Get settings from database
        $settings = \App\Models\Setting::all()->pluck('value', 'name')->toArray();
        
        // Add default values for missing settings
        $defaults = [
            'site_name' => 'Dental Clinic Management',
            'site_description' => 'Professional dental clinic management system',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'currency' => 'TND',
            'language' => 'en',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Dental Clinic',
            'google_client_id' => '',
            'google_client_secret' => '',
            'google_redirect_uri' => route('google.callback'),
            'google_folder_id' => '',
            'google_drive_enabled' => '0',
            'max_file_size' => '10',
            'session_timeout' => '120',
            'pagination_limit' => '10',
            'maintenance_mode' => '0',
            'debug_mode' => '0',
            'site_logo' => null,
            'favicon' => null,
            'primary_color' => '#696cff',
        ];
        
        $settings = array_merge($defaults, $settings);

        $identifierStats = [
            'cases' => CasePatient::count(),
            'patients' => Patient::count(),
        ];

        return view('admin.settings', compact('settings', 'identifierStats'));
    }

    /**
     * Regenerate case IDs (AR-####) and/or patient references (PT-####) for
     * all existing records. Triggered from the admin Settings page.
     */
    public function regenerateIdentifiers(Request $request, \App\Services\CaseIdentifierRegenerator $regenerator)
    {
        $request->validate([
            'target' => 'required|in:cases,patients,both',
        ]);

        try {
            $target = $request->input('target');
            $doCases = in_array($target, ['cases', 'both'], true);
            $doPatients = in_array($target, ['patients', 'both'], true);

            $result = $regenerator->regenerate($doCases, $doPatients, false);

            return response()->json([
                'success' => true,
                'message' => __('master.identifiers_regenerated_successfully'),
                'cases' => $result['cases'],
                'patients' => $result['patients'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error regenerating identifiers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('master.error_regenerating_identifiers'),
            ], 500);
        }
    }

    /**
     * Global header autocomplete: search cases by case ID or patient name.
     */
    public function globalSearch(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $cases = CasePatient::with(['patient', 'doctor'])
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
                'url' => route('admin.cases.show', $case->id),
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Update general settings
     */
    public function updateGeneralSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'currency' => 'required|string|max:10',
            'language' => 'required|string|max:10',
        ]);

        try {
            // Update settings in database
            \App\Models\Setting::setValue('site_name', $request->site_name);
            \App\Models\Setting::setValue('site_description', $request->site_description);
            \App\Models\Setting::setValue('timezone', $request->timezone);
            \App\Models\Setting::setValue('date_format', $request->date_format);
            \App\Models\Setting::setValue('currency', $request->currency);
            
            // Handle language change
            $oldLanguage = \App\Models\Setting::getValue('language', 'en');
            \App\Models\Setting::setValue('language', $request->language);
            
            // If language changed, update app locale immediately and clear any cached translations
            if ($oldLanguage !== $request->language) {
                app()->setLocale($request->language);
                session()->put('locale', $request->language);
                
                // Clear translation cache if exists
                if (function_exists('artisan')) {
                    try {
                        \Artisan::call('cache:clear');
                        \Artisan::call('config:clear');
                    } catch (\Exception $e) {
                        // Silently handle artisan command failures
                    }
                }
            }

            Log::info('General settings updated', [
                'admin_id' => auth()->user()->id,
                'settings' => $request->only(['site_name', 'timezone', 'currency', 'language'])
            ]);

            return redirect()->route('admin.settings')
                ->with('success', __('master.general_settings_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to update general settings', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.settings')
                ->with('error', __('master.failed_to_update_general_settings'));
        }
    }

    /**
     * Update email settings
     */
    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|email',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        try {
            // Update settings in database
            \App\Models\Setting::setValue('mail_host', $request->mail_host);
            \App\Models\Setting::setValue('mail_port', $request->mail_port);
            \App\Models\Setting::setValue('mail_username', $request->mail_username);
            if ($request->filled('mail_password')) {
                \App\Models\Setting::setValue('mail_password', $request->mail_password);
            }
            \App\Models\Setting::setValue('mail_encryption', $request->mail_encryption);
            \App\Models\Setting::setValue('mail_from_address', $request->mail_from_address);
            \App\Models\Setting::setValue('mail_from_name', $request->mail_from_name);

            Log::info('Email settings updated', [
                'admin_id' => auth()->user()->id,
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port
            ]);

            return redirect()->route('admin.settings')
                ->with('success', __('master.email_settings_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to update email settings', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.settings')
                ->with('error', __('master.failed_to_update_email_settings'));
        }
    }

    /**
     * Update Google Drive settings
     */
    public function updateGoogleDriveSettings(Request $request)
    {
        $request->validate([
            'google_client_id' => 'nullable|string',
            'google_client_secret' => 'nullable|string',
            'google_folder_id' => 'nullable|string',
            'google_drive_enabled' => 'boolean',
            'google_redirect_uri' => 'nullable|string',
            'default_upload_storage' => 'required|in:local,google_drive',
        ]);

        try {
            // Update settings in database
            \App\Models\Setting::setValue('google_client_id', $request->google_client_id);
            \App\Models\Setting::setValue('google_client_secret', $request->google_client_secret);
            \App\Models\Setting::setValue('google_folder_id', $request->google_folder_id);
            \App\Models\Setting::setValue('google_drive_enabled', $request->has('google_drive_enabled') ? '1' : '0');
            \App\Models\Setting::setValue('google_redirect_uri', $request->google_redirect_uri);
            \App\Models\Setting::setValue('default_upload_storage', $request->default_upload_storage);
            Log::info('Google Drive settings updated', [
                'admin_id' => auth()->user()->id,
                'enabled' => $request->has('google_drive_enabled')
            ]);

            return redirect()->route('admin.settings')
                ->with('success', __('master.google_drive_settings_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to update Google Drive settings', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.settings')
                ->with('error', __('master.failed_to_update_google_drive_settings'));
        }
    }

    /**
     * Update system settings
     */
    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'max_file_size' => 'required|numeric|min:1|max:100',
            'session_timeout' => 'required|numeric|min:15|max:1440',
            'pagination_limit' => 'required|in:10,25,50,100',
            'maintenance_mode' => 'required|in:0,1',
            'debug_mode' => 'boolean',
        ]);

        try {
            // Update settings in database
            \App\Models\Setting::setValue('max_file_size', $request->max_file_size);
            \App\Models\Setting::setValue('session_timeout', $request->session_timeout);
            \App\Models\Setting::setValue('pagination_limit', $request->pagination_limit);
            \App\Models\Setting::setValue('maintenance_mode', $request->maintenance_mode);
            \App\Models\Setting::setValue('debug_mode', $request->has('debug_mode') ? '1' : '0');

            Log::info('System settings updated', [
                'admin_id' => auth()->user()->id,
                'max_file_size' => $request->max_file_size,
                'maintenance_mode' => $request->maintenance_mode
            ]);

            return redirect()->route('admin.settings')
                ->with('success', __('master.system_settings_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to update system settings', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.settings')
                ->with('error', __('master.failed_to_update_system_settings'));
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        try {
            // Get current email settings from database
            $settings = \App\Models\Setting::all()->pluck('value', 'name')->toArray();
            
            // Configure mail settings dynamically
            Config::set('mail.mailers.smtp.host', $settings['mail_host'] ?? config('mail.mailers.smtp.host'));
            Config::set('mail.mailers.smtp.port', $settings['mail_port'] ?? config('mail.mailers.smtp.port'));
            Config::set('mail.mailers.smtp.username', $settings['mail_username'] ?? config('mail.mailers.smtp.username'));
            Config::set('mail.mailers.smtp.password', $settings['mail_password'] ?? config('mail.mailers.smtp.password'));
            Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
            Config::set('mail.from.address', $settings['mail_from_address'] ?? config('mail.from.address'));
            Config::set('mail.from.name', $settings['mail_from_name'] ?? config('mail.from.name'));

            // Send test email
            Mail::raw('This is a test email from your dental clinic management system. If you receive this, your email configuration is working correctly.', function($message) use ($settings) {
                $message->to(auth()->user()->email)
                        ->subject('Email Configuration Test - Dental Clinic System')
                        ->from($settings['mail_from_address'] ?? config('mail.from.address'), $settings['mail_from_name'] ?? config('mail.from.name'));
            });

            Log::info('Test email sent successfully', [
                'admin_id' => auth()->user()->id,
                'email' => auth()->user()->email
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.email_test_sent')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Google Drive connection
     */
    public function testGoogleDrive(Request $request)
    {
        try {
            // Get current Google Drive settings from database
            $settings = \App\Models\Setting::all()->pluck('value', 'name')->toArray();
            
            if (!$settings['google_drive_enabled'] ?? false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Drive is not enabled in settings'
                ], 400);
            }

            // Test Google Drive connection (basic test)
            $client = new \Google_Client();
            $client->setClientId($settings['google_client_id'] ?? '');
            $client->setClientSecret($settings['google_client_secret'] ?? '');
            $client->setScopes(['https://www.googleapis.com/auth/drive']);

            Log::info('Google Drive test completed', [
                'admin_id' => auth()->user()->id,
                'client_id' => $settings['google_client_id'] ?? ''
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.google_drive_test_sent')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to test Google Drive connection', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to test Google Drive: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test backup system configuration
     */
    public function testBackupSystem()
    {
        try {
            $backupPath = storage_path('backups');
            $diagnostics = [];
            
            // Check backup directory
            $diagnostics['backup_directory'] = [
                'path' => $backupPath,
                'exists' => file_exists($backupPath),
                'writable' => file_exists($backupPath) ? is_writable($backupPath) : false
            ];
            
            // Check database configuration
            $diagnostics['database_config'] = [
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
                'username' => config('database.connections.mysql.username'),
                'password_set' => !empty(config('database.connections.mysql.password')),
                'port' => config('database.connections.mysql.port', 3306)
            ];
            
            // Check mysqldump availability
            $mysqldumpPath = $this->findMysqldumpPath();
            $diagnostics['mysqldump'] = [
                'available' => $mysqldumpPath !== null,
                'path' => $mysqldumpPath
            ];
            
            // Test database connection
            try {
                \DB::connection()->getPdo();
                $diagnostics['database_connection'] = [
                    'status' => 'success',
                    'message' => 'Database connection successful'
                ];
            } catch (\Exception $e) {
                $diagnostics['database_connection'] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
            
            return response()->json([
                'success' => true,
                'diagnostics' => $diagnostics
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnostic failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create database backup
     */
    public function createBackup(Request $request)
    {
        try {
            $backupPath = storage_path('backups');
            
            // Create backups directory if it doesn't exist
            if (!file_exists($backupPath)) {
                if (!mkdir($backupPath, 0755, true)) {
                    throw new \Exception('Unable to create backup directory');
                }
            }

            // Check if directory is writable
            if (!is_writable($backupPath)) {
                throw new \Exception('Backup directory is not writable');
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            // Get database configuration
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $port = config('database.connections.mysql.port', 3306);

            // Validate database configuration
            if (empty($host) || empty($database) || empty($username)) {
                throw new \Exception('Database configuration is incomplete');
            }

            // Check if mysqldump is available
            $mysqldumpPath = $this->findMysqldumpPath();
            if (!$mysqldumpPath) {
                // Fallback to Laravel's database backup using PHP
                return $this->createPhpBackup($filepath, $filename);
            }

            // Create backup command with proper escaping
            $passwordPart = !empty($password) ? "--password=" . escapeshellarg($password) : "";
            $command = sprintf(
                '%s --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers %s > %s 2>&1',
                escapeshellcmd($mysqldumpPath),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $passwordPart,
                escapeshellarg($database),
                escapeshellarg($filepath)
            );
            
            // Execute backup
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // Check if backup file was created and has content
            if ($returnVar !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                $errorMsg = !empty($output) ? implode("\n", $output) : 'Unknown error occurred';
                throw new \Exception('Failed to create database backup: ' . $errorMsg);
            }

            Log::info('Database backup created successfully', [
                'admin_id' => auth()->user()->id,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.backup_created_successfully'),
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($filepath))
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create database backup', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => __('master.failed_to_create_backup') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore database backup
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql|max:10240' // 10MB max
        ]);

        try {
            $backupPath = storage_path('backups');
            $filename = 'restore_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            // Store uploaded file
            $request->file('backup_file')->move($backupPath, $filename);

            // Get database configuration
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Create restore command
            $command = "mysql --host={$host} --user={$username} --password={$password} {$database} < {$filepath}";
            
            // Execute restore
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Failed to restore database backup');
            }

            // Clean up uploaded file
            unlink($filepath);

            Log::info('Database backup restored successfully', [
                'admin_id' => auth()->user()->id,
                'filename' => $filename
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.backup_restored_successfully')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to restore database backup', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => __('master.failed_to_restore_backup') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup history
     */
    public function getBackupHistory()
    {
        try {
            $backupPath = storage_path('backups');
            $backups = [];

            if (file_exists($backupPath)) {
                $files = glob($backupPath . '/backup_*.sql');
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    $backups[] = [
                        'filename' => $filename,
                        'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                        'size' => $this->formatBytes(filesize($file))
                    ];
                }

                // Sort by creation date (newest first)
                usort($backups, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $backups
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get backup history', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get backup history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        try {
            $backupPath = storage_path('backups');
            $filepath = $backupPath . '/' . $filename;

            // Validate filename to prevent directory traversal
            if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
                abort(403, 'Invalid backup filename');
            }

            if (!file_exists($filepath)) {
                abort(404, 'Backup file not found');
            }

            Log::info('Backup file downloaded', [
                'admin_id' => auth()->user()->id,
                'filename' => $filename
            ]);

            return response()->download($filepath, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to download backup file', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id,
                'filename' => $filename
            ]);

            abort(500, 'Failed to download backup file');
        }
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        try {
            $backupPath = storage_path('backups');
            $filepath = $backupPath . '/' . $filename;

            // Validate filename to prevent directory traversal
            if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid backup filename'
                ], 403);
            }

            if (!file_exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            unlink($filepath);

            Log::info('Backup file deleted', [
                'admin_id' => auth()->user()->id,
                'filename' => $filename
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup file deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete backup file', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id,
                'filename' => $filename
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Find mysqldump path on the system
     */
    private function findMysqldumpPath()
    {
        // Common paths for mysqldump
        $commonPaths = [
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/lampp/bin/mysqldump',
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
        ];

        foreach ($commonPaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        // Try to find mysqldump using 'which' on Unix systems
        if (PHP_OS_FAMILY !== 'Windows') {
            $output = [];
            exec('which mysqldump 2>/dev/null', $output);
            if (!empty($output[0]) && is_executable($output[0])) {
                return $output[0];
            }
        } else {
            // Try to find mysqldump using 'where' on Windows
            $output = [];
            exec('where mysqldump 2>nul', $output);
            if (!empty($output[0]) && is_executable($output[0])) {
                return $output[0];
            }
        }

        return null;
    }

    /**
     * Create backup using PHP (fallback method)
     */
    private function createPhpBackup($filepath, $filename)
    {
        try {
            $pdo = \DB::connection()->getPdo();
            
            // Get all tables
            $tables = \DB::select('SHOW TABLES');
            $databaseName = config('database.connections.mysql.database');
            
            $sql = "-- MySQL Database Backup\n";
            $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: {$databaseName}\n\n";
            
            $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql .= "SET AUTOCOMMIT = 0;\n";
            $sql .= "START TRANSACTION;\n";
            $sql .= "SET time_zone = \"+00:00\";\n\n";
            
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                
                // Get table structure
                $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "-- Table structure for table `{$tableName}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Get table data
                $rows = \DB::select("SELECT * FROM `{$tableName}`");
                if (!empty($rows)) {
                    $sql .= "-- Dumping data for table `{$tableName}`\n";
                    $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowData = array_map(function($value) {
                            return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                        }, (array) $row);
                        $values[] = '(' . implode(',', $rowData) . ')';
                    }
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            $sql .= "COMMIT;\n";
            
            if (file_put_contents($filepath, $sql) === false) {
                throw new \Exception('Failed to write backup file');
            }
            
            Log::info('PHP-based database backup created successfully', [
                'admin_id' => auth()->user()->id,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.backup_created_successfully') . ' (PHP method)',
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($filepath))
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create PHP backup', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);
            
            throw new \Exception('PHP backup method failed: ' . $e->getMessage());
        }
    }

    // ==================== LABORATORY MANAGEMENT ====================
    
    public function laboratories()
    {
        return view('admin.laboratory.index');
    }

    public function getlaboratories(Request $request)
    {
        if($request->ajax()){
            $query = User::where('role_id', 4)->with('laboratoryCases');

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'like', '%' . $searchValue . '%')
                      ->orWhere('email', 'like', '%' . $searchValue . '%')
                      ->orWhere('phone', 'like', '%' . $searchValue . '%');
                });
            }

            // Column-specific search
            if ($request->filled('columns.0.search.value')) {
                $query->where('name', 'like', '%' . $request->input('columns.0.search.value') . '%');
            }
            if ($request->filled('columns.1.search.value')) {
                $query->where('email', 'like', '%' . $request->input('columns.1.search.value') . '%');
            }
            if ($request->filled('columns.3.search.value')) {
                $query->where('status', 'like', '%' . $request->input('columns.3.search.value') . '%');
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('laboratory_name', function($row){
                    return $row->name;
                })
                ->addColumn('laboratory_email', function($row){
                    return $row->email;
                })
                ->addColumn('laboratory_count_cases', function($row){
                    return $row->laboratoryCases->count();
                })
                ->addColumn('laboratory_photo', function($row){
                    return '<img src="'.$row->photo_url.'" alt="'.__('master.laboratory_photo').'" class="rounded-circle" style="width: 30px; height: 30px;">';
                })
                ->addColumn('laboratory_status', function($row){
                    if($row->status == 'active'){
                        return '<span class="badge bg-label-success">'.__('master.active').'</span>';
                    }else{
                        return '<span class="badge bg-label-danger">'.__('master.inactive').'</span>';
                    }
                })
                ->addColumn('action', function($row){
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('admin.laboratories.show', $row->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.laboratories.edit', $row->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('admin.laboratories.delete', $row->id).'">'.__('master.delete').'</a></li>
                         
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['action', 'laboratory_status', 'laboratory_photo'])
                ->make(true);
        }
    }

    public function laboratories_create()
    {
        return view('admin.laboratory.create');
    }

    public function laboratories_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'profile-' . time() . '.' . $photo->getClientOriginalExtension();
                // Store on the public disk (-> public/storage/profile-photos) and keep a full link.
                $storedPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $photoPath = asset('storage/' . $storedPath);
            }

            // Create the laboratory
            $laboratory = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
                'photo' => $photoPath,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'bio' => $request->bio,
            ]);

            // Send credentials email if requested
            if ($request->has('send_credentials') && $request->filled('password')) {
                try {
                    // Get email settings from cache
                    $emailSettings = Cache::get('email_settings', []);
                    
                    // Configure mail settings if available
                    if (!empty($emailSettings)) {
                        Config::set('mail.mailers.smtp.host', $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $emailSettings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $emailSettings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $emailSettings['mail_from_name'] ?? config('mail.from.name'));
                    }
                    
                    // Send credentials email
                    Mail::to($laboratory->email)->send(new UserCredentials($laboratory, $request->password, 'laboratory'));
                    
                    Log::info('Laboratory credentials email sent', [
                        'laboratory_id' => $laboratory->id,
                        'laboratory_email' => $laboratory->email,
                        'admin_id' => auth()->user()->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send laboratory credentials email', [
                        'laboratory_id' => $laboratory->id,
                        'laboratory_email' => $laboratory->email,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Laboratory created by admin', [
                'laboratory_id' => $laboratory->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.laboratories.list')
                ->with('success', __('master.laboratory_created_successfully'));

        } catch (\Exception $e) {
            Log::error('Error creating laboratory', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->withInput()
                ->with('error', __('master.failed_to_create_laboratory') . ': ' . $e->getMessage());
        }
    }

    public function laboratories_show($id)
    {
        $laboratory = User::findOrFail($id);
        $cases = CasePatient::where('laboratory_id', $id)->get();
        return view('admin.laboratory.show', compact('laboratory', 'cases'));
    }

    public function laboratories_edit($id)
    {
        $laboratory = User::findOrFail($id);
        return view('admin.laboratory.edit', compact('laboratory'));
    }

    public function laboratories_update(Request $request, $id)
    {
        $laboratory = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle photo upload
            $photoPath = $laboratory->photo; // Keep existing photo by default
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($laboratory->photo && Storage::disk('public')->exists($laboratory->photo)) {
                    Storage::disk('public')->delete($laboratory->photo);
                }
                
                $photo = $request->file('photo');
                $photoName = 'profile-' . time() . '.' . $photo->getClientOriginalExtension();
                // Store on the public disk (-> public/storage/profile-photos) and keep a full link.
                $storedPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $photoPath = asset('storage/' . $storedPath);
            }

            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'role_id' => $request->role_id,
                'photo' => $photoPath,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'bio' => $request->bio,
            ];

            // Update password only if provided
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            // Update the laboratory
            $laboratory->update($updateData);

            // Send credentials email if requested and password was changed
            if ($request->has('send_credentials') && $request->filled('password')) {
                try {
                    // Get email settings from cache
                    $emailSettings = Cache::get('email_settings', []);
                    
                    // Configure mail settings if available
                    if (!empty($emailSettings)) {
                        Config::set('mail.mailers.smtp.host', $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $emailSettings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $emailSettings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $emailSettings['mail_from_name'] ?? config('mail.from.name'));
                    }
                    
                    // Send credentials email
                    Mail::to($laboratory->email)->send(new UserCredentials($laboratory, $request->password, 'laboratory'));
                    
                    Log::info('Laboratory credentials email sent after update', [
                        'laboratory_id' => $laboratory->id,
                        'laboratory_email' => $laboratory->email,
                        'admin_id' => auth()->user()->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send laboratory credentials email after update', [
                        'laboratory_id' => $laboratory->id,
                        'laboratory_email' => $laboratory->email,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Laboratory updated by admin', [
                'laboratory_id' => $laboratory->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.laboratories.show', $laboratory->id)
                ->with('success', __('master.laboratory_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Error updating laboratory', [
                'laboratory_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->withInput()
                ->with('error', __('master.failed_to_update_laboratory') . ': ' . $e->getMessage());
        }
    }

    public function laboratories_delete($id)
    {
        try {
            $laboratory = User::findOrFail($id);
            $laboratory->delete();
            
            Log::info('Laboratory deleted by admin', [
                'laboratory_id' => $id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.laboratories.list')
                ->with('success', __('master.laboratory_deleted_successfully'));
        } catch (\Exception $e) {
            Log::error('Error deleting laboratory', [
                'laboratory_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->with('error', __('master.failed_to_delete_laboratory') . ': ' . $e->getMessage());
        }
    }

    // ==================== TECHNICIAN MANAGEMENT ====================
    
    public function technicians()
    {
        return view('admin.technician.index');
    }

    public function gettechnicians(Request $request)
    {
        if($request->ajax()){
            $query = User::where('role_id', 3)->with('technicianCases');

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'like', '%' . $searchValue . '%')
                      ->orWhere('email', 'like', '%' . $searchValue . '%')
                      ->orWhere('phone', 'like', '%' . $searchValue . '%');
                });
            }

            // Column-specific search
            if ($request->filled('columns.0.search.value')) {
                $query->where('name', 'like', '%' . $request->input('columns.0.search.value') . '%');
            }
            if ($request->filled('columns.1.search.value')) {
                $query->where('email', 'like', '%' . $request->input('columns.1.search.value') . '%');
            }
            if ($request->filled('columns.3.search.value')) {
                $query->where('status', 'like', '%' . $request->input('columns.3.search.value') . '%');
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('technician_name', function($row){
                    return $row->name;
                })
                ->addColumn('technician_email', function($row){
                    return $row->email;
                })
                ->addColumn('technician_count_cases', function($row){
                    return $row->technicianCases->count();
                })
                ->addColumn('technician_photo', function($row){
                    return '<img src="'.$row->photo_url.'" alt="'.__('master.technician_photo').'" class="rounded-circle" style="width: 30px; height: 30px;">';
                })
                ->addColumn('technician_status', function($row){
                    if($row->status == 'active'){
                        return '<span class="badge bg-label-success">'.__('master.active').'</span>';
                    }else{
                        return '<span class="badge bg-label-danger">'.__('master.inactive').'</span>';
                    }
                })
                ->addColumn('action', function($row){
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('admin.technicians.show', $row->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.technicians.edit', $row->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('admin.technicians.delete', $row->id).'">'.__('master.delete').'</a></li>
                         
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['action', 'technician_status', 'technician_photo'])
                ->make(true);
        }
    }

    public function technicians_create()
    {
        return view('admin.technician.create');
    }

    public function technicians_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'profile-' . time() . '.' . $photo->getClientOriginalExtension();
                // Store on the public disk (-> public/storage/profile-photos) and keep a full link.
                $storedPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $photoPath = asset('storage/' . $storedPath);
            }

            // Create the technician
            $technician = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
                'photo' => $photoPath,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'bio' => $request->bio,
            ]);

            // Send credentials email if requested
            if ($request->has('send_credentials') && $request->filled('password')) {
                try {
                    // Get email settings from cache
                    $emailSettings = Cache::get('email_settings', []);
                    
                    // Configure mail settings if available
                    if (!empty($emailSettings)) {
                        Config::set('mail.mailers.smtp.host', $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $emailSettings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $emailSettings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $emailSettings['mail_from_name'] ?? config('mail.from.name'));
                    }
                    
                    // Send credentials email
                    Mail::to($technician->email)->send(new UserCredentials($technician, $request->password, 'technician'));
                    
                    Log::info('Technician credentials email sent', [
                        'technician_id' => $technician->id,
                        'technician_email' => $technician->email,
                        'admin_id' => auth()->user()->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send technician credentials email', [
                        'technician_id' => $technician->id,
                        'technician_email' => $technician->email,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Technician created by admin', [
                'technician_id' => $technician->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.technicians.list')
                ->with('success', __('master.technician_created_successfully'));

        } catch (\Exception $e) {
            Log::error('Error creating technician', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->withInput()
                ->with('error', __('master.failed_to_create_technician') . ': ' . $e->getMessage());
        }
    }

    public function technicians_show($id)
    {
        $technician = User::findOrFail($id);
        $cases = CasePatient::where('technician_id', $id)->get();
        return view('admin.technician.show', compact('technician', 'cases'));
    }

    public function technicians_edit($id)
    {
        $technician = User::findOrFail($id);
        return view('admin.technician.edit', compact('technician'));
    }

    public function technicians_update(Request $request, $id)
    {
        $technician = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            // Handle photo upload
            $photoPath = $technician->photo; // Keep existing photo by default
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($technician->photo && Storage::disk('public')->exists($technician->photo)) {
                    Storage::disk('public')->delete($technician->photo);
                }
                
                $photo = $request->file('photo');
                $photoName = 'profile-' . time() . '.' . $photo->getClientOriginalExtension();
                // Store on the public disk (-> public/storage/profile-photos) and keep a full link.
                $storedPath = $photo->storeAs('profile-photos', $photoName, 'public');
                $photoPath = asset('storage/' . $storedPath);
            }

            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'role_id' => $request->role_id,
                'photo' => $photoPath,
                'specialization' => $request->specialization,
                'license_number' => $request->license_number,
                'bio' => $request->bio,
            ];

            // Update password only if provided
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            // Update the technician
            $technician->update($updateData);

            // Send credentials email if requested and password was changed
            if ($request->has('send_credentials') && $request->filled('password')) {
                try {
                    // Get email settings from cache
                    $emailSettings = Cache::get('email_settings', []);
                    
                    // Configure mail settings if available
                    if (!empty($emailSettings)) {
                        Config::set('mail.mailers.smtp.host', $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'));
                        Config::set('mail.mailers.smtp.port', $emailSettings['mail_port'] ?? config('mail.mailers.smtp.port'));
                        Config::set('mail.mailers.smtp.username', $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'));
                        Config::set('mail.mailers.smtp.password', $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'));
                        Config::set('mail.mailers.smtp.encryption', $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'));
                        Config::set('mail.from.address', $emailSettings['mail_from_address'] ?? config('mail.from.address'));
                        Config::set('mail.from.name', $emailSettings['mail_from_name'] ?? config('mail.from.name'));
                    }
                    
                    // Send credentials email
                    Mail::to($technician->email)->send(new UserCredentials($technician, $request->password, 'technician'));
                    
                    Log::info('Technician credentials email sent after update', [
                        'technician_id' => $technician->id,
                        'technician_email' => $technician->email,
                        'admin_id' => auth()->user()->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send technician credentials email after update', [
                        'technician_id' => $technician->id,
                        'technician_email' => $technician->email,
                        'admin_id' => auth()->user()->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Technician updated by admin', [
                'technician_id' => $technician->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.technicians.show', $technician->id)
                ->with('success', __('master.technician_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Error updating technician', [
                'technician_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->withInput()
                ->with('error', __('master.failed_to_update_technician') . ': ' . $e->getMessage());
        }
    }

    public function technicians_delete($id)
    {
        try {
            $technician = User::findOrFail($id);
            $technician->delete();
            
            Log::info('Technician deleted by admin', [
                'technician_id' => $id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.technicians.list')
                ->with('success', __('master.technician_deleted_successfully'));
        } catch (\Exception $e) {
            Log::error('Error deleting technician', [
                'technician_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return back()->with('error', __('master.failed_to_delete_technician') . ': ' . $e->getMessage());
        }
    }

    /**
     * Update appearance settings
     */
    public function updateAppearanceSettings(Request $request)
    {
        $request->validate([
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            // Favicons can be .ico which is NOT recognised by the `image` rule, so validate by mime only.
            'favicon' => 'nullable|mimes:jpeg,png,jpg,gif,webp,ico,svg|max:1024',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        try {
            // Handle logo upload (store on the public disk -> served at /storage/settings/...)
            if ($request->hasFile('site_logo')) {
                $logo = $request->file('site_logo');
                $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('settings', $logoName, 'public');

                // Delete old logo if exists
                $oldLogo = \App\Models\Setting::getValue('site_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                \App\Models\Setting::setValue('site_logo', 'settings/' . $logoName);
            }

            // Handle favicon upload (store on the public disk -> served at /storage/settings/...)
            if ($request->hasFile('favicon')) {
                $favicon = $request->file('favicon');
                $faviconName = 'favicon_' . time() . '.' . $favicon->getClientOriginalExtension();
                $favicon->storeAs('settings', $faviconName, 'public');

                // Delete old favicon if exists
                $oldFavicon = \App\Models\Setting::getValue('favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }

                \App\Models\Setting::setValue('favicon', 'settings/' . $faviconName);
            }

            // Update primary color
            if ($request->filled('primary_color')) {
                \App\Models\Setting::setValue('primary_color', $request->primary_color);
            }

            Log::info('Appearance settings updated', [
                'admin_id' => auth()->user()->id,
                'has_logo' => $request->hasFile('site_logo'),
                'has_favicon' => $request->hasFile('favicon')
            ]);

            return redirect()->route('admin.settings')
                ->with('success', __('master.appearance_settings_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to update appearance settings', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.settings')
                ->with('error', __('master.failed_to_update_appearance_settings'));
        }
    }

}
