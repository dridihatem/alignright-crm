<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class PatientService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get patients for DataTable with filters
     */
    public function getPatientsForDataTable(array $filters = [])
    {
        try {
            $query = Patient::query();

            // Apply filters
            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('reference', 'like', '%' . $search . '%');
                });
            }

            if (isset($filters['doctor_id'])) {
                $query->where('doctor_id', $filters['doctor_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            return $query->with(['doctor', 'cases'])->orderBy('created_at', 'desc');
        } catch (Exception $e) {
            Log::error('Error getting patients for DataTable: ' . $e->getMessage());
            throw new Exception('Failed to retrieve patients: ' . $e->getMessage());
        }
    }

    /**
     * Create a new patient
     */
    public function createPatient(array $data, $doctorId)
    {
        DB::beginTransaction();
        try {
            // Generate unique reference
            $data['reference'] = $this->generatePatientReference();
            $data['doctor_id'] = $doctorId;

            $patient = Patient::create($data);

            DB::commit();
            return $patient;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating patient: ' . $e->getMessage());
            throw new Exception('Failed to create patient: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing patient
     */
    public function updatePatient($reference, array $data)
    {
        try {
            $patient = Patient::where('reference', $reference)->firstOrFail();
            
            $patient->update($data);
            
            return $patient;
        } catch (Exception $e) {
            Log::error('Error updating patient: ' . $e->getMessage());
            throw new Exception('Failed to update patient: ' . $e->getMessage());
        }
    }

    /**
     * Delete a patient
     */
    public function deletePatient($reference)
    {
        DB::beginTransaction();
        try {
            $patient = Patient::where('reference', $reference)->firstOrFail();
            
            // Check if patient has cases
            if ($patient->cases()->count() > 0) {
                throw new Exception('Cannot delete patient with existing cases');
            }

            $patient->delete();
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting patient: ' . $e->getMessage());
            throw new Exception('Failed to delete patient: ' . $e->getMessage());
        }
    }

    /**
     * Get patient by reference
     */
    public function getPatientByReference($reference)
    {
        try {
            return Patient::where('reference', $reference)
                         ->with(['doctor', 'cases.technician', 'cases.laboratory'])
                         ->firstOrFail();
        } catch (Exception $e) {
            Log::error('Error getting patient by reference: ' . $e->getMessage());
            throw new Exception('Patient not found');
        }
    }

    /**
     * Get patient statistics for doctor
     */
    public function getPatientStats($doctorId)
    {
        try {
            $totalPatients = Patient::where('doctor_id', $doctorId)->count();
            $activePatients = Patient::where('doctor_id', $doctorId)
                                   ->where('status', 'active')
                                   ->count();
            $newPatientsThisMonth = Patient::where('doctor_id', $doctorId)
                                         ->where('created_at', '>=', now()->startOfMonth())
                                         ->count();

            return [
                'total_patients' => $totalPatients,
                'active_patients' => $activePatients,
                'new_patients_this_month' => $newPatientsThisMonth,
            ];
        } catch (Exception $e) {
            Log::error('Error getting patient stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve patient statistics');
        }
    }

    /**
     * Generate unique patient reference
     */
    private function generatePatientReference()
    {
        do {
            $reference = 'PT-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Search patients by name or email
     */
    public function searchPatients($search, $doctorId = null)
    {
        try {
            $query = Patient::query();
            
            if ($doctorId) {
                $query->where('doctor_id', $doctorId);
            }

            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('reference', 'like', '%' . $search . '%');
            })->limit(10)->get();
        } catch (Exception $e) {
            Log::error('Error searching patients: ' . $e->getMessage());
            throw new Exception('Failed to search patients');
        }
    }
}
