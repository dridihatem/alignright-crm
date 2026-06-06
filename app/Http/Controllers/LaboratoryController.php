<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Calendar;
use App\Models\CasePatient;
use App\Models\Tickets;
use App\Models\User;
use App\Models\Patient;
use App\Models\ToothProblem;
use App\Models\ToothProblemCase;
use App\Models\FileUpload;
use App\Mail\SendNotification;
use App\Models\Notification;  
use App\Models\Task;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Mail;
use Auth;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use App\Models\TreatmentType;
use Illuminate\Support\Facades\Log;
use Exception;

class LaboratoryController extends Controller
{
    public function index()
    {
        $cases = CasePatient::where('laboratory_id', auth()->user()->id)
            ->whereIn('status', ['in_production','shipped'])
            ->whereHas('weTransferNotifications')
            ->get();    
        $status_pending  = 0; // Not relevant for laboratory production workflow
        $status_draft = 0; // Not relevant for laboratory production workflow
        $status_in_planning = 0; // Not relevant for laboratory production workflow  
        $status_approval = 0; // Not relevant for laboratory production workflow
        $status_rejected = 0; // Not relevant for laboratory production workflow
        $status_in_production = CasePatient::where('status', 'in_production')
            ->where('laboratory_id', auth()->user()->id)
            ->whereHas('weTransferNotifications')
            ->count();
        $status_shipped = CasePatient::where('status', 'shipped')
            ->where('laboratory_id', auth()->user()->id)
            ->whereHas('weTransferNotifications')
            ->count();
        $count_cases = $cases->count();

        $case_retarded = 0; // No "pending" cases in laboratory production workflow
        $case_retarded_percentage = 0;
       
       $new_cases = CasePatient::where('laboratory_id', auth()->user()->id)
            ->whereIn('status', ['in_production','shipped'])
            ->whereHas('weTransferNotifications')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
       $count_patient = User::where('id', auth()->user()->id)->count();
      
      
       // Get all months with their totals for the last 30 days
       $cases_by_month = CasePatient::where('laboratory_id', auth()->user()->id)
           ->whereIn('status', ['in_production','shipped'])
           ->whereHas('weTransferNotifications')
           ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
           ->groupBy('month')
           ->pluck('total', 'month')
           ->toArray();

       // Create array with all months, defaulting to 0
       $monthly_totals = [];
       for ($i = 1; $i <= 12; $i++) {
           $monthly_totals[] = $cases_by_month[$i] ?? 0;
       }

        return view('laboratory.dashboard', compact('cases', 'status_pending', 'status_draft', 'status_in_planning', 'status_approval', 'status_rejected', 'status_in_production', 'status_shipped', 'count_cases', 'case_retarded', 'case_retarded_percentage', 'new_cases', 'count_patient', 'monthly_totals'));
    }
     
