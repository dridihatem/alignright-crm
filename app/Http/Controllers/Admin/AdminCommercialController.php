<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class AdminCommercialController extends Controller
{
    /**
     * Display the commercial users list
     */
    public function index()
    {
        try {
            return view('admin.commercial.index');
        } catch (Exception $e) {
            Log::error('Error displaying commercial users index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load commercial users page');
        }
    }

    /**
     * Get commercial users for DataTable
     */
    public function getCommercialUsers(Request $request): JsonResponse
    {
        try {
            $query = User::where('role_id', 5)->with('role');

            return DataTables::of($query)
                ->addColumn('photo', function ($user) {
                    return '<div class="avatar avatar-sm">
                        <img src="' . $user->photo_url . '" alt="' . $user->name . '" class="rounded-circle">
                    </div>';
                })
                ->addColumn('role_name', function ($user) {
                    return $user->role->name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($user) {
                    return $this->getStatusBadge($user->status);
                })
                ->addColumn('created_at_formatted', function ($user) {
                    return $user->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('actions', function ($user) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" 
                                id="dropdownMenuButton" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.commercial.show', $user->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('admin.commercial.edit', $user->id).'">'.__('master.edit').'</a></li>
                            <li><a class="dropdown-item waves-effect text-danger" href="'.route('admin.commercial.delete', $user->id).'">'.__('master.delete').'</a></li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['photo', 'status_badge', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting commercial users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve commercial users'], 500);
        }
    }

    /**
     * Show the form for creating a new commercial user
     */
    public function create()
    {
        try {
            return view('admin.commercial.create');
        } catch (Exception $e) {
            Log::error('Error showing create commercial user form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load create commercial user form');
        }
    }

    /**
     * Store a newly created commercial user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'status' => 'required|in:active,inactive,suspended,pending',
            ]);

            // Get commercial role ID
            $commercialRole = Role::where('name', 'commercial')->first();
            if (!$commercialRole) {
                return redirect()->back()->with('error', 'Commercial role not found');
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $commercialRole->id,
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'],
            ]);

            Log::info('Commercial user created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.commercial.list')
                           ->with('success', 'Commercial user created successfully');
                           
        } catch (Exception $e) {
            Log::error('Error creating commercial user: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create commercial user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified commercial user
     */
    public function show($id)
    {
        try {
            $user = User::where('role_id', 5)->findOrFail($id);
            
            return view('admin.commercial.show', compact('user'));
        } catch (Exception $e) {
            Log::error('Error showing commercial user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Commercial user not found');
        }
    }

    /**
     * Show the form for editing the specified commercial user
     */
    public function edit($id)
    {
        try {
            $user = User::where('role_id', 5)->findOrFail($id);
            
            return view('admin.commercial.edit', compact('user'));
        } catch (Exception $e) {
            Log::error('Error showing edit commercial user form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Commercial user not found');
        }
    }

    /**
     * Update the specified commercial user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::where('role_id', 5)->findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'status' => 'required|in:active,inactive,suspended,pending',
            ]);

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'],
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            Log::info('Commercial user updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('admin.commercial.list')
                           ->with('success', 'Commercial user updated successfully');
                           
        } catch (Exception $e) {
            Log::error('Error updating commercial user: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update commercial user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified commercial user
     */
    public function destroy($id)
    {
        try {
            $user = User::where('role_id', 5)->findOrFail($id);
            
            // Prevent deleting the last admin
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'You cannot delete your own account');
            }

            $userEmail = $user->email;
            $user->delete();

            Log::info('Commercial user deleted successfully', [
                'deleted_user_email' => $userEmail,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('admin.commercial.list')
                           ->with('success', 'Commercial user deleted successfully');
                           
        } catch (Exception $e) {
            Log::error('Error deleting commercial user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete commercial user: ' . $e->getMessage());
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
            'suspended' => '<span class="badge bg-label-danger">Suspended</span>',
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }
}