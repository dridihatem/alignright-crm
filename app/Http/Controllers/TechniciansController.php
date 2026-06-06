<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TechnicianService;
use Illuminate\Http\Request;
use App\Models\Calendar;
use App\Models\CasePatient;
use App\Models\Comment;
use App\Models\Tickets;
use App\Models\User;
use App\Models\Notification;  
use App\Models\Task;
use App\Models\TreatmentType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\SendNotification;
use App\Models\ToothProblemCase;
use App\Http\Controllers\Concerns\GroupsCasesByPatient;

class TechniciansController extends Controller
{
    use GroupsCasesByPatient;

    protected $technicianService;

    public function __construct(TechnicianService $technicianService)
    {
        $this->technicianService = $technicianService;
    }

    public function index()
    {
       
        $cases = CasePatient::with('patient')->where('technician_id', auth()->user()->id)->get();    
        $status_pending  = CasePatient::where('status', 'pending')->where('technician_id', auth()->user()->id)->count();
        $status_draft = CasePatient::where('status', 'draft')->where('technician_id', auth()->user()->id)->count();
        $status_in_planning = CasePatient::where('status', 'in_planning')->where('technician_id', auth()->user()->id)->count();
        $status_approval = CasePatient::where('status', 'approval')->where('technician_id', auth()->user()->id)->count();
        $status_rejected = CasePatient::where('status', 'rejected')->where('technician_id', auth()->user()->id)->count();
        $status_in_production = CasePatient::where('status', 'in_production')->where('technician_id', auth()->user()->id)->count();
        $status_shipped = CasePatient::where('status', 'shipped')->where('technician_id', auth()->user()->id)->count();
        $count_cases = $cases->where('technician_id', auth()->user()->id)->count();

        $case_retarded = CasePatient::where('status', 'pending')->where('technician_id', auth()->user()->id)->count();
       if($count_cases > 0){
        $case_retarded_percentage = number_format(($case_retarded / $count_cases) * 100, 2);
       }else{
        $case_retarded_percentage = 0;
       }
       $new_cases = CasePatient::where('technician_id', auth()->user()->id)->where('status','in_production')->where('created_at', '>=', now()->subDays(30))->count();
       $count_patient = User::where('id', auth()->user()->id)->count();
      
      
       // Get all months with their totals for the last 30 days
       $cases_by_month = CasePatient::where('technician_id', auth()->user()->id)
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

        $patientGroups = $this->buildPatientGroups($cases, ['show' => 'technician.cases.show']);

        return view('technician.dashboard', compact('cases', 'status_pending', 'status_draft', 'status_in_planning', 'status_approval', 'status_rejected', 'status_in_production', 'status_shipped', 'count_cases', 'case_retarded', 'case_retarded_percentage', 'new_cases', 'count_patient', 'monthly_totals', 'patientGroups'));
    }
     
