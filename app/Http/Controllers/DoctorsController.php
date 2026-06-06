<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CasePatient;
use App\Models\ToothProblem;
use App\Models\User;
use App\Models\Patient;
use Auth;
use DB;
use Mail;
use App\Mail\SendNotification;
use App\Models\Notification;    
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ToothProblemCase;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FileUpload;
use Illuminate\Support\Facades\Hash;
use App\Models\Task;
use App\Models\Calendar;
use App\Models\Tickets;
use App\Models\Comment;
use App\Models\TreatmentType;
use Illuminate\Support\Facades\Storage;
use App\Providers\GoogleDriveService;
use App\Http\Controllers\Concerns\GroupsCasesByPatient;
class DoctorsController extends Controller
{
    use GroupsCasesByPatient;
   
    public function check_code_doctor(Request $request)
    {
        $user = User::where('code_parrent', $request->code_parrent)->where('role_id', 2)->where('status', 'active')->first();
        if($user){
            return response()->json(['status' => 'success', 'data' => $user]);
        }else{
            return response()->json(['status' => 'error', 'data' => '']);
        }
    }
    public function index()
    {
        $cases = CasePatient::with('patient')->where('doctor_id', auth()->user()->id)->get();    
        $status_pending  = CasePatient::where('status', 'pending')->where('doctor_id', auth()->user()->id)->count();
        $status_draft = CasePatient::where('status', 'draft')->where('doctor_id', auth()->user()->id)->count();
        $status_in_planning = CasePatient::where('status', 'in_planning')->where('doctor_id', auth()->user()->id)->count();
        $status_approval = CasePatient::where('status', 'approval')->where('doctor_id', auth()->user()->id)->count();
        $status_rejected = CasePatient::where('status', 'rejected')->where('doctor_id', auth()->user()->id)->count();
        $status_in_production = CasePatient::where('status', 'in_production')->where('doctor_id', auth()->user()->id)->count();
        $status_shipped = CasePatient::where('status', 'shipped')->where('doctor_id', auth()->user()->id)->count();
        $count_cases = $cases->where('doctor_id', auth()->user()->id)->count();

        $case_retarded = CasePatient::where('status', 'pending')->where('doctor_id', auth()->user()->id)->count();
       if($count_cases > 0){
        $case_retarded_percentage = number_format(($case_retarded / $count_cases) * 100, 2);
       }else{
        $case_retarded_percentage = 0;
       }
       $new_cases = CasePatient::where('doctor_id', auth()->user()->id)->where('created_at', '>=', now()->subDays(30))->count();
       $count_patient = User::where('doctor_id', auth()->user()->id)->count();
      
      
       // Get all months with their totals for the last 30 days
       $cases_by_month = CasePatient::where('doctor_id', auth()->user()->id)
           ->where('created_at', '>=', now()->subDays(30))
           ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
           ->groupBy('month')
           ->pluck('total', 'month')
           ->toArray();

       // Create array with all months, defaulting to 0
       $monthly_totals = [];
       for ($i = 1; $i <= 12; $i++) {
           $monthly_totals[] = $cases_by_month[$i] ?? 0;
       }

        $patientGroups = $this->buildPatientGroups($cases, [
            'show'   => 'doctor.cases.show',
            'edit'   => 'doctor.cases.edit',
            'delete' => 'doctor.cases.delete',
        ]);

        return view('doctor.dashboard', compact('cases', 'status_pending', 'status_draft', 'status_in_planning', 'status_approval', 'status_rejected', 'status_in_production', 'status_shipped', 'count_cases', 'case_retarded', 'case_retarded_percentage', 'new_cases', 'count_patient', 'monthly_totals', 'patientGroups'));
    }
     