    public function latest_cases(Request $request)
    {
        if($request->ajax()){
            $query = CasePatient::query()
                ->where('laboratory_id', auth()->user()->id)
                ->whereIn('status', ['in_production','shipped'])
                ->whereHas('weTransferNotifications')
                ->with(['patient', 'weTransferNotifications']);

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
                    return '<a href="'.route('laboratory.cases.show', $row->id).'">'.$row->case_id.'</a>';
                })
                ->addColumn('status', function($row){
                    $status = $row->status;
                    $msg = '';
                    if(now()->diffInDays($row->accepted_date) > 2 && $status == 'approval'){
                            $msg = '<a href="'.route('laboratory.cases.updateStatus', ['id' => $row->id, 'status' => 'in_planning']).'" class="badge bg-label-primary"><i class="icon-base ti tabler-clock"></i> '. __('master.change_in_planning').'</a>';
                    }
                    else{
                        $msg = __('master.waiting_for_change_in_planning');
                    }
                    if($status == 'pending'){
                        return '<span class="badge bg-label-warning">'.__('master.pending').'</span>';
                    }elseif($status == 'draft'){
                        return '<span class="badge bg-label-secondary">'.__('master.draft').'</span>';
                    }elseif($status == 'in_planning'){
                        return '<span class="badge bg-label-info"><i class="icon-base ti tabler-clock"></i> '.__('master.in_planning_for_technician').'</span>';
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
                ->addColumn('laboratory_id', function($row){
                    if($row->laboratory){
                        if($row->laboratory->photo != null){
                        return 
                        '<ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" aria-label="'. $row->laboratory->name.'" data-bs-original-title="'. $row->laboratory->name.'">
                              <img src="'.$row->laboratory->photo_url.'" alt="Avatar" class="rounded-circle">
                            </li>
                            
                          </ul>'
                       ;
                        }else{
                                return '<span class="badge bg-label-primary">'. $row->laboratory->name.'</span>';
                        }
                    }
                    return '<span class="badge bg-label-danger">'.__('master.no_laboratory').'</span>';
                })
             
               
                ->addColumn('accepted_date', function($row){
                    return $row->accepted_date;
                })
                ->addColumn('rejected_date', function($row){
                    return $row->rejected_date;
                })
                ->addColumn('wetransfer_info', function($row){
                    $weTransfer = $row->latestWeTransferNotification;
                    if($weTransfer) {
                        return '<a href="'.$weTransfer->wetransfer_link.'" target="_blank" class="badge bg-label-success"><i class="icon-base ti tabler-download"></i> WeTransfer</a><br><small class="text-muted">From: '.$weTransfer->technician->name.'</small>';
                    }
                    return '<span class="badge bg-label-secondary">No WeTransfer</span>';
                })
                ->addColumn('action', function($row){
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
                ->rawColumns(['action', 'status', 'case_id', 'wetransfer_info'])
                ->make(true);
        }
    }
   

    public function cases()
    {
        $patients = CasePatient::select('patient_id','case_id','doctor_id','id')
            ->where('laboratory_id', auth()->user()->id)
            ->whereIn('status', ['in_production','shipped'])
            ->whereHas('weTransferNotifications')
            ->whereNotNull('patient_id')
            ->groupBy('patient_id')
            ->get();

        return view('laboratory.cases.index', compact('patients'));
    }

    public function case_show($id)
    {

        $case = CasePatient::where('id', $id)
            ->where('laboratory_id', auth()->user()->id)
            ->with(['latestWeTransferNotification.technician'])
            ->first();
        $toothProblemscase = ToothProblemCase::where('case_id', $id)->with('tooth_problem')->get();
        $comments = Comment::where('case_id', $id)->latest()->get();
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
        return view('laboratory.cases.show', compact('case', 'toothProblemscase', 'comments', 'treatmentTypes', 'files_clinical', 'files_radiographs', 'other_files', 'count_stl_files', 'count_clinical_files', 'count_radiograph_files', 'count_other_files'));
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
            'laboratory_id' => $case->laboratory_id,
        ]);
        toastr()->success(__('master.case_status_updated'));
        return redirect()->back();
    }



    public function add_comment(Request $request)
    {
        try {
            $request->validate([
                'comment' => 'required|string|max:1000',
                'case_id' => 'required|exists:case_patients,id'
            ]);

            // Verify the case belongs to the authenticated laboratory
            $case = CasePatient::where('id', $request->case_id)
                              ->where('laboratory_id', auth()->user()->id)
                              ->first();

            if (!$case) {
                return response()->json(['error' => 'Unauthorized access to case'], 403);
            }

            $comment = Comment::create([
                'case_id' => $request->case_id,
                'comment' => $request->comment,
                'user_id' => auth()->user()->id,
                'type' => 'laboratory_update'
            ]);

            // Load user relationship
            $comment->load('user');

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
            return response()->json(['error' => 'Failed to add comment'], 500);
        }
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
        return view('laboratory.tickets.index', compact('tickets', 'ticket_replies', 'ticket_replies_list'));
    }



    public function tickets_create()
    {
        return view('laboratory.tickets.create');
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
            'laboratory_id' => $request->assigned_to,
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
            'laboratory_id' => $assigned_to[1] == 'laboratoire' ? $assigned_to[0] : null,
        ]);
        toastr()->success(__('master.ticket_created'));
        return redirect()->route('laboratory.tickets.index');
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
        return view('laboratory.tickets.edit', compact('ticket'));
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
        return redirect()->route('laboratory.tickets.index');
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
        return redirect()->route('laboratory.tickets.index');
    }


    public function tickets_status($id, $status)
    {
        $ticket = Tickets::find($id);
        $ticket->status = $status;
        $ticket->save();
        return redirect()->route('laboratory.tickets.index');
    }   

    public function tickets_priority($id, $priority)
    {
        $ticket = Tickets::find($id);
        $ticket->priority = $priority;
        $ticket->save();
        return redirect()->route('laboratory.tickets.index');
    }   

    public function tickets_close($id)
    {
        $ticket = Tickets::find($id);
        $ticket->status = 'closed';
        $ticket->save();
        toastr()->success(__('master.ticket_closed'));
        return redirect()->route('laboratory.tickets.index');
    }   

    public function tickets_open($id)
    {
        $ticket = Tickets::find($id);
        $ticket->status = 'open';
        $ticket->save();
        toastr()->success(__('master.ticket_opened'));
        return redirect()->route('laboratory.tickets.index');
    }   

    //calendar

    public function calendar()
    {
        
        return view('laboratory.calendar.index');
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
        return view('laboratory.calendar.create');
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
        return redirect()->route('laboratory.calendar.index');
    }   

    public function calendar_edit($id)
    {
        $calendar = Calendar::find($id);
        return view('laboratory.calendar.edit', compact('calendar'));
    }

    public function calendar_delete($id)
    {
        $calendar = Calendar::find($id);
        $calendar->delete();
        toastr()->success(__('master.calendar_deleted'));
        return redirect()->route('laboratory.calendar.index');
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
                

                $button = '
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a href="'.route('laboratory.treatment_types.show', $row->id).'" target="_blank" class="dropdown-item waves-effect">'.__('master.view').'</a></li>
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
        $request->validate([
            'name' => 'required',
            'type_file' => 'required',
            'link' => 'required_if:type_file,link',
        ]);
       


        if($request->type_file == 'pdf'){
          
            $file = $request->file('file');
            $filename = time().'-'.$file->getClientOriginalName();
            $caseFolder = 'case_files/'.$id;
            
            // Store file using public disk
            $path = $file->storeAs($caseFolder, $filename, 'public');
            $link = Storage::disk('public')->url($path);
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
        $case = CasePatient::find($id);
       $notification = Notification::create([
        'title' => $case->case_id.' - '. __('master.treatment_type_created'),
        'message' => $case->case_id.' - '. __('master.treatment_type_created'),
        'type' => 'treatment_type',
        'status' => 'active',
        'case_id' => $id,
        'doctor_id' => $case->doctor_id,
        'laboratory_id' => $case->laboratory_id,
       ]);
       //Mail::to($case->technician->email)->send(new NotificationMail($notification));
       Mail::to($case->doctor->email)->send(new NotificationMail($notification));

        toastr()->success(__('master.treatment_type_created'));
        return redirect()->back();
    }

   
    public function treatment_types_accept($id)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->status = 'accepted';
        $treatment_type->save();
        
        toastr()->success(__('master.treatment_type_accepted'));
        return redirect()->back();
    }

    public function treatment_types_show($id)
    {
        $treatment_type = TreatmentType::find($id);
        return view('laboratory.cases.treatment_request.show', compact('treatment_type'));
       
    }


    public function treatment_types_reject($id)
    {
        $treatment_type = TreatmentType::find($id);
        $treatment_type->status = 'rejected';
        $treatment_type->save();
        toastr()->success(__('master.treatment_type_rejected'));
        return redirect()->back();
    }
}
