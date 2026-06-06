<?php

namespace App\Services;

use App\Models\User;
use App\Models\CasePatient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentials;
use Exception;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Create a new user
     */
    public function createUser(array $data)
    {
        try {
            // Generate password if not provided
            if (!isset($data['password'])) {
                $data['password'] = Str::random(10);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $data['role_id'],
                'status' => $data['status'] ?? 'active',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'bio' => $data['bio'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'code_parrent' => $data['code_parrent'] ?? null,
            ]);

            // Send credentials email
            $this->sendCredentialsEmail($user, $data['password']);

            return $user;

        } catch (Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            throw new Exception('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing user
     */
    public function updateUser($userId, array $data)
    {
        try {
            $user = User::findOrFail($userId);

            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'],
                'status' => $data['status'] ?? $user->status,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'bio' => $data['bio'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'code_parrent' => $data['code_parrent'] ?? null,
            ];

            // Update password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            return $user;

        } catch (Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            throw new Exception('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Check if user has associated cases
            $hasCases = CasePatient::where('doctor_id', $userId)
                                  ->orWhere('technician_id', $userId)
                                  ->orWhere('laboratory_id', $userId)
                                  ->exists();

            if ($hasCases) {
                throw new Exception('Cannot delete user with associated cases');
            }

            $user->delete();

            return true;

        } catch (Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            throw new Exception('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Get users by role with filters
     */
    public function getUsersByRole($roleId, $filters = [])
    {
        try {
            $query = User::where('role_id', $roleId);

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }

            if (isset($filters['doctor_id'])) {
                $query->where('doctor_id', $filters['doctor_id']);
            }

            return $query;

        } catch (Exception $e) {
            Log::error('Error getting users by role: ' . $e->getMessage());
            throw new Exception('Failed to retrieve users');
        }
    }

    /**
     * Get doctors with their case statistics
     */
    public function getDoctorsWithStats()
    {
        try {
            return User::where('role_id', 2)
                      ->where('status', 'active')
                      ->withCount(['cases as total_cases'])
                      ->with(['cases' => function($query) {
                          $query->select('doctor_id', 'status');
                      }])
                      ->get()
                      ->map(function($doctor) {
                          $doctor->cases_by_status = $doctor->cases->groupBy('status');
                          unset($doctor->cases);
                          return $doctor;
                      });

        } catch (Exception $e) {
            Log::error('Error getting doctors with stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve doctors statistics');
        }
    }

    /**
     * Get technicians by doctor
     */
    public function getTechniciansByDoctor($doctorId)
    {
        try {
            return User::where('role_id', 3)
                      ->where('doctor_id', $doctorId)
                      ->where('status', 'active')
                      ->get();

        } catch (Exception $e) {
            Log::error('Error getting technicians by doctor: ' . $e->getMessage());
            throw new Exception('Failed to retrieve technicians');
        }
    }

    /**
     * Get laboratories by doctor
     */
    public function getLaboratoriesByDoctor($doctorId)
    {
        try {
            return User::where('role_id', 4)
                      ->where('doctor_id', $doctorId)
                      ->where('status', 'active')
                      ->get();

        } catch (Exception $e) {
            Log::error('Error getting laboratories by doctor: ' . $e->getMessage());
            throw new Exception('Failed to retrieve laboratories');
        }
    }

    /**
     * Change user status
     */
    public function changeUserStatus($userId, $newStatus)
    {
        try {
            $user = User::findOrFail($userId);
            
            if (!in_array($newStatus, ['active', 'inactive', 'suspended', 'pending'])) {
                throw new Exception('Invalid status');
            }

            $user->update(['status' => $newStatus]);

            return $user;

        } catch (Exception $e) {
            Log::error('Error changing user status: ' . $e->getMessage());
            throw new Exception('Failed to change user status: ' . $e->getMessage());
        }
    }

    /**
     * Check if doctor code exists
     */
    public function checkDoctorCode($code)
    {
        try {
            $doctor = User::where('code_parrent', $code)
                         ->where('role_id', 2)
                         ->where('status', 'active')
                         ->first();

            return $doctor ? ['status' => 'success', 'data' => $doctor] 
                          : ['status' => 'error', 'data' => ''];

        } catch (Exception $e) {
            Log::error('Error checking doctor code: ' . $e->getMessage());
            throw new Exception('Failed to check doctor code');
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        try {
            return [
                'total_doctors' => User::where('role_id', 2)->count(),
                'active_doctors' => User::where('role_id', 2)->where('status', 'active')->count(),
                'total_technicians' => User::where('role_id', 3)->count(),
                'active_technicians' => User::where('role_id', 3)->where('status', 'active')->count(),
                'total_laboratories' => User::where('role_id', 4)->count(),
                'active_laboratories' => User::where('role_id', 4)->where('status', 'active')->count(),
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
            ];

        } catch (Exception $e) {
            Log::error('Error getting user stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve user statistics');
        }
    }

    /**
     * Send credentials email to new user
     */
    private function sendCredentialsEmail($user, $password)
    {
        try {
            Mail::to($user->email)->send(new UserCredentials($user, $password));
        } catch (Exception $e) {
            Log::error('Failed to send credentials email: ' . $e->getMessage());
            // Don't throw exception here as user creation should still succeed
        }
    }

    /**
     * Validate user data
     */
    public function validateUserData($data, $userId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($userId ? ',' . $userId : ''),
            'role_id' => 'required|integer|in:1,2,3,4',
            'status' => 'sometimes|in:active,inactive,suspended,pending',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ];

        if (!$userId) {
            $rules['password'] = 'sometimes|string|min:6';
        }

        return $rules;
    }
}
