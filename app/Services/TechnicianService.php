<?php

namespace App\Services;

use App\Models\CasePatient;
use App\Models\TreatmentType;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\CaseRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotification;
use Exception;

class TechnicianService
{
    protected $caseRepository;
    protected $userRepository;

    public function __construct(CaseRepository $caseRepository, UserRepository $userRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get dashboard statistics for technician
     */
    public function getDashboardStats($technicianId)
    {
        try {
            $cases = CasePatient::where('technician_id', $technicianId)->get();
            
            $stats = [
                'total_cases' => $cases->whereIn('status',['pending','in_production','in_planning'])->count(),
                'status_in_production' => $cases->where('status', 'in_production')->count(),
               
            ];

            // Calculate retarded percentage
            $pendingCases = $stats['status_in_production'];
            $totalCases = $stats['total_cases'];
            $stats['case_retarded_percentage'] = $totalCases > 0 
                ? number_format(($pendingCases / $totalCases) * 100, 2) 
                : 0;

            return $stats;
        } catch (Exception $e) {
            Log::error('Error getting technician dashboard stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve dashboard statistics');
        }
    }

    /**
     * Get technician's cases with pagination and filters
     */
    public function getTechnicianCases($technicianId, array $filters = [])
    {
        try {
            $query = CasePatient::where('technician_id', $technicianId)
                               ->whereIn('status',['pending','in_production','in_planning'])
                               ->with(['patient', 'doctor'])
                               ->latest();

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('case_id', 'like', '%' . $search . '%')
                      ->orWhere('treatment_type', 'like', '%' . $search . '%')
                      ->orWhereHas('patient', function($patientQuery) use ($search) {
                          $patientQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            return $query;
        } catch (Exception $e) {
            Log::error('Error getting technician cases: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases');
        }
    }

    /**
     * Update case status
     */
    public function updateCaseStatus($caseId, $status, $technicianId)
    {
        DB::beginTransaction();
        try {
            $case = CasePatient::where('id', $caseId)
                              ->where('technician_id', $technicianId)
                              ->firstOrFail();

            $case->update(['status' => $status]);

            // Update specific date fields based on status
            if ($status === 'approval') {
                $case->update(['accepted_date' => now()]);
            } elseif ($status === 'rejected') {
                $case->update(['rejected_date' => now()]);
            }

            // Send notification to doctor
            $this->sendStatusUpdateNotification($case, $status);

            DB::commit();
            return $case;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating case status: ' . $e->getMessage());
            throw new Exception('Failed to update case status');
        }
    }

    /**
     * Add comment to case
     */
    public function addCaseComment($caseId, $userId, $comment)
    {
        try {
            $case = CasePatient::where('id', $caseId)
                              ->where('technician_id', $userId)
                              ->firstOrFail();

            $commentRecord = Comment::create([
                'case_id' => $caseId,
                'user_id' => $userId,
                'comment' => $comment,
                'type' => 'technician_update'
            ]);

            // Load the user relationship for the response
            $commentRecord->load('user');

            // Send notification to doctor
            $this->sendCommentNotification($case, $commentRecord);

            return $commentRecord;
        } catch (Exception $e) {
            Log::error('Error adding case comment: ' . $e->getMessage());
            throw new Exception('Failed to add comment');
        }
    }

    /**
     * Accept treatment type
     */
    public function acceptTreatmentType($treatmentTypeId, $technicianId)
    {
        DB::beginTransaction();
        try {
            $treatmentType = TreatmentType::findOrFail($treatmentTypeId);
            
            // Verify technician has access
            $case = CasePatient::where('id', $treatmentType->case_id)
                              ->where('technician_id', $technicianId)
                              ->firstOrFail();

            $treatmentType->update([
                'status' => 'in_progress',
                'accepted_by' => $technicianId,
                'accepted_at' => now()
            ]);

            // Update case status if needed
            if ($case->status !== 'in_production') {
                $case->update(['status' => 'in_production']);
            }

            DB::commit();
            return $treatmentType;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error accepting treatment type: ' . $e->getMessage());
            throw new Exception('Failed to accept treatment type');
        }
    }

    /**
     * Complete treatment type with WeTransfer link
     */
    public function completeTreatmentType($treatmentTypeId, $technicianId, $wetransferLink, $notes = null)
    {
        DB::beginTransaction();
        try {
            $treatmentType = TreatmentType::findOrFail($treatmentTypeId);
            
            // Verify technician has access
            $case = CasePatient::where('id', $treatmentType->case_id)
                              ->where('technician_id', $technicianId)
                              ->firstOrFail();

            $treatmentType->update([
                'wetransfer_link' => $wetransferLink,
                'status' => 'completed',
                'treatment_plan_uploaded_at' => now(),
                'uploaded_by' => $technicianId
            ]);

            // Add completion comment if provided
            if ($notes) {
                Comment::create([
                    'case_id' => $case->id,
                    'user_id' => $technicianId,
                    'comment' => 'Treatment completed: ' . $notes,
                    'type' => 'treatment_completion'
                ]);
            }

            // Send notification to laboratory
            $this->sendCompletionNotification($case, $treatmentType);

            DB::commit();
            return $treatmentType;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error completing treatment type: ' . $e->getMessage());
            throw new Exception('Failed to complete treatment type');
        }
    }

    /**
     * Update estimated completion date
     */
    public function updateEstimatedCompletion($treatmentTypeId, $technicianId, $estimatedDate)
    {
        try {
            $treatmentType = TreatmentType::findOrFail($treatmentTypeId);
            
            // Verify technician has access
            CasePatient::where('id', $treatmentType->case_id)
                      ->where('technician_id', $technicianId)
                      ->firstOrFail();

            $treatmentType->update([
                'estimated_completion_date' => $estimatedDate
            ]);

            return $treatmentType;
        } catch (Exception $e) {
            Log::error('Error updating estimated completion: ' . $e->getMessage());
            throw new Exception('Failed to update estimated completion date');
        }
    }

    /**
     * Send status update notification
     */
    private function sendStatusUpdateNotification($case, $status)
    {
        try {
            $notification = Notification::create([
                'title' => 'Case Status Updated',
                'message' => "Case {$case->case_id} status changed to {$status}",
                'type' => 'case_status_update',
                'status' => 'pending',
                'case_id' => $case->id,
                'technician_id' => $case->technician_id,
                'doctor_id' => $case->doctor_id
            ]);

            if ($case->doctor && $case->doctor->email) {
                Mail::to($case->doctor->email)->send(new SendNotification($notification));
            }
        } catch (Exception $e) {
            Log::error('Error sending status update notification: ' . $e->getMessage());
        }
    }

    /**
     * Send comment notification
     */
    private function sendCommentNotification($case, $comment)
    {
        try {
            $notification = Notification::create([
                'title' => 'New Case Comment',
                'message' => "Technician added a comment to case {$case->case_id}",
                'type' => 'case_comment',
                'status' => 'pending',
                'case_id' => $case->id,
                'technician_id' => $case->technician_id,
                'doctor_id' => $case->doctor_id
            ]);

            if ($case->doctor && $case->doctor->email) {
                Mail::to($case->doctor->email)->send(new SendNotification($notification));
            }
        } catch (Exception $e) {
            Log::error('Error sending comment notification: ' . $e->getMessage());
        }
    }

    /**
     * Send completion notification to laboratory
     */
    private function sendCompletionNotification($case, $treatmentType)
    {
        try {
            if ($case->laboratory_id) {
                $notification = Notification::create([
                    'title' => 'Treatment Completed',
                    'message' => "Treatment type \"{$treatmentType->name}\" completed for case {$case->case_id}",
                    'type' => 'treatment_completion',
                    'status' => 'pending',
                    'case_id' => $case->id,
                    'technician_id' => $case->technician_id,
                    'laboratory_id' => $case->laboratory_id,
                    'treatment_type_id' => $treatmentType->id
                ]);

                if ($case->laboratory && $case->laboratory->email) {
                    Mail::to($case->laboratory->email)->send(new SendNotification($notification));
                }
            }
        } catch (Exception $e) {
            Log::error('Error sending completion notification: ' . $e->getMessage());
        }
    }
}
