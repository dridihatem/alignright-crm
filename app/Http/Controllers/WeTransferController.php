<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CasePatient;
use App\Models\Notification;
use App\Mail\SendNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class WeTransferController extends Controller
{
    /**
     * Add WeTransfer link to a case (for technicians)
     */
    public function addLink(Request $request, $caseId)
    {
        $request->validate([
            'wetransfer_link' => 'required|url|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check if user has permission (technician assigned to this case)
            if (auth()->user()->isTechnician() && $case->technician_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if case is in the right status (doctor has approved)
            if ($case->status !== CasePatient::STATUS_APPROVAL) {
                return response()->json(['error' => 'Case must be approved by doctor before adding WeTransfer link'], 400);
            }

            // Update case with WeTransfer link
            $case->update([
                'wetransfer_link' => $request->wetransfer_link,
                'status' => CasePatient::STATUS_IN_PRODUCTION
            ]);

            // Create notification for laboratory
            $this->notifyLaboratory($case, $request->wetransfer_link, $request->notes);

            Log::info('WeTransfer link added to case', [
                'case_id' => $case->id,
                'link' => $request->wetransfer_link,
                'user_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WeTransfer link added successfully',
                'data' => [
                    'case_id' => $case->id,
                    'wetransfer_link' => $request->wetransfer_link,
                    'status' => $case->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding WeTransfer link to case', [
                'case_id' => $caseId,
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to add WeTransfer link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update WeTransfer link for a case
     */
    public function updateLink(Request $request, $caseId)
    {
        $request->validate([
            'wetransfer_link' => 'required|url|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check if user has permission
            if (auth()->user()->isTechnician() && $case->technician_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Update case with new WeTransfer link
            $case->update([
                'wetransfer_link' => $request->wetransfer_link
            ]);

            // Notify laboratory about link update
            $this->notifyLaboratory($case, $request->wetransfer_link, $request->notes, 'updated');

            Log::info('WeTransfer link updated for case', [
                'case_id' => $case->id,
                'link' => $request->wetransfer_link,
                'user_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WeTransfer link updated successfully',
                'data' => [
                    'case_id' => $case->id,
                    'wetransfer_link' => $request->wetransfer_link
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating WeTransfer link for case', [
                'case_id' => $caseId,
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to update WeTransfer link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View WeTransfer link for a case (for laboratory)
     */
    public function viewLink($caseId)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check if user has permission (laboratory assigned to this case)
            if (auth()->user()->isLaboratory() && $case->laboratory_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if (!$case->wetransfer_link) {
                return response()->json(['error' => 'No WeTransfer link available for this case'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'case_id' => $case->id,
                    'wetransfer_link' => $case->wetransfer_link,
                    'status' => $case->status,
                    'technician' => $case->technician ? $case->technician->name : 'Unknown'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error viewing WeTransfer link for case', [
                'case_id' => $caseId,
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to view WeTransfer link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark case as shipped (for laboratory)
     */
    public function markShipped($caseId)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check if user has permission (laboratory assigned to this case)
            if (auth()->user()->isLaboratory() && $case->laboratory_id !== auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if case has WeTransfer link
            if (!$case->wetransfer_link) {
                return response()->json(['error' => 'Case must have a WeTransfer link before marking as shipped'], 400);
            }

            // Update case status to shipped
            $case->update([
                'status' => CasePatient::STATUS_SHIPPED
            ]);

            // Create notification for doctor and technician
            $this->notifyShipped($case);

            Log::info('Case marked as shipped', [
                'case_id' => $case->id,
                'laboratory_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Case marked as shipped successfully',
                'data' => [
                    'case_id' => $case->id,
                    'status' => $case->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking case as shipped', [
                'case_id' => $caseId,
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to mark case as shipped: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify laboratory about WeTransfer link
     */
    private function notifyLaboratory($case, $link, $notes = null, $action = 'added')
    {
        try {
            if (!$case->laboratory) {
                Log::warning('No laboratory assigned to case for WeTransfer notification', [
                    'case_id' => $case->id
                ]);
                return;
            }

            $notification = Notification::create([
                'user_id' => $case->laboratory_id,
                'title' => 'WeTransfer Link ' . ucfirst($action),
                'message' => "WeTransfer link has been {$action} for case {$case->case_id}",
                'type' => 'wetransfer_' . $action,
                'data' => json_encode([
                    'case_id' => $case->id,
                    'case_number' => $case->case_id,
                    'wetransfer_link' => $link,
                    'technician_name' => auth()->user()->name,
                    'notes' => $notes
                ])
            ]);

            // Send email notification
            Mail::to($case->laboratory->email)->send(new SendNotification($notification));

            Log::info('Laboratory notification sent for WeTransfer link ' . $action, [
                'case_id' => $case->id,
                'laboratory_id' => $case->laboratory_id,
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send laboratory notification', [
                'case_id' => $case->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify doctor and technician about shipped case
     */
    private function notifyShipped($case)
    {
        try {
            $usersToNotify = [];
            
            if ($case->doctor) {
                $usersToNotify[] = $case->doctor;
            }
            
            if ($case->technician) {
                $usersToNotify[] = $case->technician;
            }

            foreach ($usersToNotify as $user) {
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Case Shipped',
                    'message' => "Case {$case->case_id} has been marked as shipped by the laboratory",
                    'type' => 'case_shipped',
                    'data' => json_encode([
                        'case_id' => $case->id,
                        'case_number' => $case->case_id,
                        'laboratory_name' => auth()->user()->name
                    ])
                ]);

                // Send email notification
                Mail::to($user->email)->send(new SendNotification($notification));
            }

            Log::info('Shipped notifications sent', [
                'case_id' => $case->id,
                'laboratory_id' => auth()->user()->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send shipped notifications', [
                'case_id' => $case->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