    public function latest_cases(Request $request)
    {
        if($request->ajax()){
            $query = CasePatient::query()
                ->where('doctor_id', auth()->user()->id)
                ->with(['patient', 'technician', 'laboratory']);

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

            $data = $query->latest()->get();
            
            return DataTables::of($data)
                ->addIndexColumn()
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
                ->addColumn('action', function($row){
                    $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a class="dropdown-item waves-effect" href="'.route('doctor.cases.show', $row->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('doctor.cases.edit', $row->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('doctor.cases.delete', $row->id).'">'.__('master.delete').'</a></li>
                           
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
    }
   



    

     public function case_store(Request $request)
     {
        $request->validate([
            'patient_type' => 'required|in:existing,new',
            'patient_id' => 'required_if:patient_type,existing|exists:patients,id',
            // New patient validation rules
            'name' => 'required_if:patient_type,new',
            'surname' => 'required_if:patient_type,new',
            'gender' => 'required_if:patient_type,new',
            'phone' => 'required_if:patient_type,new',
            // Case validation rules
            'treatment_type' => 'required',
            'treatment_treat' => 'required',
            'treatment_overjet' => 'required',
            'treatment_overbite' => 'required',
            'treatment_midline' => 'required',
            'treatment_irp' => 'required',
            'treatment_attachments' => 'required',
        ]);

        DB::beginTransaction();
        try {
              
            $googleDriveService = new GoogleDriveService();
            // Handle patient
            if ($request->patient_type === 'new') {
                $patient = Patient::create([
                    'reference' => $request->reference,
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'gender' => $request->gender,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                    'country' => $request->country,
                    'birth_date' => $request->birth_date,
                ]);
                $patientId = $patient->id;
            } else {
                $patientId = $request->patient_id;
            }

            // Create case
            $case = CasePatient::create([
                'case_id' => $request->case_id,
                'patient_id' => $patientId,
                'doctor_id' => auth()->id(),
                'treatment_type' => $request->treatment_type,
                'treatment_treat' => $request->treatment_treat,
                'treatment_overjet' => $request->treatment_overjet,
                'treatment_overbite' => $request->treatment_overbite,
                'treatment_midline' => $request->treatment_midline,
                'treatment_irp' => $request->treatment_irp,
                'treatment_attachments' => $request->treatment_attachments,
                'doctor_instruction' => $request->doctor_instruction,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'type_of_scan' => $request->impression_type,
                'technician_id' => $request->technician_id,
                'laboratory_id' => $request->laboratory_id,
                'patient_chief_complaint' => $request->patient_chief_complaint,
                'status' => $request->technician_id == null && $request->laboratory_id == null ? CasePatient::getStatuses()[0] : CasePatient::getStatuses()[1],
            ]);
           
            $calendar = Calendar::create([
                'title' => $case->case_id,
                'description' => 'New Case '.$case->case_id,
                'start' => now()->format('Y-m-d H:i:s'),
                'end' => now()->addHour()->format('Y-m-d H:i:s'),
                'user_id' => auth()->id(),
                'color' => 'primary',
                'url' => route('doctor.cases.show', $case->id),
            ]);


            $upper_scan = $request->file('upper_scan');
            if($upper_scan){
              
                $uploadResult = $googleDriveService->uploadForUser($upper_scan, auth()->user(), null, "case_files", $case->case_id);
               
               
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
            FileUpload::create([
                'case_id' => $case->id,
                'patient_id' => $patientId,
                'wich_rubrique' => 'upper_scan',
                'path' => $uploadResult['webViewLink'],    
                'type' => explode('.', $upper_scan->getClientOriginalName())[1],
                'name' => explode('.', $upper_scan->getClientOriginalName())[0],
                    'size' => $upper_scan->getSize(),
                    'url' => $link,
                ]);
            }

                $lower_scan = $request->file('lower_scan');
            if($lower_scan){
                $uploadResult = $googleDriveService->uploadForUser($lower_scan, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'lower_scan',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $lower_scan->getClientOriginalName())[1],
                    'name' => explode('.', $lower_scan->getClientOriginalName())[0],
                    'size' => $lower_scan->getSize(),
                    'url' => $link,
                ]);
            }

            $bite_scan = $request->file('bite_scan');
            if($bite_scan){
                $uploadResult = $googleDriveService->uploadForUser($bite_scan, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'bite_scan',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $bite_scan->getClientOriginalName())[1],
                    'name' => explode('.', $bite_scan->getClientOriginalName())[0],
                    'size' => $bite_scan->getSize(),
                    'url' => $link,
                ]);
            }



            if($request->hasFile('photo_clinic_01')){
                $photo_clinic_01 = $request->file('photo_clinic_01');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_01, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
               
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_01',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_01->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_01->getClientOriginalName())[0],
                    'size' => $photo_clinic_01->getSize(),
                    'url' => $link,
                ]);
            }
            if($request->hasFile('photo_clinic_02')){
                $photo_clinic_02 = $request->file('photo_clinic_02');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_02, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_02',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_02->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_02->getClientOriginalName())[0],
                    'size' => $photo_clinic_02->getSize(),
                    'url' => $link,
                ]);
            }
            if($request->hasFile('photo_clinic_03')){
                $photo_clinic_03 = $request->file('photo_clinic_03');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_03, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_03',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_03->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_03->getClientOriginalName())[0],
                    'size' => $photo_clinic_03->getSize(),
                    'url' => $link,
                ]);
            }
            if($request->hasFile('photo_clinic_04')){
                $photo_clinic_04 = $request->file('photo_clinic_04');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_04, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_04',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_04->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_04->getClientOriginalName())[0],
                    'size' => $photo_clinic_04->getSize(),
                    'url' => $link,
                ]);
            }
            if($request->hasFile('photo_clinic_05')){
                $photo_clinic_05 = $request->file('photo_clinic_05');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_05, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_05',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_05->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_05->getClientOriginalName())[0],
                    'size' => $photo_clinic_05->getSize(),
                    'url' => $link,
                ]);
            }
            
            if($request->hasFile('photo_clinic_06')){
                $photo_clinic_06 = $request->file('photo_clinic_06');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_06, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_06',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_06->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_06->getClientOriginalName())[0],
                    'size' => $photo_clinic_06->getSize(),
                    'url' => $link,
                ]);
            }

            if($request->hasFile('photo_clinic_07')){
                $photo_clinic_07 = $request->file('photo_clinic_07');
                $uploadResult = $googleDriveService->uploadForUser($photo_clinic_07, auth()->user(), null, "case_files", $case->case_id);
                $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                FileUpload::create([
                    'case_id' => $case->id,
                    'patient_id' => $patientId,
                    'wich_rubrique' => 'photo_clinic_07',
                    'path' => $uploadResult['webViewLink'],
                    'type' => explode('.', $photo_clinic_07->getClientOriginalName())[1],
                    'name' => explode('.', $photo_clinic_07->getClientOriginalName())[0],
                    'size' => $photo_clinic_07->getSize(),
                    'url' => $link,
                ]);
            }
            if($request->hasFile('photo_radiographs')){
                foreach($request->file('photo_radiographs') as $photo){

                    $uploadResult = $googleDriveService->uploadForUser($photo, auth()->user(), null, "case_files", $case->case_id);
                    $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                    FileUpload::create([
                        'case_id' => $case->id,
                        'patient_id' => $patientId,
                        'wich_rubrique' => 'photo_radiographs',
                        'path' => $uploadResult['webViewLink'],
                        'type' => explode('.', $photo->getClientOriginalName())[1],
                        'name' => explode('.', $photo->getClientOriginalName())[0],
                        'size' => $photo->getSize(),
                        'url' => $link,
                    ]);
                }
            }

            if($request->hasFile('other_files')){
                foreach($request->file('other_files') as $photo){

                    $uploadResult = $googleDriveService->uploadForUser($photo, auth()->user(), null, "case_files", $case->case_id);
                    $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
                    FileUpload::create([
                        'case_id' => $case->id,
                        'patient_id' => $patientId,
                        'wich_rubrique' => 'other_files',
                        'path' => $uploadResult['webViewLink'],
                        'type' => explode('.', $photo->getClientOriginalName())[1],
                        'name' => explode('.', $photo->getClientOriginalName())[0],
                        'size' => $photo->getSize(),
                        'url' => $link,
                    ]);
                }   
            }
            // Handle tooth problems
            $toothNumbers = $request->input('tooth_problems', []);

            foreach ($toothNumbers as $toothNumber => $data) {
                $problemId = $data['problem_id'] ?? null;
                $notes = $data['notes'] ?? null;
                if ($problemId) {
                    ToothProblemCase::create([
                        'case_id' => $case->id,
                        'tooth_number' => $toothNumber,
                        'tooth_problem_id' => $problemId,
                        'tooth_notes' => $notes
                    ]);
                }
            }

            DB::commit();
            $technician = null;
            $laboratory = null;
            if($case->technician_id != null){
                $technician = User::find($case->technician_id);
            }
            if($case->laboratory_id != null){
                $laboratory = User::find($case->laboratory_id);
            }
            if($technician != null && $laboratory != null){
            $notification = Notification::create([
                'title' => 'New Case',
                'message' => 'New case has been created',
                'type' => 'case',
                'status' => 'pending',
                'case_id' => $case->id,
                'doctor_id' => auth()->id(),
                'technician_id' => $technician->id,
                'laboratory_id' => $laboratory->id,
                'doctor_id' => auth()->id(),
            ]);
                Mail::to($technician->email)->send(new SendNotification($notification));
                Mail::to($laboratory->email)->send(new SendNotification($notification));
            }


            return redirect()
                ->route('doctor.cases')
                ->with('success', __('master.case_created_successfully'))
                ->with('upload_complete', true);

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', __('master.error_creating_case') . ' ' . $e->getMessage());
        }
     }  

     public function case_update(Request $request, $id)
     {
        $case = CasePatient::where('id', $id)->where('doctor_id', auth()->user()->id)->first();
        $case->update($request->all());
    
        if($request->has('tooth_problems')){    
        ToothProblemCase::where('case_id', $id)->delete();
        $toothNumbers = $request->input('tooth_problems', []);

        foreach ($toothNumbers as $toothNumber => $data) {
            $problemId = $data['problem_id'] ?? null;
            $notes = $data['notes'] ?? null;
            if ($problemId) {
                ToothProblemCase::create([
                    'case_id' => $case->id,
                    'tooth_number' => $toothNumber,
                    'tooth_problem_id' => $problemId,
                    'tooth_notes' => $notes
                ]);
            }
        }
        }
       
      
        $notification = Notification::create([
            'case_id' => $id,
            'title' => 'Case Updated',
            'message' => 'Case has been updated',
            'type' => 'case',
            'status' => 'pending',
            'doctor_id' => auth()->id(),
            'technician_id' => $case->technician_id,
            'laboratory_id' => $case->laboratory_id,
        ]);
        Mail::to($case->technician->email)->send(new SendNotification($notification));
        Mail::to($case->laboratory->email)->send(new SendNotification($notification));

        toastr()->success(__('master.case_updated'));
        return redirect()->route('doctor.cases');
     }
     
    public function case_delete($id)
    {
        $case = CasePatient::where('id', $id)->where('doctor_id', auth()->user()->id)->first();
        $case->delete();
        Notification::where('case_id', $id)->delete();
        FileUpload::where('case_id', $id)->delete();
        ToothProblemCase::where('case_id', $id)->delete();
        if(Invoice::where('case_id', $id)->exists()){
        Invoice::where('case_id', $id)->delete();
        $invoice_id = Invoice::where('case_id', $id)->first()->id;
        if($invoice_id){
        Payment::where('invoice_id', $invoice_id)->delete();
        }
        }
        Comment::where('case_id', $id)->delete();
        WeTransferNotification::where('case_id', $id)->delete();
        Patient::where('id', $case->patient_id)->delete();
        toastr()->success(__('master.case_deleted'));
        return redirect()->route('doctor.cases');
    }

    public function change_status($id, $status)
    {
        $case = CasePatient::where('id', $id)->where('doctor_id', auth()->user()->id)->first();
        $case->status = $status;
        $case->save();
        toastr()->success(__('master.status_changed'));
        return redirect()->route('doctor.cases');
    }





    public function add_comment(Request $request)
    {
        $request->validate([
            'comment' => 'required',
        ]);
            $comment = Comment::create([
                'case_id' => $request->case_id,
                'comment' => $request->comment,
                'user_id' => auth()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.comment_added_successfully'),
                'comment' => $comment->comment,
                'user' => auth()->user()->name,
                'date' => $comment->created_at->format('Y-m-d H:i:s'),
                'user_photo' => auth()->user()->photo,
            ]);
    }   

    public function get_comments($id)
    {
        $comments = Comment::where('case_id', $id)->latest()->get();
        return response()->json($comments);
    }


    public function exportcasesPdf(Request $request){
       
        $query = CasePatient::query()
        ->where('doctor_id', auth()->user()->id);

      

    // Apply filters
    if ($request->has('case_id') && $request->case_id != null) {
        $query->where('case_id', 'like', '%' . $request->case_id . '%');
    }

    if ($request->has('patient_id') && $request->patient_id != null) {
        $query->where('patient_id', $request->patient_id);
    }

    if ($request->has('treatment_type') && $request->treatment_type != null) {
        $query->where('treatment_type', $request->treatment_type);
    }
    if($request->has('technician_id') && $request->technician_id != null){
        $query->where('technician_id', $request->technician_id);
    }
    if($request->has('laboratory_id') && $request->laboratory_id != null){
        $query->where('laboratory_id', $request->laboratory_id);
    }


    if ($request->has('status') && $request->status != null) {
        $query->where('status', $request->status);
    }

    $data = $query->latest()->get();
  
   
   $pdf= Pdf::loadView('doctor.cases.exportPdf', compact('data'));

    return view('doctor.cases.exportPdf', compact('data'));

    }

    public function send_notification($id)
    {
        $case = CasePatient::where('id', $id)->where('doctor_id', auth()->user()->id)->first();
        $technician = User::find($case->technician_id);
        $laboratory = User::find($case->laboratory_id);
        if($technician != null && $laboratory != null){
        $notification = Notification::create([
            'title' => 'New Case',
            'message' => 'New case has been created',
            'type' => 'case',
            'status' => 'in_planning',
            'case_id' => $case->id,
            'technician_id' => $technician->id,
            'laboratory_id' => $laboratory->id,
            'doctor_id' => auth()->id(),
        ]);
        Mail::to($technician->email)->send(new SendNotification($notification));
        Mail::to($laboratory->email)->send(new SendNotification($notification));

        toastr()->success( __('master.notification_sent'));
        }else{
            toastr()->error( __('master.notification_not_sent'));
        }
        
               
                return redirect()->route('doctor.cases');
       
              
    }

   
  
    
    
    
    public function technician_list()
    {
        $technicians = User::where('role_id', 3)->where('doctor_id', auth()->user()->id)->get();
        return view('doctor.technicians.index', compact('technicians'));
    }



    public function gettechnicians(Request $request)
    {
        if($request->ajax()){
        $technicians = User::where('role_id', 3)->where('doctor_id', auth()->user()->id)->get();
        return DataTables::of($technicians)
        ->addIndexColumn()
        ->addColumn('technician_name', function($row){
            return $row->name;
        })
        ->addColumn('technician_email', function($row){
            return $row->email;
        })
        ->addColumn('technician_count_cases', function($row){
            $count = $row->casesTechnician->count();
            if($count > 0){
                return $count.' '. __('master.cases').' <a href="'.route('doctor.technicians.show', $row->id).'" class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" title="'.__('master.view_cases').'" data-bs-toggle="tooltip"><i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i></a>';
            }else{
                return $count .' '. __('master.cases');
            }
        })
        ->addColumn('technician_status', function($row){
            if($row->status == 'active'){
                return '<span class="badge bg-label-success">'.__('master.active').'</span>';
            }else{
                return '<span class="badge bg-label-danger">'.__('master.inactive').'</span>';
            }
        })
        ->addColumn('technician_photo', function($row){
            if($row->photo != null){
                return '
                <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="'.$row->name.'" data-bs-original-title="'.$row->name.'">
                              <img src="'.$row->photo.'" alt="Avatar" class="rounded-circle">
                            </li>
                          
                          </ul>';
            }else{
                return '
                <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="'.$row->name.'" data-bs-original-title="'.$row->name.'">
                              <img src="'.asset('assets/img/avatars/default.png').'" alt="Avatar" class="rounded-circle">
                            </li>
                          
                          </ul>';
            }
        })
        ->addColumn('action', function($row){
            $button = '
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                           <li><a href="'.route('doctor.technicians.show', $row->id).'" class="dropdown-item waves-effect">'.__('master.view_profile').'</a></li>
                           <li><a href="'.route('doctor.technicians.status', $row->id).'" class="dropdown-item waves-effect">'.__('master.change_status').'</a></li>
                           <li><a href="'.route('doctor.technicians.edit', $row->id).'" class="dropdown-item waves-effect">'.__('master.edit').'</a></li>
                        </ul>
                    </div>';    
                    return $button;

         

        })
        ->rawColumns(['technician_photo', 'action', 'technician_status', 'technician_count_cases'])
            ->make(true);
        }
    }



    public function technician_create()
    {
        return view('doctor.technicians.create');
    }   

    public function technician_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).+$/',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3,
            'status' => 'active',
            'doctor_id' => auth()->user()->id,
        ]);
        $notification = Notification::create([
            'title' => 'New Technician',
            'message' => 'New technician has been created by '.auth()->user()->name.'. Your account is ready to use. Your password is: '.$request->password,
            'type' => 'new user',
            'status' => 'active',
            'technician_id' => $user->id,
            'doctor_id' => auth()->user()->id,
        ]);
        $technician = User::find($user->id);
        Mail::to($technician->email)->send(new SendNotification($notification));
        toastr()->success( __('master.technician_created_successfully'));

        return redirect()->route('doctor.technicians.index');
    }   

    public function technician_edit($id)
    {
        $technician = User::find($id);
        return view('doctor.technicians.edit', compact('technician'));
    }

    public function technician_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);
        $technician = User::find($id);
        if($request->filled('password')){
            $request->validate([
                'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'password_confirmation' => 'required|same:password',
            ]);
        }

        $technician->update($request->all());
        toastr()->success( __('master.technician_updated_successfully'));
        return redirect()->route('doctor.technicians.index');
    }   




    

    public function getlaboratories(Request $request)
    {
        if($request->ajax()){
            $laboratories = User::where('role_id', 4)->where('doctor_id', auth()->user()->id)->get();
            return DataTables::of($laboratories)
            ->addIndexColumn()
            ->addColumn('laboratory_name', function($row){
                return $row->name;
            })
            ->addColumn('laboratory_email', function($row){
                return $row->email;
            })
            ->addColumn('laboratory_count_cases', function($row){
                $count = $row->casesLaboratory->count();
                if($count > 0){
                    return $count.' '. __('master.cases').' <a href="'.route('doctor.laboratory.show', $row->id).'" class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" title="'.__('master.view_cases').'" data-bs-toggle="tooltip"><i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i></a>';
                }else{
                    return $count .' '. __('master.cases');
                }
            })
            ->addColumn('laboratory_status', function($row){
                if($row->status == 'active'){
                    return '<span class="badge bg-label-success">'.__('master.active').'</span>';
                }else{
                    return '<span class="badge bg-label-danger">'.__('master.inactive').'</span>';
                }
            })
            ->addColumn('laboratory_photo', function($row){
                if($row->photo != null){
                    return '
                    <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="'.$row->name.'" data-bs-original-title="'.$row->name.'">
                                  <img src="'.$row->photo.'" alt="Avatar" class="rounded-circle">
                                </li>
                              
                              </ul>';
                }else{
                    return '
                    <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="'.$row->name.'" data-bs-original-title="'.$row->name.'">
                                  <img src="'.asset('assets/img/avatars/default.png').'" alt="Avatar" class="rounded-circle">
                                </li>
                              
                              </ul>';
                }
            })
            ->addColumn('action', function($row){
                $button = '
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                               <li><a href="'.route('doctor.laboratory.show', $row->id).'" class="dropdown-item waves-effect">'.__('master.view_profile').'</a></li>
                               <li><a href="'.route('doctor.laboratory.status', $row->id).'" class="dropdown-item waves-effect">'.__('master.change_status').'</a></li>
                               <li><a href="'.route('doctor.laboratory.edit', $row->id).'" class="dropdown-item waves-effect">'.__('master.edit').'</a></li>
                            </ul>
                        </div>';    
                        return $button;
    
             
    
            })
            ->rawColumns(['laboratory_photo', 'action', 'laboratory_status', 'laboratory_count_cases'])
                ->make(true);
            }
    }

    public function laboratory_create()
    {
        return view('doctor.laboratory.create');
    }

    public function laboratory_store(Request $request)      

    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).+$/',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => 'active',
            'password' => Hash::make($request->password),
            'role_id' => 4,
            'doctor_id' => auth()->user()->id,

        ]);
        $notification = Notification::create([
            'title' => 'New Laboratory',
            'message' => 'New laboratory has been created by '.auth()->user()->name.'. Your account is ready to use. Your password is: '.$request->password,
            'type' => 'new user',
            'status' => 'active',
            'laboratory_id' => $user->id,
            'doctor_id' => auth()->user()->id,
        ]);
        $laboratory = User::find($user->id);
        Mail::to($laboratory->email)->send(new SendNotification($notification));

        toastr()->success( __('master.laboratory_created_successfully'));

        return redirect()->route('doctor.laboratory.index');

    }

    public function laboratory_edit($id)
    {
        $laboratory = User::find($id);
        return view('doctor.laboratory.edit', compact('laboratory'));   
    }

    public function laboratory_update(Request $request, $id)
    {
        $laboratory = User::find($id);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);
        if($request->filled('password')){
            $request->validate([
                'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).+$/',   
                'password_confirmation' => 'required|same:password',
            ]);
        }   
        $laboratory->update($request->all());
        toastr()->success( __('master.laboratory_updated_successfully'));
        return redirect()->route('doctor.laboratory.index');
    }

    public function laboratory_show($id)
    {
        $laboratory = User::find($id);
        $cases = CasePatient::where('laboratory_id', $id)->with(['patient'])->latest()->get();
        return view('doctor.laboratory.show', compact('laboratory', 'cases'));
    }

    public function technician_show($id)
    {
        $technician = User::find($id);
        $cases = CasePatient::where('technician_id', $id)->with(['patient'])->latest()->get();

        return view('doctor.technicians.show', compact('technician', 'cases'));
    }




    public function technician_cases(Request $request, $id)
    {

        if($request->ajax()){
        $cases = CasePatient::where('technician_id', $id)->with(['patient'])->latest()->get();
        return DataTables::of($cases)
        ->addIndexColumn()
        ->addColumn('case_number', function($row){
            return '<a href="'.route('doctor.cases.show', $row->id).'">'.$row->case_id.'</a>';
        })
        ->addColumn('patient_name', function($row){
            if($row->patient != null){
                return $row->patient->name;
            }else{
                return __('master.no_patient');
            }
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
        ->rawColumns(['status', 'case_number'])
        ->make(true);
        }
    }


    public function laboratory_cases(Request $request, $id)
    {
        if($request->ajax()){
        $cases = CasePatient::where('laboratory_id', $id)->with(['patient'])->latest()->get();
        return DataTables::of($cases)
        ->addIndexColumn()
        ->addColumn('case_number', function($row){
            return '<a href="'.route('doctor.cases.show', $row->id).'">'.$row->case_id.'</a>';
        })
        ->addColumn('patient_name', function($row){
            if($row->patient != null){
                return $row->patient->name;
            }else{
                return __('master.no_patient');
            }   
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
        ->rawColumns(['case_number', 'status'])
        ->make(true);
    }
    }


    public function laboratory_list()
    {
        $laboratories = User::where('doctor_id', auth()->user()->id)->get();
        return view('doctor.laboratory.index', compact('laboratories'));
    }




    
    public function patients_list()
    {
        $listpatients_by_doctor = CasePatient::where('doctor_id', auth()->user()->id)->get();
        $listpatients_by_doctor_id = $listpatients_by_doctor->pluck('patient_id');
        $patients = Patient::whereIn('id', $listpatients_by_doctor_id)->get();
        return view('doctor.patients.index', compact('patients'));
    }



    public function getpatients(Request $request)
    {
        if($request->ajax()){
            $listpatients_by_doctor = CasePatient::where('doctor_id', auth()->user()->id)->get();
        $listpatients_by_doctor_id = $listpatients_by_doctor->pluck('patient_id');
        $patients = Patient::whereIn('id', $listpatients_by_doctor_id)->get();
            return DataTables::of($patients)
            ->addIndexColumn()
            ->addColumn('patient_reference', function($row){
                return $row->reference;
            })
            ->addColumn('case_id', function($row){
               $case = CasePatient::where('patient_id', $row->id)->get();
               $case_id = $case->map(function($item){
                return '<a href="'.route('doctor.cases.show', $item->id).'">'.$item->case_id.'</a>';
               });
               return $case_id->implode('<br> ');
            })
            ->addColumn('patient_name', function($row){
                return $row->name . ' ' . $row->surname;
            })
           
            ->addColumn('patient_gender', function($row){
                return ucfirst($row->gender);
            })  
            ->addColumn('patient_phone', function($row){
                return $row->phone;
            })
            ->addColumn('patient_email', function($row){
                return $row->email;
            })  
            ->addColumn('patient_address', function($row){
                return $row->address;
            })
            ->addColumn('patient_city', function($row){
                return $row->city;
            })
            ->addColumn('patient_country', function($row){

                return  $row->country;
            })
            ->addColumn('patient_birthday', function($row){
                return Carbon::parse($row->birthday)->format('d M Y');
            })
            ->addColumn('created_at', function($row){
                return Carbon::parse($row->created_at)->format('d M Y');
            })
            ->addColumn('updated_at', function($row){
                return Carbon::parse($row->updated_at)->format('d M Y');
            })
           
            ->addColumn('action', function($row){
                $button = '
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                       <li><a href="'.route('doctor.patients.show', $row->reference).'" class="dropdown-item waves-effect">'.__('master.view_profile').'</a></li>
                       <li><a href="'.route('doctor.patients.edit', $row->reference).'" class="dropdown-item waves-effect">'.__('master.edit').'</a></li>
                    </ul>
                </div>';    
                return $button;


               
            })
            ->rawColumns(['action', 'case_id', 'patient_reference'])
            ->make(true);
        }
    }

    public function patients_show($reference)
    {   
        $patient = Patient::where('reference', $reference)->first();
        return view('doctor.patients.show', compact('patient'));
    }

   
    public function patients_create()
    {
        return view('doctor.patients.create');
    }       

    public function patients_edit($reference)
    {

        $patient = Patient::where('reference', $reference)->first();
        return view('doctor.patients.edit', compact('patient'));
    }

    public function patients_details($reference)
    {
        $patient = Patient::where('reference', $reference)->first();
        return response()->json($patient);
    }

    public function patients_delete($reference)
    {
        $patient = Patient::where('reference', $reference)->first();
        $patient->delete();
        toastr()->success(__('master.patient_deleted'));
        return redirect()->route('doctor.patients');
    }

    public function patients_store(Request $request)
    {
        $request->validate([
            'reference' => 'required|unique:patients',
            'name' => 'required',
            'surname' => 'required',
            'gender' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:patients'
        ]);
        $patient = Patient::create($request->all());
        toastr()->success(__('master.patient_created'));
        return redirect()->route('doctor.patients');
    }   

    public function patients_update(Request $request, $reference)
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'gender' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:patients'
        ]);
        $patient = Patient::where('reference', $reference)->first();
        $patient->update($request->all());
        toastr()->success(__('master.patient_updated'));
        return redirect()->route('doctor.patients');
    }

    //tickets
    public function tickets()
    {
        $tickets = Tickets::where('user_id', auth()->user()->id)->with('assigned_to')->latest()->paginate(10);    
        $users = User::where('doctor_id', auth()->user()->id)->where('status', 'active')->get();
        return view('doctor.tickets.index', compact('tickets', 'users'));
    }



    public function tickets_create()
    {
        return view('doctor.tickets.create');
    }

    public function tickets_store(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'subject' => 'required',
            'priority' => 'required',
            'assigned_to' => 'required',
        ]);
        $ticket = Tickets::create([
            'message' => $request->message,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'user_id' => auth()->user()->id,
            'assigned_to' => $request->assigned_to,
            'status' => 'closed',
            'ticket_id' => null,

        ]);
        $assigned_to = explode('-', $request->assigned_to);
        $notification = Notification::create([
            'title' => 'New Ticket',
            'message' => 'New ticket has been created by '.auth()->user()->name.'.',
            'type' => 'new ticket',
            'status' => 'active',
            'ticket_id' => $ticket->id,
            'doctor_id' => auth()->user()->id,
            'technician_id' =>  $assigned_to[1] == 'technicien' ? $assigned_to[0] : null,
            'laboratory_id' => $assigned_to[1] == 'laboratoire' ? $assigned_to[0] : null,
        ]);
        toastr()->success(__('master.ticket_created'));
        return redirect()->route('doctor.tickets.index');
    }

    public function tickets_show($id)
    {
        $ticket = Tickets::find($id);
        $ticket->update(['status' => 'open']);
        return response()->json(['ticket' => $ticket]);
    }   

    public function tickets_edit($id)
    {
        $ticket = Tickets::find($id);
        return view('doctor.tickets.edit', compact('ticket'));
    }   

    public function tickets_update(Request $request, $id)    
    {
        $request->validate([
            'message' => 'required',
            'subject' => 'required',
            'priority' => 'required',
        ]);

        $ticket = Tickets::find($id);
        $ticket->update($request->all());
        toastr()->success(__('master.ticket_updated'));
        return redirect()->route('doctor.tickets.index');
    }

    public function tickets_delete($id)
    {
        $ticket = Tickets::find($id);
        $ticket->delete();

        $notification = Notification::create([
            'title' => 'Ticket Deleted',
            'message' => 'Ticket has been deleted by '.auth()->user()->name.'.',
            'type' => 'ticket deleted',
            'status' => 'active',
            'ticket_id' => $ticket->id,
            'doctor_id' => auth()->user()->id
        ]);
        toastr()->success(__('master.ticket_deleted'));
        return redirect()->route('doctor.tickets.index');
    }


    public function tickets_status($id, $status)
    {
        $ticket = Tickets::find($id);
        $ticket->status = $status;
        $ticket->save();
        return redirect()->route('doctor.tickets.index');
    }   

    public function tickets_priority($id, $priority)
    {
        $ticket = Tickets::find($id);
        $ticket->priority = $priority;
        $ticket->save();
        return redirect()->route('doctor.tickets.index');
    }   

    public function tickets_close($id)
    {
        $ticket = Tickets::find($id);
        $ticket->status = 'closed';
        $ticket->save();
        toastr()->success(__('master.ticket_closed'));
        return redirect()->route('doctor.tickets.index');
    }   

    public function tickets_open($id)
    {
        $ticket = Tickets::find($id);
        $ticket->status = 'open';
        $ticket->save();
        toastr()->success(__('master.ticket_opened'));
        return redirect()->route('doctor.tickets.index');
    }   

    //calendar

    public function calendar()
    {
        
        return view('doctor.calendar.index');
    }

    public function calendar_events()
    {
        

    // Récupérer tous les événements de ces utilisateurs
    $events = Calendar::where('user_id', auth()->user()->id)->get();

        // Transformer les données pour FullCalendar
    $formatted = $events->map(function ($event) {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start,
            'end'   => $event->end,
            'event_url' => $event->event_url,
            'description' => $event->description,
            'color' => $event->color,
            'user_id' => $event->user_id,
        ];
    });
        return response()->json($formatted);
    }

    public function calendar_create()
    {
        return view('doctor.calendar.create');
    }
    

    public function calendar_store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);
        $calendar = Calendar::create([
            'title' => $request->title,
            'description' => $request->description,
            'start' => $request->start,
            'end' => $request->end,
            'color' => $request->color,
            'event_url' => $request->event_url,
            'user_id' => auth()->id(),
        ]);
        toastr()->success(__('master.calendar_created'));
        return redirect()->route('doctor.calendar.index');
    }   

    public function calendar_edit($id)
    {
        $calendar = Calendar::find($id);
        return view('doctor.calendar.edit', compact('calendar'));
    }

    public function calendar_delete($id)
    {
        $calendar = Calendar::find($id);
        $calendar->delete();
        toastr()->success(__('master.calendar_deleted'));
        return redirect()->route('doctor.calendar.index');
    }
    
    public function treatment_types_list(Request $request, $id)
    {
        
        if($request->ajax()){
            $treatment_types = TreatmentType::where('case_id', $id)->latest()->get();
            return DataTables::of($treatment_types)
            ->addIndexColumn()
            ->addColumn('name', function($row){
                return $row->name;
            })
            ->addColumn('type_file', function($row){
                if($row->type_file == 'pdf'){
                    return '<i class="icon-base ti tabler-file-text icon-md text-body-secondary"></i>';
                }elseif($row->type_file == 'link'){
                    return '<i class="icon-base ti tabler-link icon-md text-body-secondary"></i>';
                }
            })
            ->addColumn('status', function($row){
                if($row->status == 'pending'){
                    return '<span class="badge bg-label-warning">'.__('master.pending').'</span>';
                }elseif($row->status == 'accepted'){
                    return '<span class="badge bg-label-success">'.__('master.accepted').'</span>';
                }elseif($row->status == 'rejected'){
                    return '<span class="badge bg-label-danger">'.__('master.rejected').'</span>';
                }
                
            })
            ->addColumn('action', function($row){
                

                if($row->status == 'pending'){
                $button = '
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a href="'.route('doctor.treatment_types.accept', $row->id).'" class="dropdown-item waves-effect">'.__('master.accept').'</a></li>
                        <li><a href="'.route('doctor.treatment_types.show', $row->id).'" target="_blank" class="dropdown-item waves-effect">'.__('master.view').'</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#share-modal" data-link="'.$row->link.'" class="share-link dropdown-item waves-effect">'.__('master.share').'</a></li>
                        <li><a href="'.route('doctor.treatment_types.reject', $row->id).'" class="dropdown-item waves-effect text-danger">'.__('master.reject').'</a></li>
                        <li><a href="'.route('doctor.treatment_types.delete', $row->id).'" class="dropdown-item waves-effect text-danger"><i class="icon-base ti tabler-trash"></i> '.__('master.delete').'</a></li>
                    </ul>
                </div>';
                return $button;
            }
            elseif($row->status == 'accepted'){
                $button = '
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a href="'.route('doctor.treatment_types.show', $row->id).'" target="_blank" class="dropdown-item waves-effect">'.__('master.view').'</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#share-modal" data-link="'.$row->link.'" class="share-link dropdown-item waves-effect">'.__('master.share').'</a></li>
                    </ul>
                </div>';
                return $button;
            }   
            elseif($row->status == 'rejected'){
                $button = '
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a href="'.route('doctor.treatment_types.show', $row->id).'" target="_blank" class="dropdown-item waves-effect">'.__('master.view').'</a></li>
                    </ul>
                </div>';
                return $button;
            }
            })
            ->rawColumns(['action', 'status', 'name', 'type_file'])
            ->make(true);
        }
    }

   

    public function treatment_types_store(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'type_file' => 'required',
            'link' => 'required_if:type_file,link',
        ]);
       


        if($request->type_file == 'pdf'){
            $case = CasePatient::find($id);
            $file = $request->file('file'); 
            $googleDriveService = new GoogleDriveService();
            $uploadResult = $googleDriveService->uploadForUser($file, auth()->user(), null, "case_files", $case->case_id);
            $link = 'https://drive.google.com/file/d/'.$uploadResult['id'].'/view';
        }elseif($request->type_file == 'link'){
            $link = $request->link;
        }

        $treatment_type = TreatmentType::create([
            'name' => $request->name,
            'type_file' => $request->type_file,
            'status' => 'pending',
            'link' => $link,
            'case_id' => $id,
        ]);
       
        toastr()->success(__('master.treatment_type_created'));
        return redirect()->back();
    }

   
    public function treatment_types_accept($id)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->status = 'accepted';
        $treatment_type->save();

        $case = CasePatient::find($treatment_type->case_id);
        $case->status = 'in_production';
        $case->save();
        $notification = Notification::create([
            'case_id' => $case->id,
            'title' => $case->case_id.' - '. __('master.case_status_updated_to_in_production'),
            'message' => $case->case_id.' - '. __('master.case_affected_to_laboratory' ),
            'type' => 'case',
            'status' => 'case_in_production',
            'doctor_id' => $case->doctor_id,
            'technician_id' => $case->technician_id,
            'laboratory_id' => $case->laboratory_id,
        ]);
        Mail::to($case->technician->email)->send(new SendNotification($notification));
        Mail::to($case->laboratory->email)->send(new SendNotification($notification));

        toastr()->success(__('master.treatment_type_accepted'));
        return redirect()->back();
    }

    public function treatment_types_show($id)
    {
        $treatment_type = TreatmentType::find($id);
        return view('doctor.cases.treatment_request.show', compact('treatment_type'));
       
    }


    public function treatment_types_reject($id)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->status = 'rejected';
        $treatment_type->save();
        toastr()->success(__('master.treatment_type_rejected'));
        return redirect()->back();
    }

    public function treatment_types_delete($id)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->delete();
        toastr()->success(__('master.treatment_type_deleted'));
        return redirect()->back();
    }
    public function notifications_delete($id)
    {
        $notification = Notification::find($id);
        $notification->delete();
        if(auth()->user()->isDoctor()){
            $count = Notification::where('doctor_id', auth()->user()->id)->count();
        }elseif(auth()->user()->isTechnician()){
            $count = Notification::where('technician_id', auth()->user()->id)->count();
        }elseif(auth()->user()->isLaboratory()){
            $count = Notification::where('laboratory_id', auth()->user()->id)->count();
        }
        return response()->json(['count' => $count]);
    }


    public function technician_status($id)
    {
        $technician = User::find($id);
        if($technician->status == 'active'){
            $technician->status = 'inactive';
        }else{
            $technician->status = 'active';
        }
        $technician->save();
        toastr()->success(__('master.technician_status_changed'));
        return redirect()->back();
    }

    public function laboratory_status($id)
    {
        $laboratory = User::find($id);
        if($laboratory->status == 'active'){
            $laboratory->status = 'inactive';
        }else{
            $laboratory->status = 'active';
        }
        $laboratory->save();
        toastr()->success(__('master.laboratory_status_changed'));
        return redirect()->back();
    }


    public function google_drive()
    {
        return view('doctor.google.drive');
    }
    
}
