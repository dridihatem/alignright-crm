<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\CrmContact;
use App\Models\CrmInteraction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class CrmController extends Controller
{
    public function index()
    {
        return view('technician.crm.index');
    }

    public function getContacts(Request $request)
    {
        if ($request->ajax()) {
            $query = CrmContact::with(['creator', 'assignedUser', 'interactions'])
                ->where('created_by', Auth::id())
                ->orWhere('assigned_to', Auth::id());

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $badgeClass = $row->status_badge;
                    return '<span class="badge bg-label-' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('source_badge', function ($row) {
                    $badgeClass = $row->source_badge;
                    return '<span class="badge bg-label-' . $badgeClass . '">' . ucfirst(str_replace('_', ' ', $row->source)) . '</span>';
                })
                ->addColumn('assigned_user', function ($row) {
                    return $row->assignedUser ? $row->assignedUser->name : 'Unassigned';
                })
                ->addColumn('interactions_count', function ($row) {
                    return $row->interactions->count();
                })
                ->addColumn('last_interaction', function ($row) {
                    $lastInteraction = $row->interactions()->latest()->first();
                    return $lastInteraction ? $lastInteraction->created_at->format('M d, Y') : 'Never';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="' . route('technician.crm.contacts.show', $row->id) . '">
                                <i class="ti ti-eye me-1"></i> View
                            </a>
                            <a class="dropdown-item" href="' . route('technician.crm.contacts.edit', $row->id) . '">
                                <i class="ti ti-pencil me-1"></i> Edit
                            </a>
                            <a class="dropdown-item" href="' . route('technician.crm.contacts.interactions', $row->id) . '">
                                <i class="ti ti-message-circle me-1"></i> Interactions
                            </a>
                        </div>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'source_badge', 'action'])
                ->make(true);
        }
    }

    public function create()
    {
        $users = User::whereIn('role_id', [3, 4])->get(); // Technicians and Laboratories
        return view('technician.crm.contacts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,prospect,customer',
            'source' => 'required|in:website,referral,cold_call,email,social_media,other',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        CrmContact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'position' => $request->position,
            'notes' => $request->notes,
            'status' => $request->status,
            'source' => $request->source,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
        ]);

        return redirect()->route('technician.crm.index')->with('success', 'Contact created successfully.');
    }

    public function show($id)
    {
        $contact = CrmContact::with(['creator', 'assignedUser', 'interactions.user'])->findOrFail($id);
        $users = User::whereIn('role_id', [3, 4])->get();
        
        return view('technician.crm.contacts.show', compact('contact', 'users'));
    }

    public function edit($id)
    {
        $contact = CrmContact::findOrFail($id);
        $users = User::whereIn('role_id', [3, 4])->get();
        
        return view('technician.crm.contacts.edit', compact('contact', 'users'));
    }

    public function update(Request $request, $id)
    {
        $contact = CrmContact::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,prospect,customer',
            'source' => 'required|in:website,referral,cold_call,email,social_media,other',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $contact->update($request->all());

        return redirect()->route('technician.crm.contacts.show', $id)->with('success', 'Contact updated successfully.');
    }

    public function destroy($id)
    {
        $contact = CrmContact::findOrFail($id);
        $contact->delete();

        return redirect()->route('technician.crm.index')->with('success', 'Contact deleted successfully.');
    }

    public function interactions($id)
    {
        $contact = CrmContact::findOrFail($id);
        $interactions = $contact->interactions()->with('user')->latest()->get();
        
        return view('technician.crm.contacts.interactions', compact('contact', 'interactions'));
    }

    public function storeInteraction(Request $request, $id)
    {
        $contact = CrmContact::findOrFail($id);
        
        $request->validate([
            'type' => 'required|in:call,email,meeting,note,task,follow_up',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'priority' => 'required|in:1,2,3',
        ]);

        CrmInteraction::create([
            'contact_id' => $contact->id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'subject' => $request->subject,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'priority' => $request->priority,
            'status' => $request->scheduled_at ? 'pending' : 'completed',
        ]);

        return redirect()->back()->with('success', 'Interaction added successfully.');
    }

    public function updateInteractionStatus(Request $request, $id)
    {
        $interaction = CrmInteraction::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $interaction->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }
}
