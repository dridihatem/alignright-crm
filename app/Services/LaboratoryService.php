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

class LaboratoryService
{
    protected $caseRepository;
    protected $userRepository;

    public function __construct(CaseRepository $caseRepository, UserRepository $userRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get dashboard statistics for laboratory
     */
    public function getDashboardStats($laboratoryId)
    {
        try {
            $cases = CasePatient::where('laboratory_id', $laboratoryId)
                ->whereIn('status', ['in_production','shipped'])
                ->whereHas('weTransferNotifications')
                ->get();
            
            $stats = [
                'total_cases' => $cases->count(),
                'status_in_production' => $cases->where('status', 'in_production')->count(),
                'status_shipped' => $cases->where('status', 'shipped')->count(),
                'status_pending' => 0, // Not relevant for laboratory production workflow
                'status_draft' => 0, // Not relevant for laboratory production workflow
                'status_in_planning' => 0, // Not relevant for laboratory production workflow  
                'status_approval' => 0, // Not relevant for laboratory production workflow
                'status_rejected' => 0, // Not relevant for laboratory production workflow
                'new_cases' => $cases->where('created_at', '>=', now()->subDays(30))->where('status', 'in_production')->count(),
                'case_retarded' => 0, // No "pending" cases in laboratory production workflow
                'case_retarded_percentage' => $cases->count() > 0
                    ? ($cases->where('status', 'in_production')->count() / $cases->count()) * 100
                    : 0,
            ];

            // Get monthly statistics
            $stats['monthly_totals'] = $this->getMonthlyStatistics($laboratoryId);

            return $stats;
        } catch (Exception $e) {
            Log::error('Error getting laboratory dashboard stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve dashboard statistics');
        }
    }

    /**
     * Get monthly case statistics
     */
    public function getMonthlyStatistics($laboratoryId)
    {
        try {
            $cases_by_month = CasePatient::where('laboratory_id', $laboratoryId)
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

            return $monthly_totals;
        } catch (Exception $e) {
            Log::error('Error getting laboratory monthly statistics: ' . $e->getMessage());
            throw new Exception('Failed to retrieve monthly statistics');
        }
    }

    /**
     * Get laboratory's cases with pagination and filters
     */
    public function getCases($laboratoryId, array $filters = [])
    {
        try {
            $query = CasePatient::where('laboratory_id', $laboratoryId)
                ->whereIn('status', ['in_production','shipped'])
                ->whereHas('weTransferNotifications')
                ->with(['patient', 'weTransferNotifications']);

            // Apply filters
            if (isset($filters['case_id'])) {
                $query->where('case_id', 'like', '%' . $filters['case_id'] . '%');
            }

            if (isset($filters['patient_id'])) {
                $query->where('patient_id', $filters['patient_id']);
            }

            if (isset($filters['treatment_type'])) {
                $query->where('treatment_type', $filters['treatment_type']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            return $query->latest()->get();
        } catch (Exception $e) {
            Log::error('Error getting laboratory cases: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases');
        }
    }

    /**
     * Get case with all related data for display
     */
    public function getCaseDetails($caseId, $laboratoryId)
    {
        try {
            $case = CasePatient::where('id', $caseId)
                ->where('laboratory_id', $laboratoryId)
                ->with(['weTransferNotifications.technician', 'latestWeTransferNotification.technician', 'patient', 'doctor', 'technician', 'laboratory'])
                ->firstOrFail();

            return $case;
        } catch (Exception $e) {
            Log::error('Error getting case details: ' . $e->getMessage());
            throw new Exception('Case not found or access denied');
        }
    }

    /**
     * Update case status
     */
    public function updateCaseStatus($caseId, $status, $laboratoryId)
    {
        DB::beginTransaction();
        try {
            $case = CasePatient::where('id', $caseId)
                ->where('laboratory_id', $laboratoryId)
                ->firstOrFail();

            $case->update(['status' => $status]);

            // Create notification
            $notification = Notification::create([
                'case_id' => $case->id,
                'title' => $case->case_id . ' - ' . __('master.case_status_updated'),
                'message' => $case->case_id . ' - ' . __('master.case_status_updated'),
                'type' => 'case',
                'status' => $status,
                'doctor_id' => $case->doctor_id,
                'technician_id' => $case->technician_id,
                'laboratory_id' => $case->laboratory_id,
            ]);

            // Send notification email to doctor and technician
            if ($case->doctor) {
                Mail::to($case->doctor->email)->send(new SendNotification($notification));
            }
            if ($case->technician) {
                Mail::to($case->technician->email)->send(new SendNotification($notification));
            }

            // Send notification email to all admin users
            $adminUsers = User::where('role_id', 1)->where('status', 'active')->get();
            foreach ($adminUsers as $admin) {
                Mail::to($admin->email)->send(new SendNotification($notification));
            }
            

            DB::commit();
            return $case;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating case status: ' . $e->getMessage());
            throw new Exception('Failed to update case status');
        }
    }

    /**
     * Add comment to a case
     */
    public function addCaseComment($caseId, $laboratoryId, $comment)
    {
        try {
            // Verify case belongs to laboratory
            $case = CasePatient::where('id', $caseId)
                ->where('laboratory_id', $laboratoryId)
                ->firstOrFail();

            $commentRecord = Comment::create([
                'case_id' => $caseId,
                'comment' => $comment,
                'user_id' => $laboratoryId,
                'type' => 'laboratory_update'
            ]);

            // Load user relationship
            $commentRecord->load('user');

            return $commentRecord;
        } catch (Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            throw new Exception('Failed to add comment');
        }
    }

    /**
     * Get patients for laboratory with case data
     */
    public function getPatients($laboratoryId)
    {
        try {
            return CasePatient::select('patient_id','case_id','doctor_id','id')
                ->where('laboratory_id', $laboratoryId)
                ->whereIn('status', ['in_production','shipped'])
                ->whereHas('weTransferNotifications')
                ->whereNotNull('patient_id')
                ->groupBy('patient_id')
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting laboratory patients: ' . $e->getMessage());
            throw new Exception('Failed to retrieve patients');
        }
    }
}
