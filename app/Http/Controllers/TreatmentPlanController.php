<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CasePatient;
use App\Models\TreatmentType;
use App\Models\Notification;
use App\Services\TreatmentPlanService;
use App\Providers\GoogleDriveService;
use App\Mail\SendNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class TreatmentPlanController extends Controller
{
    protected $googleDriveService;
    protected $treatmentPlanService;

    public function __construct(GoogleDriveService $googleDriveService, TreatmentPlanService $treatmentPlanService)
    {
        $this->googleDriveService = $googleDriveService;
        $this->treatmentPlanService = $treatmentPlanService;
    }

    /**
     * Upload treatment plan for a case (Technician action)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'case_id' => 'required|exists:case_patients,id',
            'treatment_plan_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            $case = CasePatient::findOrFail($request->case_id);
            
            // Check if user is technician and has permission
            if (auth()->user()->role_id !== 3) {
                return response()->json(['error' => 'Only technicians can upload treatment plans'], 403);
            }

            if ($case->technician_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized to upload treatment plan for this case'], 403);
            }

            $treatmentPlan = $this->treatmentPlanService->uploadTreatmentPlan(
                $request->case_id,
                auth()->user()->id,
                $request->file('treatment_plan_file'),
                $request->description
            );

            Log::info('Treatment plan uploaded successfully', [
                'case_id' => $case->id,
                'user_id' => auth()->user()->id,
                'treatment_plan_id' => $treatmentPlan->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment plan uploaded successfully',
                'data' => $treatmentPlan
            ]);

        } catch (Exception $e) {
            Log::error('Error uploading treatment plan', [
                'case_id' => $request->case_id,
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to upload treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View treatment plan for a case
     */
    public function view($caseId)
    {
        $case = CasePatient::findOrFail($caseId);
        
        // Check permissions
        if (!auth()->user()->isAdmin() && 
            !auth()->user()->isDoctor() && 
            !auth()->user()->isTechnician() && 
            !auth()->user()->isLaboratory()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'treatment_plan_file' => $case->treatment_plan_file,
                'treatment_plan_link' => $case->treatment_plan_link,
                'treatment_plan_google_drive_id' => $case->treatment_plan_google_drive_id,
                'uploaded_at' => $case->treatment_plan_uploaded_at,
                'uploaded_by' => $case->technician ? $case->technician->name : 'Unknown'
            ]
        ]);
    }

    /**
     * Notify admin about treatment plan upload
     */
    private function notifyAdmin($case, $uploadData)
    {
        try {
            // Create notification
            $notification = Notification::create([
                'user_id' => $case->doctor_id, // Notify the doctor
                'title' => 'Treatment Plan Uploaded',
                'message' => "Treatment plan has been uploaded for case {$case->case_id}",
                'type' => 'treatment_plan_uploaded',
                'data' => json_encode([
                    'case_id' => $case->id,
                    'case_number' => $case->case_id,
                    'technician_name' => auth()->user()->name,
                    'upload_data' => $uploadData
                ])
            ]);

            // Send email notification to admin
            $adminUsers = \App\Models\User::where('role_id', 1)->get(); // Admin role
            
            foreach ($adminUsers as $admin) {
                Mail::to($admin->email)->send(new SendNotification($notification));
            }

            Log::info('Admin notification sent for treatment plan upload', [
                'case_id' => $case->id,
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'case_id' => $case->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete treatment plan
     */
    public function delete($caseId)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check permissions
            if (auth()->user()->isTechnician() && $case->technician_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Clear treatment plan data
            $case->update([
                'treatment_plan_file' => null,
                'treatment_plan_link' => null,
                'treatment_plan_google_drive_id' => null,
                'treatment_plan_uploaded_at' => null
            ]);

            Log::info('Treatment plan deleted', [
                'case_id' => $case->id,
                'user_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment plan deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting treatment plan', [
                'case_id' => $caseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to delete treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept treatment plan (Doctor action)
     */
    public function accept(Request $request)
    {
        try {
            $request->validate([
                'treatment_plan_id' => 'required|exists:treatment_types,id'
            ]);

            $treatmentPlan = $this->treatmentPlanService->acceptTreatmentPlan(
                $request->treatment_plan_id,
                auth()->user()->id
            );
            
            // The service already handles case status update, so we don't need to do it here again
            
            // Send notifications (optional - the service might already handle this)
            try {
                $case = CasePatient::find($treatmentPlan->case_id);
                
                if ($case && $case->technician) {
                    $notification = Notification::create([
                        'user_id' => $case->technician->id,
                        'title' => 'Treatment Plan Accepted - ' . $case->case_id,
                        'message' => "Treatment plan for case {$case->case_id} has been accepted by doctor.",
                        'type' => 'treatment_plan_accepted',
                        'data' => json_encode(['treatment_plan_id' => $treatmentPlan->id, 'case_id' => $case->id])
                    ]);
                    Mail::to($case->technician->email)->send(new SendNotification($notification));
                }
                
                if ($case && $case->doctor) {
                    $notification = Notification::create([
                        'user_id' => $case->doctor->id,
                        'title' => 'Treatment Plan Accepted - ' . $case->case_id,
                        'message' => "You have accepted the treatment plan for case {$case->case_id}.",
                        'type' => 'treatment_plan_accepted',
                        'data' => json_encode(['treatment_plan_id' => $treatmentPlan->id, 'case_id' => $case->id])
                    ]);
                    Mail::to($case->doctor->email)->send(new SendNotification($notification));
                }
                
                // Send to admin users
                $admins = \App\Models\User::where('role_id', 1)->get();
                foreach ($admins as $admin) {
                    $notification = Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'Treatment Plan Accepted - ' . $case->case_id,
                        'message' => "Treatment plan for case {$case->case_id} has been accepted. Please add price to proceed.",
                        'type' => 'treatment_plan_accepted',
                        'data' => json_encode(['treatment_plan_id' => $treatmentPlan->id, 'case_id' => $case->id])
                    ]);
                    Mail::to($admin->email)->send(new SendNotification($notification));
                }
            } catch (Exception $mailException) {
                // Log mail error but don't fail the main operation
                Log::warning('Failed to send acceptance notification emails: ' . $mailException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Treatment plan accepted successfully',
                'data' => $treatmentPlan
            ]);

        } catch (Exception $e) {
            Log::error('Error accepting treatment plan: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject treatment plan (Doctor action)
     */
    public function reject(Request $request)
    {
        try {
            $request->validate([
                'treatment_plan_id' => 'required|exists:treatment_types,id',
                'rejection_reason' => 'nullable|string|max:500'
            ]);

            $treatmentPlan = $this->treatmentPlanService->rejectTreatmentPlan(
                $request->treatment_plan_id,
                auth()->user()->id,
                $request->rejection_reason
            );
            
            // Send notifications (optional - the service might already handle this)
            try {
                $case = CasePatient::find($treatmentPlan->case_id);
                
                if ($case && $case->technician) {
                    $notification = Notification::create([
                        'user_id' => $case->technician->id,
                        'title' => 'Treatment Plan Rejected - ' . $case->case_id,
                        'message' => "Treatment plan for case {$case->case_id} has been rejected. Reason: " . ($treatmentPlan->rejection_reason ?? 'No reason provided'),
                        'type' => 'treatment_plan_rejected',
                        'data' => json_encode(['treatment_plan_id' => $treatmentPlan->id, 'case_id' => $case->id])
                    ]);
                    Mail::to($case->technician->email)->send(new SendNotification($notification));
                }
                
                // Send to admin users
                $admins = \App\Models\User::where('role_id', 1)->get();
                foreach ($admins as $admin) {
                    $notification = Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'Treatment Plan Rejected - ' . $case->case_id,
                        'message' => "Treatment plan for case {$case->case_id} has been rejected. Reason: " . ($treatmentPlan->rejection_reason ?? 'No reason provided'),
                        'type' => 'treatment_plan_rejected',
                        'data' => json_encode(['treatment_plan_id' => $treatmentPlan->id, 'case_id' => $case->id])
                    ]);
                    Mail::to($admin->email)->send(new SendNotification($notification));
                }
            } catch (Exception $mailException) {
                // Log mail error but don't fail the main operation
                Log::warning('Failed to send rejection notification emails: ' . $mailException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Treatment plan rejected successfully',
                'data' => $treatmentPlan
            ]);

        } catch (Exception $e) {
            Log::error('Error rejecting treatment plan: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add price to treatment plan (Admin action)
     */
    public function addPrice(Request $request)
    {
        try {
            $request->validate([
                'treatment_plan_id' => 'required|exists:treatment_types,id',
                'price' => 'required|numeric|min:0'
            ]);

            $treatmentPlan = $this->treatmentPlanService->addPriceToTreatmentPlan(
                $request->treatment_plan_id,
                auth()->user()->id,
                $request->price
            );

            return response()->json([
                'success' => true,
                'message' => 'Price added to treatment plan successfully',
                'data' => $treatmentPlan
            ]);

        } catch (Exception $e) {
            Log::error('Error adding price to treatment plan: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending treatment plans for admin
     */
    public function pendingPlans()
    {
        try {
            $pendingPlans = TreatmentType::with(['case.patient', 'case.doctor', 'technician'])
                ->where('status', 'accepted')
                ->whereNull('price')
                ->orderBy('accepted_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pendingPlans
            ]);

        } catch (Exception $e) {
            Log::error('Error getting pending treatment plans: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
