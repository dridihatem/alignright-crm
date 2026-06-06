<?php

namespace App\Services;

use App\Models\TreatmentType;
use App\Models\CasePatient;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotification;
use Exception;
use Vinkla\Hashids\Facades\Hashids;

class TreatmentPlanService
{
    protected $caseService;

    public function __construct(CaseService $caseService)
    {
        $this->caseService = $caseService;
    }

    /**
     * Create a treatment plan for a case
     */
    public function createTreatmentPlan(array $data, $caseId)
    {
        DB::beginTransaction();
        try {
            $treatmentPlan = TreatmentType::create([
                'name' => $data['name'],
                'link' => $data['link'],
                'status' => 'pending',
                'description' => $data['description'] ?? '',
                'case_id' => $caseId,
                'type_file' => $data['type_file'] ?? null,
            ]);

            DB::commit();
            return $treatmentPlan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to create treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Accept treatment plan (Doctor action)
     */
    public function acceptTreatmentPlan($treatmentPlanId, $doctorId)
    {
        DB::beginTransaction();
        try {
            $treatmentPlan = TreatmentType::findOrFail($treatmentPlanId);
            
            // Check if treatment plan is pending
            if (!$treatmentPlan->isPending()) {
                throw new Exception('Treatment plan is not in pending status');
            }

            $treatmentPlan->update([
                'status' => 'accepted',
                'accepted_by' => $doctorId,
                'accepted_at' => now()
            ]);

            // Update case status to approval (waiting for admin to add price)
            $case = CasePatient::findOrFail($treatmentPlan->case_id);
            $case->update(['status' => 'approval']);

            // Send notification to admin
            $this->sendAdminNotification($case, $treatmentPlan, 'accepted');

            DB::commit();
            return $treatmentPlan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error accepting treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to accept treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Reject treatment plan (Doctor action)
     */
    public function rejectTreatmentPlan($treatmentPlanId, $doctorId, $reason = null)
    {
        DB::beginTransaction();
        try {
            $treatmentPlan = TreatmentType::findOrFail($treatmentPlanId);
            
            // Check if treatment plan is pending
            if (!$treatmentPlan->isPending()) {
                throw new Exception('Treatment plan is not in pending status');
            }

            $treatmentPlan->update([
                'status' => 'rejected',
                'rejected_by' => $doctorId,
                'rejected_at' => now(),
                'rejection_reason' => $reason
            ]);

            // Update case status to rejected
            $case = CasePatient::findOrFail($treatmentPlan->case_id);
            $case->update(['status' => 'rejected']);

            // Send notification to technician
            $this->sendTechnicianNotification($case, $treatmentPlan, 'rejected');

            DB::commit();
            return $treatmentPlan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to reject treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Get treatment plans for a case
     */
    public function getTreatmentPlansForCase($caseId)
    {
        try {
            return TreatmentType::where('case_id', $caseId)
                               ->orderBy('created_at', 'desc')
                               ->get();
        } catch (Exception $e) {
            Log::error('Error getting treatment plans: ' . $e->getMessage());
            throw new Exception('Failed to retrieve treatment plans');
        }
    }

    /**
     * Get treatment plan by ID
     */
    public function getTreatmentPlanById($id)
    {
        try {
            return TreatmentType::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error getting treatment plan: ' . $e->getMessage());
            throw new Exception('Treatment plan not found');
        }
    }

    /**
     * Update treatment plan
     */
    public function updateTreatmentPlan($id, array $data)
    {
        try {
            $treatmentPlan = TreatmentType::findOrFail($id);
            $treatmentPlan->update($data);
            return $treatmentPlan;
        } catch (Exception $e) {
            Log::error('Error updating treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to update treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Delete treatment plan
     */
    public function deleteTreatmentPlan($id)
    {
        try {
            $treatmentPlan = TreatmentType::findOrFail($id);
            $treatmentPlan->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to delete treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for treatment plan status change
     */
    private function createTreatmentPlanNotification($case, $status, $reason = null)
    {
        try {
            $title = $case->case_id . ' - Treatment Plan ' . ucfirst($status);
            $message = $case->case_id . ' - Treatment plan has been ' . $status;
            
            if ($reason) {
                $message .= '. Reason: ' . $reason;
            }

            $notification = Notification::create([
                'case_id' => $case->id,
                'title' => $title,
                'message' => $message,
                'type' => 'treatment_plan',
                'status' => 'treatment_plan_' . $status,
                'doctor_id' => $case->doctor_id,
                'technician_id' => $case->technician_id,
                'laboratory_id' => $case->laboratory_id,
            ]);

            // Send email notifications if technician and laboratory are assigned
            if ($case->technician) {
                Mail::to($case->technician->email)->send(new SendNotification($notification));
            }
            
            if ($case->laboratory) {
                Mail::to($case->laboratory->email)->send(new SendNotification($notification));
            }

            return $notification;
        } catch (Exception $e) {
            Log::error('Error creating treatment plan notification: ' . $e->getMessage());
            // Don't throw exception here as it's not critical
        }
    }

    /**
     * Get treatment plan statistics for a doctor
     */
    public function getTreatmentPlanStats($doctorId)
    {
        try {
            $cases = CasePatient::where('doctor_id', $doctorId)->pluck('id');
            
            $stats = [
                'total_plans' => TreatmentType::whereIn('case_id', $cases)->count(),
                'pending_plans' => TreatmentType::whereIn('case_id', $cases)
                                               ->where('status', 'pending')
                                               ->count(),
                'accepted_plans' => TreatmentType::whereIn('case_id', $cases)
                                               ->where('status', 'accepted')
                                               ->count(),
                'rejected_plans' => TreatmentType::whereIn('case_id', $cases)
                                               ->where('status', 'rejected')
                                               ->count(),
            ];

            return $stats;
        } catch (Exception $e) {
            Log::error('Error getting treatment plan stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve treatment plan statistics');
        }
    }

    /**
     * Add price to case (Admin action)
     */
    public function addPriceToCase($caseId, $adminId, $price, $advancePayment = null, $estimatedCompletionDate = null)
    {
        DB::beginTransaction();
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check if case has any treatment plans
            $totalTreatmentPlans = $case->treatmentType()->count();
            if ($totalTreatmentPlans === 0) {
                throw new Exception('Case must have treatment plans before adding price');
            }
            
            // Check if ALL treatment plans are accepted
            $acceptedTreatmentPlans = $case->treatmentType()->where('status', 'accepted')->count();
            if ($acceptedTreatmentPlans !== $totalTreatmentPlans) {
                throw new Exception('All treatment plans must be accepted before adding price. Current: ' . $acceptedTreatmentPlans . '/' . $totalTreatmentPlans . ' accepted');
            }

            // Calculate remaining balance
            $remainingBalance = $advancePayment ? $price - $advancePayment : $price;

            $case->update([
                'price' => $price,
                'advance_payment' => $advancePayment,
                'remaining_balance' => $remainingBalance,
                'price_added_by' => $adminId,
                'price_added_at' => now(),
                'estimated_completion_date' => $estimatedCompletionDate,
                'status' => 'in_production'
            ]);

            // Determine invoice status based on payment
           /* $invoiceStatus = \App\Models\Invoice::STATUS_PENDING;
            if ($advancePayment == 0 || $advancePayment == $price) {
                // If no advance payment (full amount due) or advance payment equals total amount (fully paid)
                $invoiceStatus = \App\Models\Invoice::STATUS_PAID;
            }*/

            // Create invoice for the case
            $invoice = \App\Models\Invoice::create([
                'case_id' => $case->id,
                'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                'total_amount' => $price,
                'advance_payment' => $advancePayment ?? 0,
                'remaining_balance' => $remainingBalance,
                'status' => \App\Models\Invoice::STATUS_PENDING,
                'notes' => 'Price added by admin for case ' . $case->case_id
            ]);

            // Send notification to doctor
            $this->sendDoctorNotification($case, null, 'price_added');

            DB::commit();
            return $case;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error adding price to case: ' . $e->getMessage());
            throw new Exception('Failed to add price to case: ' . $e->getMessage());
        }
    }

    /**
     * Upload treatment plan file (Technician action)
     */
    public function uploadTreatmentPlan($caseId, $technicianId, $file, $description = null)
    {
        DB::beginTransaction();
        try {
            // Handle file upload to Google Drive
            $uploadResult = $this->caseService->uploadFileToGoogleDrive($file, $caseId, 'treatment_plans');
            
            $treatmentPlan = TreatmentType::create([
                'name' => 'Treatment Plan - ' . now()->format('Y-m-d H:i'),
                'link' => $uploadResult['web_view_link'],
                'status' => 'pending',
                'description' => $description,
                'case_id' => $caseId,
                'type_file' => $uploadResult['file_name'],
                'uploaded_by' => $technicianId
            ]);

            // Update case status to in_planning
            $case = CasePatient::findOrFail($caseId);
            $case->update(['status' => 'in_planning']);

            // Send notification to doctor
            $this->sendDoctorNotification($case, $treatmentPlan, 'uploaded');

            DB::commit();
            return $treatmentPlan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error uploading treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to upload treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to admin
     */
    private function sendAdminNotification($case, $treatmentPlan, $action)
    {
        try {
            $admins = \App\Models\User::where('role_id', 1)->get();
            
            foreach ($admins as $admin) {
                $notification = Notification::create([
                    'case_id' => $case->id,
                    'title' => 'Treatment Plan Accepted - ' . $case->case_id,
                    'message' => "Treatment plan for case {$case->case_id} has been accepted by doctor. Please add price to proceed.",
                    'type' => 'treatment_plan_admin',
                    'status' => 'pending',
                    'user_id' => $admin->id
                ]);

                Mail::to($admin->email)->send(new SendNotification($notification));
            }
        } catch (Exception $e) {
            Log::error('Error sending admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to technician
     */
    private function sendTechnicianNotification($case, $treatmentPlan, $action)
    {
        try {
            if ($treatmentPlan->technician) {
                $notification = Notification::create([
                    'case_id' => $case->id,
                    'title' => 'Treatment Plan Rejected - ' . $case->case_id,
                    'message' => "Treatment plan for case {$case->case_id} has been rejected by doctor. Reason: {$treatmentPlan->rejection_reason}",
                    'type' => 'treatment_plan_technician',
                    'status' => 'pending',
                    'user_id' => $treatmentPlan->technician->id
                ]);

                Mail::to($treatmentPlan->technician->email)->send(new SendNotification($notification));
            }
        } catch (Exception $e) {
            Log::error('Error sending technician notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to doctor
     */
    private function sendDoctorNotification($case, $treatmentPlan, $action)
    {
        try {
            $notification = Notification::create([
                'case_id' => $case->id,
                'title' => 'Treatment Plan Update - ' . $case->case_id,
                'message' => $action === 'uploaded' 
                    ? "New treatment plan uploaded for case {$case->case_id}. Please review and accept/reject."
                    : "Price has been added to treatment plan for case {$case->case_id}. Case is now in production.",
                'type' => 'treatment_plan_doctor',
                'status' => 'pending',
                'user_id' => $case->doctor_id
            ]);

            Mail::to($case->doctor->email)->send(new SendNotification($notification));
        } catch (Exception $e) {
            Log::error('Error sending doctor notification: ' . $e->getMessage());
        }
    }

    /**
     * Get cases with ALL treatment plans accepted waiting for price (Admin price manager)
     */
    public function getCasesWaitingForPricing()
    {
        try {
            return CasePatient::with(['patient', 'doctor', 'technician', 'treatmentType'])
                               ->whereHas('treatmentType') // Must have at least one treatment plan
                               ->whereNull('price')
                               ->where('status', 'approval')
                               ->orderBy('updated_at', 'desc')
                               ->get()
                               ->filter(function($case) {
                                   // Filter to only include cases where ALL treatment plans are accepted
                                   $totalTreatmentPlans = $case->treatmentType->count();
                                   $acceptedTreatmentPlans = $case->treatmentType->where('status', 'accepted')->count();
                                   return $totalTreatmentPlans > 0 && $acceptedTreatmentPlans === $totalTreatmentPlans;
                               });
        } catch (Exception $e) {
            Log::error('Error getting cases waiting for pricing: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases waiting for pricing');
        }
    }

    /**
     * Get cases with prices (Admin price manager)
     */
    public function getCasesWithPrices()
    {
        try {
            return CasePatient::with(['patient', 'doctor', 'technician', 'admin', 'treatmentType' => function($query) {
                                    $query->where('status', 'accepted');
                                }])
                               ->whereHas('treatmentType', function($query) {
                                   $query->where('status', 'accepted');
                               })
                               ->whereNotNull('price')
                               ->where('status', 'in_production')
                               ->orderBy('price_added_at', 'desc')
                               ->get();
        } catch (Exception $e) {
            Log::error('Error getting cases with prices: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases with prices');
        }
    }

    /**
     * Clean up orphaned treatment plans (those without valid cases)
     */
    public function cleanupOrphanedTreatmentPlans()
    {
        try {
            $orphanedCount = TreatmentType::whereNull('case_id')
                                        ->orWhereNotExists(function ($query) {
                                            $query->select(\DB::raw(1))
                                                  ->from('case_patients')
                                                  ->whereRaw('case_patients.id = treatment_types.case_id');
                                        })
                                        ->delete();

            Log::info("Cleaned up {$orphanedCount} orphaned treatment plans");
            return $orphanedCount;
        } catch (Exception $e) {
            Log::error('Error cleaning up orphaned treatment plans: ' . $e->getMessage());
            throw new Exception('Failed to clean up orphaned treatment plans');
        }
    }

    /**
     * Get cases with priced treatment plans waiting for doctor acceptance (NEW WORKFLOW)
     */
    public function getCasesWaitingForDoctorAcceptance()
    {
        try {
            return CasePatient::with(['patient', 'doctor', 'technician', 'treatmentType'])
                               ->whereHas('treatmentType', function($query) {
                                   $query->where('status', 'priced'); // Treatment plans with prices waiting for doctor
                               })
                               ->where('status', 'priced') // Cases in priced status
                               ->orderBy('updated_at', 'desc')
                               ->get();
        } catch (Exception $e) {
            Log::error('Error getting cases waiting for doctor acceptance: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases waiting for doctor acceptance');
        }
    }

    /**
     * Get cases with accepted treatment plans (NEW WORKFLOW)
     */
    public function getCasesWithAcceptedPlans()
    {
        try {
            return CasePatient::with(['patient', 'doctor', 'technician', 'treatmentType'])
                               ->whereHas('treatmentType', function($query) {
                                   $query->where('status', 'accepted'); // Treatment plans accepted by doctor
                               })
                               ->where('status', 'approval') // Cases in approval status (ready for production)
                               ->orderBy('updated_at', 'desc')
                               ->get();
        } catch (Exception $e) {
            Log::error('Error getting cases with accepted plans: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases with accepted plans');
        }
    }
}
