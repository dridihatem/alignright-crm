<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\CasePatient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class PatientRepository
{
    protected $model;

    public function __construct(Patient $model)
    {
        $this->model = $model;
    }

    /**
     * Get all patients with optional filters
     */
    public function getAll(array $filters = [], array $relations = ['doctor', 'cases'])
    {
        try {
            $query = $this->model->with($relations);

            if (isset($filters['doctor_id'])) {
                $query->where('doctor_id', $filters['doctor_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('reference', 'like', '%' . $search . '%');
                });
            }

            return $query->orderBy('created_at', 'desc');
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve patients: ' . $e->getMessage());
        }
    }

    /**
     * Get patient by ID
     */
    public function findById($id, array $relations = ['doctor', 'cases'])
    {
        try {
            return $this->model->with($relations)->findOrFail($id);
        } catch (Exception $e) {
            throw new Exception('Patient not found');
        }
    }

    /**
     * Get patient by reference
     */
    public function findByReference($reference, array $relations = ['doctor', 'cases'])
    {
        try {
            return $this->model->with($relations)
                              ->where('reference', $reference)
                              ->firstOrFail();
        } catch (Exception $e) {
            throw new Exception('Patient not found');
        }
    }

    /**
     * Get patients by doctor ID
     */
    public function getByDoctor($doctorId, array $relations = ['cases'])
    {
        try {
            return $this->model->with($relations)
                              ->where('doctor_id', $doctorId)
                              ->orderBy('created_at', 'desc')
                              ->get();
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve patients for doctor');
        }
    }

    /**
     * Create a new patient
     */
    public function create(array $data)
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            throw new Exception('Failed to create patient: ' . $e->getMessage());
        }
    }

    /**
     * Update a patient
     */
    public function update($id, array $data)
    {
        try {
            $patient = $this->model->findOrFail($id);
            $patient->update($data);
            return $patient;
        } catch (Exception $e) {
            throw new Exception('Failed to update patient: ' . $e->getMessage());
        }
    }

    /**
     * Update patient by reference
     */
    public function updateByReference($reference, array $data)
    {
        try {
            $patient = $this->model->where('reference', $reference)->firstOrFail();
            $patient->update($data);
            return $patient;
        } catch (Exception $e) {
            throw new Exception('Failed to update patient: ' . $e->getMessage());
        }
    }

    /**
     * Delete a patient
     */
    public function delete($id)
    {
        try {
            $patient = $this->model->findOrFail($id);
            $patient->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete patient: ' . $e->getMessage());
        }
    }

    /**
     * Delete patient by reference
     */
    public function deleteByReference($reference)
    {
        try {
            $patient = $this->model->where('reference', $reference)->firstOrFail();
            $patient->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete patient: ' . $e->getMessage());
        }
    }

    /**
     * Get patient statistics
     */
    public function getStats($doctorId = null)
    {
        try {
            $query = $this->model;

            if ($doctorId) {
                $query = $query->where('doctor_id', $doctorId);
            }

            $stats = [
                'total' => $query->count(),
                'active' => $query->where('status', 'active')->count(),
                'inactive' => $query->where('status', 'inactive')->count(),
                'new_this_month' => $query->where('created_at', '>=', now()->startOfMonth())->count(),
                'new_this_week' => $query->where('created_at', '>=', now()->startOfWeek())->count(),
            ];

            return $stats;
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve patient statistics');
        }
    }

    /**
     * Search patients
     */
    public function search($search, $doctorId = null, $limit = 10)
    {
        try {
            $query = $this->model;

            if ($doctorId) {
                $query = $query->where('doctor_id', $doctorId);
            }

            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('reference', 'like', '%' . $search . '%');
            })->limit($limit)->get();
        } catch (Exception $e) {
            throw new Exception('Failed to search patients');
        }
    }

    /**
     * Get patients with case counts
     */
    public function getWithCaseCounts($doctorId = null)
    {
        try {
            $query = $this->model->withCount('cases');

            if ($doctorId) {
                $query = $query->where('doctor_id', $doctorId);
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve patients with case counts');
        }
    }

    /**
     * Check if patient reference exists
     */
    public function referenceExists($reference)
    {
        try {
            return $this->model->where('reference', $reference)->exists();
        } catch (Exception $e) {
            throw new Exception('Failed to check patient reference');
        }
    }

    /**
     * Get patients for DataTable
     */
    public function getForDataTable(array $filters = [])
    {
        try {
            $query = $this->model->with(['doctor', 'cases']);

            // Apply filters
            if (isset($filters['doctor_id'])) {
                $query->where('doctor_id', $filters['doctor_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('reference', 'like', '%' . $search . '%');
                });
            }

            return $query->orderBy('created_at', 'desc');
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve patients for DataTable');
        }
    }
}
