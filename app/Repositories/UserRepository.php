<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\CasePatient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get all users
     */
    public function getAll(array $relations = [])
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Get user by ID
     */
    public function findById($id, array $relations = [])
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Get user by email
     */
    public function findByEmail($email, array $relations = [])
    {
        return $this->model->with($relations)->where('email', $email)->first();
    }

    /**
     * Get users by role
     */
    public function getByRole($roleId, array $relations = [])
    {
        return $this->model->where('role_id', $roleId)
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get active users by role
     */
    public function getActiveByRole($roleId, array $relations = [])
    {
        return $this->model->where('role_id', $roleId)
                          ->where('status', 'active')
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get users by status
     */
    public function getByStatus($status, array $relations = [])
    {
        return $this->model->where('status', $status)
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get users by doctor (technicians and laboratories)
     */
    public function getByDoctor($doctorId, array $relations = [])
    {
        return $this->model->where('doctor_id', $doctorId)
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get users with filters
     */
    public function getWithFilters(array $filters = [], array $relations = [])
    {
        $query = $this->model->with($relations);

        // Apply filters
        if (isset($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('specialization', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    /**
     * Get users with pagination
     */
    public function getPaginated($perPage = 15, array $filters = [], array $relations = [])
    {
        $query = $this->getWithFilters($filters, $relations);
        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Create a new user
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a user
     */
    public function update($id, array $data)
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user;
    }

    /**
     * Delete a user
     */
    public function delete($id)
    {
        $user = $this->model->findOrFail($id);
        return $user->delete();
    }

    /**
     * Get doctors with case statistics
     */
    public function getDoctorsWithStats()
    {
        return $this->model->where('role_id', 2)
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
    }

    /**
     * Get technicians with case statistics
     */
    public function getTechniciansWithStats()
    {
        return $this->model->where('role_id', 3)
                          ->where('status', 'active')
                          ->withCount(['technicianCases as total_cases'])
                          ->with(['technicianCases' => function($query) {
                              $query->select('technician_id', 'status');
                          }])
                          ->get()
                          ->map(function($technician) {
                              $technician->cases_by_status = $technician->technicianCases->groupBy('status');
                              unset($technician->technicianCases);
                              return $technician;
                          });
    }

    /**
     * Get laboratories with case statistics
     */
    public function getLaboratoriesWithStats()
    {
        return $this->model->where('role_id', 4)
                          ->where('status', 'active')
                          ->withCount(['laboratoryCases as total_cases'])
                          ->with(['laboratoryCases' => function($query) {
                              $query->select('laboratory_id', 'status');
                          }])
                          ->get()
                          ->map(function($laboratory) {
                              $laboratory->cases_by_status = $laboratory->laboratoryCases->groupBy('status');
                              unset($laboratory->laboratoryCases);
                              return $laboratory;
                          });
    }

    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        return [
            'total_users' => $this->model->count(),
            'active_users' => $this->model->where('status', 'active')->count(),
            'inactive_users' => $this->model->where('status', 'inactive')->count(),
            'suspended_users' => $this->model->where('status', 'suspended')->count(),
            'pending_users' => $this->model->where('status', 'pending')->count(),
            'total_doctors' => $this->model->where('role_id', 2)->count(),
            'active_doctors' => $this->model->where('role_id', 2)->where('status', 'active')->count(),
            'total_technicians' => $this->model->where('role_id', 3)->count(),
            'active_technicians' => $this->model->where('role_id', 3)->where('status', 'active')->count(),
            'total_laboratories' => $this->model->where('role_id', 4)->count(),
            'active_laboratories' => $this->model->where('role_id', 4)->where('status', 'active')->count(),
        ];
    }

    /**
     * Get users by role with statistics
     */
    public function getByRoleWithStats($roleId)
    {
        return $this->model->where('role_id', $roleId)
                          ->withCount(['cases as total_cases'])
                          ->with(['cases' => function($query) {
                              $query->select('doctor_id', 'status');
                          }])
                          ->get()
                          ->map(function($user) {
                              $user->cases_by_status = $user->cases->groupBy('status');
                              unset($user->cases);
                              return $user;
                          });
    }

    /**
     * Search users
     */
    public function search($searchTerm, array $relations = [])
    {
        return $this->model->where(function($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('specialization', 'like', '%' . $searchTerm . '%')
                      ->orWhere('license_number', 'like', '%' . $searchTerm . '%');
            })
            ->with($relations)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get users by date range
     */
    public function getByDateRange($startDate, $endDate, array $relations = [])
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get recent users
     */
    public function getRecent($limit = 10, array $relations = [])
    {
        return $this->model->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get users with case counts
     */
    public function getWithCaseCounts($roleId = null)
    {
        $query = $this->model->withCount(['cases as total_cases']);

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->orderBy('total_cases', 'desc')->get();
    }

    /**
     * Get users by specialization
     */
    public function getBySpecialization($specialization, array $relations = [])
    {
        return $this->model->where('specialization', 'like', '%' . $specialization . '%')
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get users by city/location
     */
    public function getByLocation($city, array $relations = [])
    {
        return $this->model->where('address', 'like', '%' . $city . '%')
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get users with no cases
     */
    public function getWithNoCases($roleId = null)
    {
        $query = $this->model->whereDoesntHave('cases');

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get users with pending cases
     */
    public function getWithPendingCases($roleId = null)
    {
        $query = $this->model->whereHas('cases', function($q) {
            $q->where('status', 'pending');
        });

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->withCount(['cases as pending_cases' => function($q) {
            $q->where('status', 'pending');
        }])->orderBy('pending_cases', 'desc')->get();
    }

    /**
     * Get users by activity level
     */
    public function getByActivityLevel($daysThreshold = 30, $roleId = null)
    {
        $query = $this->model->whereHas('cases', function($q) use ($daysThreshold) {
            $q->where('created_at', '>=', now()->subDays($daysThreshold));
        });

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->withCount(['cases as recent_cases' => function($q) use ($daysThreshold) {
            $q->where('created_at', '>=', now()->subDays($daysThreshold));
        }])->orderBy('recent_cases', 'desc')->get();
    }

    /**
     * Check if doctor code exists
     */
    public function checkDoctorCode($code)
    {
        try {
            $doctor = $this->model->where('code_parrent', $code)
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
     * Get technicians by doctor
     */
    public function getTechniciansByDoctor($doctorId, array $relations = [])
    {
        return $this->model->where('role_id', 3)
                          ->where('doctor_id', $doctorId)
                          ->where('status', 'active')
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }

    /**
     * Get laboratories by doctor
     */
    public function getLaboratoriesByDoctor($doctorId, array $relations = [])
    {
        return $this->model->where('role_id', 4)
                          ->where('doctor_id', $doctorId)
                          ->where('status', 'active')
                          ->with($relations)
                          ->orderBy('name')
                          ->get();
    }
}