    public function latest_cases(Request $request)
    {
        if($request->ajax()){
            $query = CasePatient::query()
                ->where('technician_id', auth()->user()->id)
                ->whereIn('status', ['pending', 'in_planning','in_production', 'approval','rejected'])
                ->with(['patient']);

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
                    return '<a href="'.route('technician.cases.show', $row->id).'">'.$row->case_id.'</a>';
                })
                ->addColumn('status', function($row){
                    $status = $row->status;
                    $msg = '';
                    if(now()->diffInDays($row->accepted_date) > 2 && $status == 'approval'){
                        $msg = '<a href="'.route('technician.cases.updateStatus', ['id' => $row->id, 'status' => 'in_planning']).'" class="badge bg-label-primary"><i class="icon-base ti tabler-clock"></i> '. __('master.change_in_planning').'</a>';
                    }
                    else{
                        $msg = __('master.waiting_for_change_in_planning') . ' <br> <a href="'.route('technician.cases.updateStatus', ['id' => $row->id, 'status' => 'in_planning']).'" class="badge bg-label-primary"><i class="icon-base ti tabler-clock"></i> '. __('master.change_in_planning').'</a>';
                    }
                    if($status == 'pending'){
                        return '<span class="badge bg-label-warning">'.__('master.pending').'</span>';
                    }elseif($status == 'draft'){
                        return '<span class="badge bg-label-secondary">'.__('master.draft').'</span>';
                    }elseif($status == 'in_planning'){
                        return '<span class="badge bg-label-info"><i class="icon-base ti tabler-clock"></i> '.__('master.in_planning').'</span>';
                    }elseif($status == 'approval'){
                        return '<span class="badge bg-label-success">'.__('master.approval').'</span> <br><small class="text-body-secondary">'.$msg.'</small>';
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
                ->addColumn('doctor_id', function($row){
                    if($row->doctor){
                        return $row->doctor->name;
                    }
                    return __('master.no_doctor');
                })
                ->addColumn('patient_id', function($row){
                    if($row->patient){
                        return $row->patient->name;
                    }
                    return __('master.no_patient');
                }) 
                ->addColumn('technician_id', function($row){
                    if($row->technician){
                        if($row->technician->photo != null){
                        return 
                        '<ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="'. $row->technician->name.'" data-bs-original-title="'. $row->technician->name.'">
                              <img src="'.$row->technician->photo_url.'" alt="Avatar" class="rounded-circle">
                            </li>
                            
                          </ul>'
                       ;
                        }else{
                            return '<span class="badge bg-label-primary">'. $row->technician->name.'</span>';
                        }
                    }
                    return '<span class="badge bg-label-danger">'.__('master.no_technician').'</span>';
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
                           <li><a class="dropdown-item waves-effect" href="'.route('technician.cases.show', $row->id).'">'.__('master.view').'</a></li>
                       
                        </ul>
                    </div>';    
                    return $button;
                })
                ->rawColumns(['action', 'status', 'case_id'])
                ->make(true);
        }
    }
   

    public function cases()
    {
        $patients = CasePatient::select('patient_id','case_id','doctor_id','id')->where('technician_id', auth()->user()->id)->whereNotNull('patient_id')->groupBy('patient_id')->get();

        return view('technician.cases.index', compact('patients'));
    }

    public function case_show($id)
    {
        $case = CasePatient::where('id', $id)->where('technician_id', auth()->user()->id)->first();
        
        if (!$case) {
            abort(404, 'Case not found or you do not have access to this case');
        }

        // Get case data
        $toothProblemscase = ToothProblemCase::where('case_id', $id)->with('tooth_problem')->get();
        $comments = Comment::where('case_id', $id)->latest()->get();
        
        // Get treatment types for this case
        $treatmentTypes = TreatmentType::where('case_id', $id)->latest()->get();
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

        return view('technician.cases.show', compact('case', 'toothProblemscase', 'comments', 'treatmentTypes', 'files_clinical', 'files_radiographs', 'other_files', 'count_stl_files', 'count_clinical_files', 'count_radiograph_files', 'count_other_files'));
    }



    public function updateStatus($id, $status)
    {
        $case = CasePatient::find($id);
        $case->status = $status;
        if($status == 'approval'){
            $case->accepted_date = now();

        }elseif($status == 'rejected'){
            $case->rejected_date = now();
        }
        $case->save();
        $notification = Notification::create([
            'case_id' => $case->id,
            'title' => $case->case_id.' - '. __('master.case_status_updated'),
            'message' => $case->case_id.' - '. __('master.case_status_updated'),
            'type' => 'case',
            'status' => $status,
            'doctor_id' => $case->doctor_id,
            'technician_id' => $case->technician_id,
        ]);
        toastr()->success(__('master.case_status_updated'));
        return redirect()->back();
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
                'user_photo' => auth()->user()->photo != null ? auth()->user()->photo : asset('assets/img/avatars/default.png'),
            ]);
    }   

    public function get_comments($id)
    {
        $comments = Comment::where('case_id', $id)->latest()->get();

        return response()->json($comments);
    }


    //tickets
    public function tickets()
    {
        $tickets = Tickets::where('assigned_to', auth()->user()->id)->with('user')->where('ticket_id', null)->latest()->paginate(10);  
        $ticket_replies = Tickets::where('ticket_id', '!=', null)->where('assigned_to', auth()->user()->id)->with('user')->count();
        $ticket_replies_list = Tickets::where('ticket_id', '!=', null)->where('assigned_to', auth()->user()->id)->with('user')->latest()->paginate(10);
        return view('technician.tickets.index', compact('tickets', 'ticket_replies', 'ticket_replies_list'));
    }



    public function tickets_create()
    {
        return view('technician.tickets.create');
    }
 

    public function tickets_reply(Request $request, $id)
    {
        $ticket = Tickets::create([
            'message' => $request->reply_message,
            'user_id' => auth()->user()->id,
            'ticket_id' => $id,
            'status' => 'open',
            'assigned_to' => null,
        ]);
        $notification = Notification::create([
            'title' => 'New Ticket Reply',
            'message' => 'New ticket reply has been created by '.auth()->user()->name.'.',
            'type' => 'ticket reply',
            'status' => 'active',
            'ticket_id' => $id,
            'doctor_id' => auth()->user()->id,
            'technician_id' => $request->assigned_to,
        ]);
        toastr()->success(__('master.ticket_reply_sent'));
        return redirect()->back();
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
        return view('technician.tickets.edit', compact('ticket'));
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
        return redirect()->route('technician.tickets.index');
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
        return redirect()->route('technician.tickets.index');
    }


    public function tickets_status($id, $status)
    {
        $ticket = Tickets::find($id);
        $ticket->status = $status;
        $ticket->save();
        return redirect()->route('technician.tickets.index');
    }   

    public function tickets_priority($id, $priority)
    {
        $ticket = Tickets::find($id);
        $ticket->priority = $priority;
        $ticket->save();
        return redirect()->route('technician.tickets.index');
    }   

    public function tickets_close($id)
    {
        $ticket = Tickets::find($id);
        $ticket->status = 'closed';
        $ticket->save();
        toastr()->success(__('master.ticket_closed'));
        return redirect()->route('technician.tickets.index');
    }   

    public function tickets_open($id)
    {
        $ticket = Tickets::find($id);
        $ticket->status = 'open';
        $ticket->save();
        toastr()->success(__('master.ticket_opened'));
        return redirect()->route('technician.tickets.index');
    }   

    //calendar

    public function calendar()
    {
        
        return view('technician.calendar.index');
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
        return view('technician.calendar.create');
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
        return redirect()->route('technician.calendar.index');
    }   

    public function calendar_edit($id)
    {
        $calendar = Calendar::find($id);
        return view('technician.calendar.edit', compact('calendar'));
    }

    public function calendar_delete($id)
    {
        $calendar = Calendar::find($id);
        $calendar->delete();
        toastr()->success(__('master.calendar_deleted'));
        return redirect()->route('technician.calendar.index');
    }

    
    public function treatment_types_list(Request $request, $id)
    {
        
        if($request->ajax()){
            $treatment_types = TreatmentType::where('case_id', $id)->latest()->get();
            return DataTables::of($treatment_types)
            ->addIndexColumn()
            ->addColumn('name', function($row){
                return $row->description ?? 'Treatment Plan';
            })
            ->addColumn('type_file', function($row){
                if($row->irp_file){
                    return '<i class="icon-base ti tabler-file-type-pdf icon-md text-body-secondary"></i>';
                }elseif($row->link_viewer){
                    return '<i class="icon-base ti tabler-link icon-md text-body-secondary"></i>';
                }
                return '<i class="icon-base ti tabler-file icon-md text-body-secondary"></i>';
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
                

                $button = '
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a href="'.route('technician.treatment_types.show', $row->id).'" target="_blank" class="dropdown-item waves-effect">'.__('master.view').'</a></li>
                    </ul>
                </div>';
                return $button;
           
            })
            ->rawColumns(['action', 'status', 'name', 'type_file'])
            ->make(true);
        }
    }

   

    public function treatment_types_store(Request $request, $id)
    {
        try {
            Log::info('Treatment type store request received', [
                'case_id' => $id,
                'user_id' => auth()->id(),
                'has_irp_file' => $request->hasFile('irp_file'),
                'has_link_viewer' => $request->filled('link_viewer'),
                'description' => $request->filled('description'),
                'all_files' => $request->allFiles(),
                'file_info' => $request->hasFile('irp_file') ? [
                    'name' => $request->file('irp_file')->getClientOriginalName(),
                    'size' => $request->file('irp_file')->getSize(),
                    'mime' => $request->file('irp_file')->getMimeType(),
                    'extension' => $request->file('irp_file')->getClientOriginalExtension(),
                    'is_valid' => $request->file('irp_file')->isValid(),
                    'error' => $request->file('irp_file')->getError(),
                    'error_message' => $request->file('irp_file')->getErrorMessage()
                ] : null
            ]);

            // Validate request data
            $validator = \Validator::make($request->all(), [
                'description' => 'nullable|string|max:1000',
                'irp_file' => 'nullable|file|mimes:pdf|max:20480', // Increased to 20MB
                'link_viewer' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed for treatment type store', [
                    'errors' => $validator->errors()->toArray(),
                    'input' => $request->all()
                ]);
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }
           
            $irp_file_path = null;
            $link_viewer = $request->link_viewer;

            // Handle IRP file upload
            if($request->hasFile('irp_file')){
                $file = $request->file('irp_file');
                
                Log::info('Processing IRP file upload', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_mime' => $file->getMimeType(),
                    'is_valid' => $file->isValid(),
                    'error_code' => $file->getError(),
                    'error_message' => $file->getErrorMessage(),
                    'temp_path' => $file->getPathname(),
                    'temp_exists' => file_exists($file->getPathname()),
                    'temp_readable' => is_readable($file->getPathname()),
                    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
                    'sys_temp_dir' => sys_get_temp_dir()
                ]);
                
                // Validate file
                if (!$file->isValid()) {
                    $errorMessage = 'Invalid file upload: ' . $file->getErrorMessage();
                    Log::error('File upload validation failed', [
                        'error_code' => $file->getError(),
                        'error_message' => $file->getErrorMessage(),
                        'file_name' => $file->getClientOriginalName()
                    ]);
                    throw new \Exception($errorMessage);
                }
                
                // Check file size (10MB limit)
                if ($file->getSize() > 10 * 1024 * 1024) {
                    throw new \Exception('File too large. Maximum size is 10MB.');
                }
                
                // Check file type
                if ($file->getMimeType() !== 'application/pdf') {
                    throw new \Exception('Only PDF files are allowed.');
                }
                
                $filename = time().'-'.$file->getClientOriginalName();
               
                $caseFolder = "case_files/{$id}/treatment_plans";
                
                // Create directory if it doesn't exist
                if (!Storage::disk('public')->exists($caseFolder)) {
                    Storage::disk('public')->makeDirectory($caseFolder);
                }
                
                Log::info('Attempting to store file', [
                    'case_folder' => $caseFolder,
                    'filename' => $filename,
                    'storage_disk' => 'public'
                ]);
                
                // Store file using public disk with fallback
                try {
            $path = $file->storeAs($caseFolder, $filename, 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store file on disk.');
                    }
                } catch (\Exception $e) {
                    Log::error('File storage failed, trying alternative method', [
                        'error' => $e->getMessage(),
                        'case_folder' => $caseFolder,
                        'filename' => $filename
                    ]);
                    
                    // Try alternative storage method
                    $alternativePath = $caseFolder . '/' . $filename;
                    $fullPath = public_path('storage/' . $alternativePath);
                    
                    // Ensure directory exists
                    $dir = dirname($fullPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    
                    // Move file manually
                    if (!move_uploaded_file($file->getPathname(), $fullPath)) {
                        throw new \Exception('Failed to store file using alternative method.');
                    }
                    
                    $path = $alternativePath;
                }
                
                $irp_file_path = Storage::disk('public')->url($path);
                
                Log::info('IRP file uploaded successfully', [
                    'case_id' => $id,
                    'file_path' => $path,
                    'public_url' => $irp_file_path,
                    'file_size' => $file->getSize()
                ]);
        }

        $treatment_type = TreatmentType::create([
                'irp_file' => $irp_file_path,
                'link_viewer' => $link_viewer,
                'description' => $request->description,
            'status' => 'pending',
            'case_id' => $id,
                'uploaded_by' => auth()->user()->id,
                'treatment_plan_uploaded_at' => now(),
        ]);
        $case = CasePatient::find($id);
       $notification = Notification::create([
        'title' => $case->case_id.' - '. __('master.treatment_type_created'),
        'message' => $case->case_id.' - '. __('master.treatment_type_created'),
        'type' => 'treatment_type',
        'status' => 'active',
        'case_id' => $id,
        'doctor_id' => $case->doctor_id,
        'technician_id' => $case->technician_id,
       ]);
       //Mail::to($case->technician->email)->send(new SendNotification($notification));
       Mail::to($case->doctor->email)->send(new SendNotification($notification));
       $admins = \App\Models\User::where('role_id', 1)->get();
       foreach ($admins as $admin) {
        $notification = Notification::create([
            'user_id' => $admin->id,
            'title' => 'Treatment Plan Added - ' . $case->case_id,
            'message' => "Treatment plan for case {$case->case_id} has been added. Please add price to proceed.",
                    'type' => 'treatment_plan_accepted',
            'data' => json_encode(['treatment_plan_id' => $treatment_type->id, 'case_id' => $case->id])
        ]);
        Mail::to($admin->email)->send(new SendNotification($notification));
    }

            // Check if this is an AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('master.treatment_type_created'),
                    'treatment_type_id' => $treatment_type->id
                ]);
            }

        toastr()->success(__('master.treatment_type_created'));
        return redirect()->back();
            
        } catch (\Exception $e) {
            Log::error('Error creating treatment type', [
                'error' => $e->getMessage(),
                'case_id' => $id,
                'user_id' => auth()->id()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating treatment type: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error creating treatment type: ' . $e->getMessage());
        }
    }

   
    public function treatment_types_accept($id)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->status = 'accepted';
        $treatment_type->save();
        $case = CasePatient::find($treatment_type->case_id);
        $case->status = 'in_production';
        $case->save();
        Mail::to($case->technician->email)->send(new SendNotification($treatment_type));
        Mail::to($case->doctor->email)->send(new SendNotification($treatment_type));
        $admins = \App\Models\User::where('role_id', 1)->get();
        foreach ($admins as $admin) {
            $notification = Notification::create([
                'user_id' => $admin->id,
                'title' => 'Treatment Plan Accepted - ' . $case->case_id,
                'message' => "Treatment plan for case {$case->case_id} has been accepted.",
                'type' => 'treatment_plan_accepted',
                'data' => json_encode(['treatment_plan_id' => $treatment_type->id, 'case_id' => $case->id])
            ]);
            Mail::to($admin->email)->send(new SendNotification($notification));
        }
        
        toastr()->success(__('master.treatment_type_accepted'));
        return redirect()->back();
    }

    public function treatment_types_show($id)
    {
        $treatment_type = TreatmentType::find($id);
        return view('technician.cases.treatment_request.show', compact('treatment_type'));
       
    }


    public function treatment_types_reject($id, Request $request)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->status = 'rejected';
        $treatment_type->rejection_reason=$request->rejection_reason;
        $treatment_type->save();
        
        $case = CasePatient::find($treatment_type->case_id);
        
        Mail::to($case->technician->email)->send(new SendNotification($treatment_type));
        Mail::to($case->doctor->email)->send(new SendNotification($treatment_type));
        $admins = \App\Models\User::where('role_id', 1)->get();
        foreach ($admins as $admin) {
            $notification = Notification::create([
                'user_id' => $admin->id,
                'title' => 'Treatment Plan Rejected - ' . $case->case_id,
                'message' => "Treatment plan for case {$case->case_id} has been rejected. Reason: " . ($treatment_type->rejection_reason ?? 'No reason provided'),
                'type' => 'treatment_plan_rejected',
                'data' => json_encode(['treatment_plan_id' => $treatment_type->id, 'case_id' => $case->id])
            ]);
            Mail::to($admin->email)->send(new SendNotification($notification));
        }
        toastr()->success(__('master.treatment_type_rejected'));
        return redirect()->back();
    }

    /**
     * Update estimated completion time for treatment type
     */
    public function updateEstimatedCompletion(Request $request, $id)
    {
        $request->validate([
            'estimated_completion_date' => 'required|date|after_or_equal:today'
        ]);

        $treatmentType = TreatmentType::findOrFail($id);
        
        // Check if technician has access to this treatment type
        $case = CasePatient::where('id', $treatmentType->case_id)
                          ->where('technician_id', auth()->user()->id)
                          ->first();
        
        if (!$case) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $treatmentType->update([
            'estimated_completion_date' => $request->estimated_completion_date
        ]);

        return response()->json(['success' => true, 'message' => 'Estimated completion date updated successfully']);
    }

    /**
     * Complete treatment type and add WeTransfer link
     */
    public function completeTreatmentType(Request $request, $id)
    {
        $request->validate([
            'wetransfer_link' => 'required|url',
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        $treatmentType = TreatmentType::findOrFail($id);
        
        // Check if technician has access to this treatment type
        $case = CasePatient::where('id', $treatmentType->case_id)
                          ->where('technician_id', auth()->user()->id)
                          ->first();
        
        if (!$case) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $treatmentType->update([
            'wetransfer_link' => $request->wetransfer_link,
            'status' => 'completed',
            'treatment_plan_uploaded_at' => now(),
            'uploaded_by' => auth()->user()->id
        ]);

        // Add completion comment if provided
        if ($request->completion_notes) {
            Comment::create([
                'case_id' => $case->id,
                'user_id' => auth()->user()->id,
                'content' => 'Treatment completed: ' . $request->completion_notes,
                'type' => 'treatment_completion'
            ]);
        }

        // Send notification to laboratory
        if ($case->laboratory_id) {
            Notification::create([
                'title' => 'Treatment Completed',
                'message' => 'Treatment type "' . ($treatmentType->description ?? 'Treatment Plan') . '" has been completed and is ready for laboratory review.',
                'type' => 'treatment_completion',
                'status' => 'pending',
                'case_id' => $case->id,
                'technician_id' => auth()->user()->id,
                'laboratory_id' => $case->laboratory_id,
                'treatment_type_id' => $treatmentType->id
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Treatment type completed successfully']);
    }

    /**
     * Accept treatment type (start working on it)
     */
    public function acceptTreatmentType($id)
    {
        $treatmentType = TreatmentType::findOrFail($id);
        
        // Check if technician has access to this treatment type
        $case = CasePatient::where('id', $treatmentType->case_id)
                          ->where('technician_id', auth()->user()->id)
                          ->first();
        
        if (!$case) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $treatmentType->update([
            'status' => 'in_progress',
            'accepted_by' => auth()->user()->id,
            'accepted_at' => now()
        ]);

        // Update case status to in_production if not already
        if ($case->status !== 'in_production') {
            $case->update(['status' => 'in_production']);
        }

        toastr()->success(__('master.treatment_type_accepted'));
        return redirect()->back();
    }

    /**
     * Add comment to case
     */
    public function case_update(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'estimated_completion_date' => 'nullable|date|after_or_equal:today'
        ]);

        $case = CasePatient::where('id', $id)->where('technician_id', auth()->user()->id)->first();
        
        if (!$case) {
            return redirect()->back()->with('error', 'Case not found or unauthorized access');
        }

        // Add comment
        Comment::create([
            'case_id' => $id,
            'user_id' => auth()->user()->id,
            'content' => $request->comment,
            'type' => 'technician_update'
        ]);

        // Update estimated completion date if provided
        if ($request->estimated_completion_date) {
            $case->update(['estimated_completion_date' => $request->estimated_completion_date]);
        }

        // Send notification to doctor
        Notification::create([
            'title' => 'Case Update from Technician',
            'message' => 'Technician ' . auth()->user()->name . ' has added an update to case ' . $case->case_id,
            'type' => 'case_update',
            'status' => 'pending',
            'case_id' => $id,
            'technician_id' => auth()->user()->id,
            'doctor_id' => $case->doctor_id
        ]);

        toastr()->success(__('master.case_updated_successfully'));
        return redirect()->back();
    }

    /**
     * Remove file from treatment type
     */
    public function removeTreatmentFile($id)
    {
        try {
            $treatmentType = TreatmentType::findOrFail($id);
            
            // Check if user is authorized (technicians and admins can remove files)
            $user = auth()->user();
            $userRole = strtolower($user->role->name ?? '');
            
            Log::info('Remove file authorization check', [
                'user_id' => $user->id,
                'user_role' => $user->role->name,
                'user_role_lower' => $userRole,
                'treatment_type_id' => $id
            ]);
            
            if (!in_array($userRole, ['technician', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('master.unauthorized_action') . ' - Role: ' . $user->role->name
                ], 403);
            }

            // Check if treatment type has a file
            if (!$treatmentType->irp_file) {
                return response()->json([
                    'success' => false,
                    'message' => __('master.no_file_to_remove')
                ], 404);
            }

            $fileName = basename($treatmentType->irp_file);
            $filePath = $treatmentType->irp_file;

            // If it's a physical file (not an external link), try to delete it from storage
            if (!filter_var($filePath, FILTER_VALIDATE_URL)) {
                // Extract relative path from URL if it's a storage URL
                $relativePath = str_replace(asset('storage/'), '', $filePath);
                
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                    Log::info('Physical file deleted from storage', [
                        'treatment_type_id' => $id,
                        'file_path' => $relativePath
                    ]);
                }
            }

            // Delete the entire treatment type record
            $treatmentType->delete();
            
            

            Log::info('Treatment file removed', [
                'treatment_type_id' => $id,
                'treatment_name' => $fileName,
                'removed_by' => auth()->id(),
                'file_path' => $filePath
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.file_removed_successfully')
            ]);

        } catch (ModelNotFoundException $e) {
            Log::error('Treatment type not found for file removal', [
                'treatment_type_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('master.treatment_type_not_found')
            ], 404);

        } catch (Exception $e) {
            Log::error('Error removing treatment file', [
                'treatment_type_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('master.error_removing_file') . ': ' . $e->getMessage()
            ], 500);
        }
    }
    
}
