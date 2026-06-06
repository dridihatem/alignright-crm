<?php

namespace App\Repositories;

use App\Models\CasePatient;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class CaseRepository
{
    protected $model;

    public function __construct(CasePatient $model)
    {
        $this->model = $model;
    }

    /**
     * Get all cases with relationships
     */
    public function getAllWithRelations(array $relations = ['patient', 'doctor', 'technician', 'laboratory'])
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Get cases by doctor
     */
    public function getByDoctor($doctorId, array $relations = ['patient'])
    {
        return $this->model->where('doctor_id', $doctorId)
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases by technician
     */
    public function getByTechnician($technicianId, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where('technician_id', $technicianId)
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases by laboratory
     */
    public function getByLaboratory($laboratoryId, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where('laboratory_id', $laboratoryId)
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases by status
     */
    public function getByStatus($status, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where('status', $status)
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get case by ID with relationships
     */
    public function findById($id, array $relations = ['patient', 'doctor', 'technician', 'laboratory'])
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Get case by case_id
     */
    public function findByCaseId($caseId, array $relations = ['patient', 'doctor', 'technician', 'laboratory'])
    {
        return $this->model->where('case_id', $caseId)
                          ->with($relations)
                          ->first();
    }

    /**
     * Create a new case
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a case
     */
    public function update($id, array $data)
    {
        $case = $this->model->findOrFail($id);
        $case->update($data);
        return $case;
    }

    /**
     * Delete a case
     */
    public function delete($id)
    {
        $case = $this->model->findOrFail($id);
        return $case->delete();
    }

    /**
     * Get cases with filters for DataTables
     */
    public function getWithFilters(array $filters = [], array $relations = ['patient', 'doctor'])
    {
        $query = $this->model->with($relations);

        // Apply filters
        if (isset($filters['case_id'])) {
            $query->where('case_id', 'like', '%' . $filters['case_id'] . '%');
        }

        if (isset($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (isset($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (isset($filters['technician_id'])) {
            $query->where('technician_id', $filters['technician_id']);
        }

        if (isset($filters['laboratory_id'])) {
            $query->where('laboratory_id', $filters['laboratory_id']);
        }

        if (isset($filters['treatment_type'])) {
            $query->where('treatment_type', $filters['treatment_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('case_id', 'like', '%' . $search . '%')
                  ->orWhere('treatment_type', 'like', '%' . $search . '%')
                  ->orWhereHas('patient', function($patientQuery) use ($search) {
                      $patientQuery->where('name', 'like', '%' . $search . '%')
                                  ->orWhere('surname', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query;
    }

    /**
     * Get cases with pagination
     */
    public function getPaginated($perPage = 15, array $filters = [], array $relations = ['patient', 'doctor'])
    {
        $query = $this->getWithFilters($filters, $relations);
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        return [
            'total_cases' => $this->model->count(),
            'draft_cases' => $this->model->where('status', 'draft')->count(),
            'pending_cases' => $this->model->where('status', 'pending')->count(),
            'in_planning_cases' => $this->model->where('status', 'in_planning')->count(),
            'approval_cases' => $this->model->where('status', 'approval')->count(),
            'in_production_cases' => $this->model->where('status', 'in_production')->count(),
            'shipped_cases' => $this->model->where('status', 'shipped')->count(),
            'rejected_cases' => $this->model->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get monthly case totals
     */
    public function getMonthlyTotals($months = 12)
    {
        return $this->model->where('created_at', '>=', now()->subMonths($months))
                          ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                          ->groupBy('month')
                          ->pluck('total', 'month')
                          ->toArray();
    }

    /**
     * Get cases by date range
     */
    public function getByDateRange($startDate, $endDate, array $relations = ['patient', 'doctor'])
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get unassigned cases (no technician or laboratory)
     */
    public function getUnassignedCases(array $relations = ['patient', 'doctor'])
    {
        return $this->model->whereNull('technician_id')
                          ->orWhereNull('laboratory_id')
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases that need attention (pending for too long)
     */
    public function getCasesNeedingAttention($daysThreshold = 7, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where('status', 'pending')
                          ->where('created_at', '<=', now()->subDays($daysThreshold))
                          ->with($relations)
                          ->orderBy('created_at', 'asc')
                          ->get();
    }

    /**
     * Get cases by priority
     */
    public function getByPriority($priority, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where('priority', $priority)
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases with price information
     */
    public function getWithPriceInfo(array $relations = ['patient', 'doctor'])
    {
        return $this->model->whereNotNull('price')
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases by treatment type
     */
    public function getByTreatmentType($treatmentType, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where('treatment_type', $treatmentType)
                          ->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * Get cases statistics by doctor
     */
    public function getStatsByDoctor($doctorId)
    {
        return $this->model->where('doctor_id', $doctorId)
                          ->selectRaw('status, COUNT(*) as count')
                          ->groupBy('status')
                          ->pluck('count', 'status')
                          ->toArray();
    }

    /**
     * Get cases statistics by technician
     */
    public function getStatsByTechnician($technicianId)
    {
        return $this->model->where('technician_id', $technicianId)
                          ->selectRaw('status, COUNT(*) as count')
                          ->groupBy('status')
                          ->pluck('count', 'status')
                          ->toArray();
    }

    /**
     * Get cases statistics by laboratory
     */
    public function getStatsByLaboratory($laboratoryId)
    {
        return $this->model->where('laboratory_id', $laboratoryId)
                          ->selectRaw('status, COUNT(*) as count')
                          ->groupBy('status')
                          ->pluck('count', 'status')
                          ->toArray();
    }

    /**
     * Get recent cases
     */
    public function getRecent($limit = 10, array $relations = ['patient', 'doctor'])
    {
        return $this->model->with($relations)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Search cases
     */
    public function search($searchTerm, array $relations = ['patient', 'doctor'])
    {
        return $this->model->where(function($query) use ($searchTerm) {
                $query->where('case_id', 'like', '%' . $searchTerm . '%')
                      ->orWhere('treatment_type', 'like', '%' . $searchTerm . '%')
                      ->orWhere('doctor_instruction', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('patient', function($patientQuery) use ($searchTerm) {
                          $patientQuery->where('name', 'like', '%' . $searchTerm . '%')
                                      ->orWhere('surname', 'like', '%' . $searchTerm . '%')
                                      ->orWhere('email', 'like', '%' . $searchTerm . '%');
                      });
            })
            ->with($relations)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
