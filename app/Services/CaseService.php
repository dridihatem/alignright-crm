<?php

namespace App\Services;

use App\Models\CasePatient;
use App\Models\Patient;
use App\Models\User;
use App\Models\ToothProblemCase;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\FileUpload;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\WeTransferNotification;
use App\Providers\GoogleDriveService;
use App\Services\ImageProcessingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CaseAssignedNotification;
use App\Mail\NewCaseForPricingNotification;
use App\Mail\PriceAcceptedNotification;
use App\Mail\TreatmentPlanReadyNotification;
use Exception;
use Illuminate\Http\UploadedFile;

class CaseService
{
    protected $googleDriveService;
    protected $imageProcessingService;

    public function __construct(GoogleDriveService $googleDriveService, ImageProcessingService $imageProcessingService)
    {
        $this->googleDriveService = $googleDriveService;
        $this->imageProcessingService = $imageProcessingService;
    }

    /**
     * Get dashboard statistics for cases
     */
    public function getDashboardStats()
    {
        try {
            return [
                'status_draft' => CasePatient::where('status', 'draft')->count(),
                'status_pending' => CasePatient::where('status', 'pending')->count(),
                'status_in_planning' => CasePatient::where('status', 'in_planning')->count(),
                'status_approval' => CasePatient::where('status', 'approval')->count(),
                'status_in_production' => CasePatient::where('status', 'in_production')->count(),
                'status_shipped' => CasePatient::where('status', 'shipped')->count(),
                'status_rejected' => CasePatient::where('status', 'rejected')->count(),
                'new_cases' => CasePatient::count(),
                'total_cases' => CasePatient::count(),
                'total_doctors' => User::where('role_id', 2)->count(),
                'total_technicians' => User::where('role_id', 3)->count(),
                'total_laboratories' => User::where('role_id', 4)->count(),
                'count_patient' => Patient::count(),
                'count_cases' => CasePatient::count(),
                'case_retarded_percentage' => $this->calculateRetardedPercentage(),
                'monthly_totals' => $this->getMonthlyTotals(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting dashboard stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve dashboard statistics');
        }
    }

    /**
     * Create a new case with patient
     */
    public function createCase(array $data, $doctorId)
    {
        DB::beginTransaction();
        try {
            // Handle patient creation or selection
            $patientId = $this->handlePatient($data);

            // Use provided case_id or generate one if not provided
            $caseId = $data['case_id'] ?? $this->generateCaseId();

            // Create case (without technician and laboratory - they will be assigned later)
            $case = CasePatient::create([
                'case_id' => $caseId,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'technician_id' => null, // Will be assigned after treatment plan acceptance
                'laboratory_id' => null, // Will be assigned after treatment plan acceptance
                'date' => $data['date'] ?? null,
                'time' => $data['time'] ?? null,
                'status' => 'pending',
                'doctor_instruction' => $data['doctor_instruction'] ?? null,
                'treatment_type' => $data['treatment_type'] ?? null,
                'treatment_overjet' => $data['treatment_overjet'] ?? null,
                'treatment_overbite' => $data['treatment_overbite'] ?? null,
                'treatment_midline' => $data['treatment_midline'] ?? null,
                'treatment_irp' => $data['treatment_irp'] ?? null,
                'treatment_attachments' => $data['treatment_attachments'] ?? null,
                'patient_chief_complaint' => $data['patient_chief_complaint'] ?? null,
                'type_of_scan' => $data['type_of_scan'] ?? null,
                'price' => $data['price'] ?? null,
                'advance_payment' => $data['advance_payment'] ?? null,
                'remaining_balance' => $data['remaining_balance'] ?? null,
                'wetransfer_link' => $data['wetransfer_link'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
            ]);

            // Handle tooth problems if provided
            if (isset($data['tooth_problems']) && is_array($data['tooth_problems'])) {
                $this->handleToothProblems($case->id, $data['tooth_problems']);
            }

            // Send notifications
            $this->sendCaseNotifications($case);

            // Send email notification to admin when case is submitted for pricing
            $this->sendNewCaseForPricingNotification($case);

            DB::commit();
            return $case;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating case: ' . $e->getMessage());
            throw new Exception('Failed to create case: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing case
     */
    public function updateCase($caseId, array $data, $doctorId = null)
    {
        DB::beginTransaction();
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check if user has permission to update this case
            if ($doctorId && $case->doctor_id !== $doctorId) {
                throw new Exception('Unauthorized to update this case');
            }

            $case->update($data);

            // Handle tooth problems if provided
            if (isset($data['tooth_problems']) && is_array($data['tooth_problems'])) {
                $this->handleToothProblems($case->id, $data['tooth_problems']);
            }

            DB::commit();
            return $case;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating case: ' . $e->getMessage());
            throw new Exception('Failed to update case: ' . $e->getMessage());
        }
    }

    /**
     * Change case status
     */
    public function changeCaseStatus($caseId, $newStatus, $userId = null)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Validate status transition
            if (!$this->isValidStatusTransition($case->status, $newStatus)) {
                throw new Exception('Invalid status transition from ' . $case->status . ' to ' . $newStatus);
            }

            $case->update(['status' => $newStatus]);

            // Send notifications for status change
            $this->sendStatusChangeNotification($case, $newStatus, $userId);

            return $case;

        } catch (Exception $e) {
            Log::error('Error changing case status: ' . $e->getMessage());
            throw new Exception('Failed to change case status: ' . $e->getMessage());
        }
    }

    /**
     * Assign technician to case
     */
    public function assignTechnician($caseId, $technicianId)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            $technician = User::where('id', $technicianId)
                             ->where('role_id', 3)
                             ->where('status', 'active')
                             ->firstOrFail();

            $case->update(['technician_id' => $technicianId]);

            // Send notification to technician
            $this->sendAssignmentNotification($case, $technician, 'technician');

            return $case;

        } catch (Exception $e) {
            Log::error('Error assigning technician: ' . $e->getMessage());
            throw new Exception('Failed to assign technician: ' . $e->getMessage());
        }
    }

    /**
     * Assign laboratory to case
     */
    public function assignLaboratory($caseId, $laboratoryId)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            $laboratory = User::where('id', $laboratoryId)
                             ->where('role_id', 4)
                             ->where('status', 'active')
                             ->firstOrFail();

            $case->update(['laboratory_id' => $laboratoryId]);

            // Send notification to laboratory
            $this->sendAssignmentNotification($case, $laboratory, 'laboratory');

            return $case;

        } catch (Exception $e) {
            Log::error('Error assigning laboratory: ' . $e->getMessage());
            throw new Exception('Failed to assign laboratory: ' . $e->getMessage());
        }
    }

    /**
     * Delete a case
     */
    public function deleteCase($caseId, $userId = null)
    {
        try {
            $case = CasePatient::findOrFail($caseId);
            
            // Check permissions
            if ($userId && $case->doctor_id !== $userId) {
                throw new Exception('Unauthorized to delete this case');
            }

            // Delete related records
            ToothProblemCase::where('case_id', $caseId)->delete();
            FileUpload::where('case_id', $caseId)->delete();
            if(Invoice::where('case_id', $caseId)->exists()){
            Invoice::where('case_id', $caseId)->delete();
            $invoice_id = Invoice::where('case_id', $caseId)->first()->id;
                if($invoice_id){
                Payment::where('invoice_id', $invoice_id)->delete();
                }
            }
            Comment::where('case_id', $caseId)->delete();
            Notification::where('case_id', $caseId)->delete();
            WeTransferNotification::where('case_id', $caseId)->delete();
            
            // Only delete patient if they are not associated with any other cases
            $patientId = $case->patient_id;
            $otherCasesCount = CasePatient::where('patient_id', $patientId)
                                        ->where('id', '!=', $caseId)
                                        ->count();
            
            if ($otherCasesCount === 0) {
                Patient::where('id', $patientId)->delete();
                Log::info('Patient deleted as no other cases exist', [
                    'patient_id' => $patientId,
                    'case_id' => $caseId
                ]);
            } else {
                Log::info('Patient not deleted as other cases exist', [
                    'patient_id' => $patientId,
                    'case_id' => $caseId,
                    'other_cases_count' => $otherCasesCount
                ]);
            }

            $case->delete();

            return true;

        } catch (Exception $e) {
            Log::error('Error deleting case: ' . $e->getMessage());
            throw new Exception('Failed to delete case: ' . $e->getMessage());
        }
    }

    /**
     * Get cases with filters for DataTables
     */
    public function getCasesForDataTable($request)
    {
        try {
            $query = CasePatient::with(['patient', 'doctor', 'technician', 'laboratory']);

            // Apply filters
            if ($request->filled('case_id')) {
                $query->where('case_id', 'like', '%' . $request->case_id . '%');
            }

            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            if ($request->filled('treatment_type')) {
                $query->where('treatment_type', $request->treatment_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('case_id', 'like', '%' . $searchValue . '%')
                      ->orWhere('treatment_type', 'like', '%' . $searchValue . '%')
                      ->orWhereHas('patient', function($patientQuery) use ($searchValue) {
                          $patientQuery->where('name', 'like', '%' . $searchValue . '%');
                      });
                });
            }

            return $query;

        } catch (Exception $e) {
            Log::error('Error getting cases for DataTable: ' . $e->getMessage());
            throw new Exception('Failed to retrieve cases');
        }
    }

    // Private helper methods

    private function handlePatient($data)
    {
        if ($data['patient_type'] === 'new') {
            // Check if patient already exists with the same email or reference
            $existingPatient = null;
            
            if (!empty($data['email'])) {
                $existingPatient = Patient::where('email', $data['email'])->first();
            }
            
            if (!$existingPatient && !empty($data['reference'])) {
                $existingPatient = Patient::where('reference', $data['reference'])->first();
            }
            
            if ($existingPatient) {
                Log::info('Patient already exists, using existing patient', [
                    'existing_patient_id' => $existingPatient->id,
                    'email' => $data['email'] ?? null,
                    'reference' => $data['reference'] ?? null
                ]);
                return $existingPatient->id;
            }
            
            // Create new patient if no duplicate found
            $patient = Patient::create([
                'reference' => $data['reference'],
                'name' => $data['name'],
                'surname' => $data['surname'],
                'gender' => $data['gender'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'country' => $data['country'],
                'birth_date' => $data['birth_day'] ?? null,
            ]);
            
            Log::info('New patient created successfully', [
                'patient_id' => $patient->id,
                'email' => $data['email'] ?? null,
                'reference' => $data['reference'] ?? null
            ]);
            
            return $patient->id;
        } else {
            return $data['patient_id'];
        }
    }

    private function handleToothProblems($caseId, $toothProblems)
    {
        // Delete existing tooth problems
        ToothProblemCase::where('case_id', $caseId)->delete();

        // Create new tooth problems - expecting format: tooth_problems[tooth_number][problem_id/notes]
        foreach ($toothProblems as $toothNumber => $data) {
            $problemId = $data['problem_id'] ?? null;
            $notes = $data['notes'] ?? null;
            
            if ($problemId) {
                ToothProblemCase::create([
                    'case_id' => $caseId,
                    'tooth_number' => $toothNumber,
                    'tooth_problem_id' => $problemId,
                    'tooth_notes' => $notes
                ]);
            }
        }
    }

    private function generateCaseId()
    {
        do {
            $candidate = 'AR-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (CasePatient::where('case_id', $candidate)->exists());

        return $candidate;
    }

    private function calculateRetardedPercentage()
    {
        $caseRetarded = CasePatient::where('status', 'pending')->count();
        $countCases = CasePatient::count();
        
        return $countCases > 0 ? number_format(($caseRetarded / $countCases) * 100, 2) : 0;
    }

    private function getMonthlyTotals()
    {
        $casesByMonth = CasePatient::where('created_at', '>=', now()->subDays(365))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyTotals = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyTotals[] = $casesByMonth[$i] ?? 0;
        }

        return $monthlyTotals;
    }

    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            'draft' => ['pending', 'in_planning'],
            'pending' => ['in_planning', 'rejected'],
            'in_planning' => ['approval', 'rejected'],
            'approval' => ['in_production', 'rejected'],
            'in_production' => ['shipped', 'rejected'],
            'shipped' => [],
            'rejected' => ['draft', 'pending'],
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    private function sendCaseNotifications($case)
    {
        // Send notifications to assigned technician and laboratory
        if ($case->technician_id) {
            $technician = User::find($case->technician_id);
            $this->sendAssignmentNotification($case, $technician, 'technician');
        }

        if ($case->laboratory_id) {
            $laboratory = User::find($case->laboratory_id);
            $this->sendAssignmentNotification($case, $laboratory, 'laboratory');
        }
    }

    private function sendAssignmentNotification($case, $user, $role)
    {
        try {
            Mail::to($user->email)->send(new CaseAssignedNotification($case, $user, $role));
        } catch (Exception $e) {
            Log::error('Failed to send assignment notification: ' . $e->getMessage());
        }
    }

    private function sendStatusChangeNotification($case, $newStatus, $userId)
    {
        // Implement status change notifications
        // This could send emails to relevant parties based on status change
    }

    /**
     * Process and upload files with compression
     */
    public function processAndUploadFiles($caseId, array $files, array $options = [])
    {
        try {
            $uploadedFiles = [];
            
            foreach ($files as $fieldName => $fileData) {
                if (is_array($fileData)) {
                    // Handle multiple files
                    $processedFiles = [];
                    foreach ($fileData as $file) {
                        if (str_contains($fieldName, 'scan')) {
                            // STL files - no processing needed
                            $processedFiles[] = $file;
                        } else {
                            // Image files - process with compression
                            $processedFiles[] = $this->imageProcessingService->processImage($file, $options);
                        }
                    }
                    $uploadedFiles[$fieldName] = $this->uploadFilesToGoogleDrive($processedFiles, $caseId, $fieldName);
                } else {
                    // Handle single file
                    if (str_contains($fieldName, 'scan')) {
                        // STL files - no processing needed
                        $processedFile = $fileData;
                    } else {
                        // Image files - process with compression
                        $processedFile = $this->imageProcessingService->processImage($fileData, $options);
                    }
                    $uploadedFiles[$fieldName] = $this->uploadFileToGoogleDrive($processedFile, $caseId, $fieldName);
                }
            }

            return $uploadedFiles;
        } catch (Exception $e) {
            Log::error('Error processing and uploading files: ' . $e->getMessage());
            throw new Exception('Failed to process and upload files: ' . $e->getMessage());
        }
    }

    /**
     * Upload single file to Google Drive or local storage
     */
    private function uploadFileToGoogleDrive(UploadedFile $file, $caseId, $fieldName)
    {
        try {
            // Set timeout for this operation
            set_time_limit(300); // 5 minutes
            
            // Get the case to get the case_id string
            $case = CasePatient::find($caseId);
            $caseIdString = $case ? $case->case_id : "CASE-{$caseId}";
            
            Log::info('Starting file upload process', [
                'case_id' => $caseId,
                'case_id_string' => $caseIdString,
                'field_name' => $fieldName,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
            ]);
            
            // Check if Google Drive is connected by checking user tokens
            $user = auth()->user();
            $googleDriveConnected = $user && $user->google_access_token && $user->google_refresh_token;
            
            if ($googleDriveConnected) {
                try {
                    // Try to upload to Google Drive with timeout
                    Log::info('Attempting Google Drive upload', [
                        'case_id' => $caseId,
                        'field_name' => $fieldName,
                        'file_name' => $file->getClientOriginalName()
                    ]);
                    
                    $uploadResult = $this->googleDriveService->uploadForUser(
                        $file, 
                        $user, 
                        null, 
                        "Cases", // Main cases folder
                        $caseIdString // Use the actual case_id string for the subfolder
                    );
                    
                    Log::info('Google Drive upload successful', [
                        'case_id' => $caseId,
                        'field_name' => $fieldName,
                        'google_drive_id' => $uploadResult['id']
                    ]);
                    
                    // Save file information to FileUpload table with Google Drive info
                    $fileUpload = \App\Models\FileUpload::create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $uploadResult['webViewLink'], // Store Google Drive web view link
                        'type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'url' => $uploadResult['webViewLink'], // Store Google Drive web view link
                        'case_id' => $caseId, // Store the numeric case ID for database relationships
                        'patient_id' => $case->patient_id ?? null,
                        'wich_rubrique' => $fieldName, // Store the field name
                        'storage_type' => 'google_drive' // Mark as Google Drive storage
                    ]);
                    
                    return [
                        'file_name' => $file->getClientOriginalName(),
                        'google_drive_id' => $uploadResult['id'],
                        'web_view_link' => $uploadResult['webViewLink'],
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'case_id' => $caseIdString,
                        'file_upload_id' => $fileUpload->id,
                        'storage_type' => 'google_drive'
                    ];
                    
                } catch (Exception $googleDriveError) {
                    Log::warning('Google Drive upload failed, falling back to local storage', [
                        'case_id' => $caseId,
                        'field_name' => $fieldName,
                        'error' => $googleDriveError->getMessage()
                    ]);
                    
                    // Fall through to local storage
                    $googleDriveConnected = false;
                }
            }
            
            // If Google Drive is not connected or failed, use local storage
            if (!$googleDriveConnected) {
                Log::info('Using local storage for file', [
                    'case_id' => $caseId,
                    'field_name' => $fieldName,
                    'file_name' => $file->getClientOriginalName()
                ]);
                
                // Create local storage directory if it doesn't exist
                $localPath = storage_path("app/public/cases/{$caseIdString}");
                if (!file_exists($localPath)) {
                    if (!mkdir($localPath, 0755, true)) {
                        throw new Exception("Failed to create local storage directory: {$localPath}");
                    }
                }
                
                // Generate unique filename
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $localPath . '/' . $fileName;
                
                // Move file to local storage with error handling
                if (!$file->move($localPath, $fileName)) {
                    throw new Exception("Failed to move file to local storage: {$filePath}");
                }
                
                // Generate public URL
                $publicUrl = asset("storage/cases/{$caseIdString}/{$fileName}");
                
                Log::info('Local storage upload successful', [
                    'case_id' => $caseId,
                    'field_name' => $fieldName,
                    'local_path' => $filePath,
                    'public_url' => $publicUrl
                ]);
                
                // Save file information to FileUpload table with local storage info
                $fileUpload = \App\Models\FileUpload::create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $filePath, // Store local file path
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'url' => $publicUrl, // Store public URL
                    'case_id' => $caseId, // Store the numeric case ID for database relationships
                    'patient_id' => $case->patient_id ?? null,
                    'wich_rubrique' => $fieldName, // Store the field name
                    'storage_type' => 'local' // Mark as local storage
                ]);
                
                return [
                    'file_name' => $file->getClientOriginalName(),
                    'local_path' => $filePath,
                    'public_url' => $publicUrl,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'case_id' => $caseIdString,
                    'file_upload_id' => $fileUpload->id,
                    'storage_type' => 'local'
                ];
            }
            
        } catch (Exception $e) {
            Log::error("Error uploading file: {$e->getMessage()}", [
                'case_id' => $caseId,
                'field_name' => $fieldName,
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception("Failed to upload file: {$e->getMessage()}");
        }
    }

    /**
     * Upload multiple files to Google Drive
     */
    private function uploadFilesToGoogleDrive(array $files, $caseId, $fieldName)
    {
        $uploadedFiles = [];
        
        foreach ($files as $file) {
            $uploadedFiles[] = $this->uploadFileToGoogleDrive($file, $caseId, $fieldName);
        }
        
        return $uploadedFiles;
    }

    /**
     * Send email notification to admin when new case needs pricing
     */
    private function sendNewCaseForPricingNotification($case)
    {
        try {
            // Get all admin users
            $admins = User::where('role_id', 1)->where('status', 'active')->get();
            
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new NewCaseForPricingNotification($case, $case->doctor));
            }
            
            Log::info('New case for pricing notification sent', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'doctor_id' => $case->doctor_id,
                'admin_count' => $admins->count()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send new case for pricing notification: ' . $e->getMessage());
        }
    }

    /**
     * Accept price and change status to approval
     */
    public function acceptPrice($caseId, $doctorId)
    {
        try {
            DB::beginTransaction();
            
            $case = CasePatient::with(['doctor', 'patient'])->findOrFail($caseId);
            
            // Check if case belongs to the doctor (cast to int to avoid type mismatch)
            if ((int)$case->doctor_id !== (int)$doctorId) {
                throw new Exception('Unauthorized to accept price for this case');
            }
            
            // Check if case has price set
            if (!$case->price) {
                throw new Exception('No price has been set for this case');
            }
            
            // Check if case is in valid status for price acceptance
            if (!in_array($case->status, ['pending', 'in_planning', 'approval'])) {
                throw new Exception('Case must be in pending, in_planning, or approval status to accept price');
            }
            
            // Check if price is already accepted or rejected
            if ($case->price_accepted_at || $case->price_rejected_at) {
                throw new Exception('Price has already been accepted or rejected');
            }
            
            // Update case status to in_production and mark price as accepted
            $case->update([
                'status' => 'in_production',
                'accepted_date' => now(),
                'price_accepted_at' => now(),
                'price_accepted_by' => $doctorId
            ]);
            
            // Create invoice with the proposal price as total amount
            $invoice = $this->createInvoice($case);
            
            // Create payment record if advance payment exists
            if ($case->advance_payment && $case->advance_payment > 0) {
                $this->createPayment($invoice, $case->advance_payment, 'advance_payment');
            }
            
            // Send notifications to all admins
            $this->sendPriceAcceptedNotification($case);
            $this->createAdminNotifications($case, 'price_accepted');
            
            Log::info('Price accepted successfully', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'doctor_id' => $doctorId,
                'price' => $case->price,
                'invoice_id' => $invoice->id
            ]);
            
            DB::commit();
            return $case;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error accepting price: ' . $e->getMessage());
            throw new Exception('Failed to accept price: ' . $e->getMessage());
        }
    }

    /**
     * Reject price for a case
     */
    public function rejectPrice($caseId, $doctorId, $reason)
    {
        try {
            DB::beginTransaction();
            
            $case = CasePatient::with(['doctor', 'patient'])->findOrFail($caseId);
            
            // Check if case belongs to the doctor (cast to int to avoid type mismatch)
            if ((int)$case->doctor_id !== (int)$doctorId) {
                throw new Exception('Unauthorized to reject price for this case');
            }
            
            // Check if case has price set
            if (!$case->price) {
                throw new Exception('No price has been set for this case');
            }
            
            // Check if case is in valid status for price rejection
            if (!in_array($case->status, ['pending', 'in_planning', 'approval'])) {
                throw new Exception('Case must be in pending, in_planning, or approval status to reject price');
            }
            
            // Check if price is already accepted or rejected
            if ($case->price_accepted_at || $case->price_rejected_at) {
                throw new Exception('Price has already been accepted or rejected');
            }
            
            // Update case status to rejected and mark price as rejected
            $case->update([
                'status' => 'rejected',
                'rejected_date' => now(),
                'price_rejected_at' => now(),
                'price_rejected_by' => $doctorId,
                'price_rejection_reason' => $reason
            ]);
            
            // Send notifications to all admins
            $this->sendPriceRejectedNotification($case, $reason);
            $this->createAdminNotifications($case, 'price_rejected', $reason);
            
            Log::info('Price rejected successfully', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'doctor_id' => $doctorId,
                'price' => $case->price,
                'reason' => $reason
            ]);
            
            DB::commit();
            return $case;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting price: ' . $e->getMessage());
            throw new Exception('Failed to reject price: ' . $e->getMessage());
        }
    }

    /**
     * Create invoice for a case when price is accepted
     */
    private function createInvoice($case)
    {
        $invoice = Invoice::create([
            'case_id' => $case->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'total_amount' => $case->price, // Use the proposal price as total amount
            'advance_payment' => 0, // Will be calculated from actual payments
            'remaining_balance' => $case->price, // Initially, remaining balance equals total
            'status' => Invoice::STATUS_PENDING,
            'due_date' => $case->estimated_completion_date ?? now()->addDays(30),
            'notes' => "Invoice for case #{$case->case_id} - Proposal price accepted"
        ]);
        
        return $invoice;
    }

    /**
     * Create payment record for advance payment
     */
    private function createPayment($invoice, $amount, $paymentMethod = 'advance_payment')
    {
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $paymentMethod,
            'notes' => 'Advance payment for case'
        ]);
        
        // Update invoice status after payment
        $invoice->updateStatus();
        
        return $payment;
    }

    /**
     * Send email notification when doctor accepts price
     */
    private function sendPriceAcceptedNotification($case)
    {
        try {
            // Get all admin users
            $admins = User::where('role_id', 1)->where('status', 'active')->get();
            
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new PriceAcceptedNotification(
                    $case, 
                    $case->doctor, 
                    $admin, 
                    $case->price
                ));
            }
            
            Log::info('Price accepted notification sent to all admins', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'admin_count' => $admins->count()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send price accepted notification: ' . $e->getMessage());
        }
    }

    /**
     * Accept treatment plan and change status to in_production
     */
    public function acceptTreatmentPlan($caseId, $doctorId)
    {
        try {
            $case = CasePatient::with(['doctor', 'patient', 'technician'])->findOrFail($caseId);
            
            // Check if case belongs to the doctor
            if ($case->doctor_id !== $doctorId) {
                throw new Exception('Unauthorized to accept treatment plan for this case');
            }
            
            // Check if case is in approval status
            if ($case->status !== 'approval') {
                throw new Exception('Case must be in approval status to accept treatment plan');
            }
            
            // Check if case has treatment plans
            $treatmentPlans = $case->treatmentType()->where('status', 'pending')->count();
            if ($treatmentPlans === 0) {
                throw new Exception('No treatment plans available for acceptance');
            }
            
            // Update case status to in_production
            $case->update(['status' => 'in_production']);
            
            // Update treatment plan status to accepted
            $case->treatmentType()->where('status', 'pending')->update([
                'status' => 'accepted',
                'accepted_by' => $doctorId,
                'accepted_at' => now()
            ]);
            
            Log::info('Treatment plan accepted successfully', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'doctor_id' => $doctorId
            ]);
            
            return $case;
            
        } catch (Exception $e) {
            Log::error('Error accepting treatment plan: ' . $e->getMessage());
            throw new Exception('Failed to accept treatment plan: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification when treatment plan is ready for review
     */
    public function sendTreatmentPlanReadyNotification($case, $technician)
    {
        try {
            Mail::to($case->doctor->email)->send(new TreatmentPlanReadyNotification(
                $case, 
                $case->doctor, 
                $technician
            ));
            
            Log::info('Treatment plan ready notification sent', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'doctor_id' => $case->doctor_id,
                'technician_id' => $technician->id
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send treatment plan ready notification: ' . $e->getMessage());
        }
    }

    /**
     * Get cases waiting for doctor price acceptance
     */
    public function getCasesWaitingForPriceAcceptance($doctorId = null)
    {
        $query = CasePatient::with(['patient', 'doctor'])
            ->where('status', 'pending')
            ->whereNotNull('price');
            
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }
        
        return $query->orderBy('price_added_at', 'desc')->get();
    }

    /**
     * Get cases waiting for treatment plan acceptance
     */
    public function getCasesWaitingForTreatmentPlanAcceptance($doctorId = null)
    {
        $query = CasePatient::with(['patient', 'doctor', 'technician'])
            ->where('status', 'approval')
            ->whereHas('treatmentType', function($q) {
                $q->where('status', 'pending');
            });
            
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }
        
        return $query->orderBy('updated_at', 'desc')->get();
    }

    /**
     * Send email notification when doctor rejects price
     */
    private function sendPriceRejectedNotification($case, $reason)
    {
        try {
            // Get all admin users
            $admins = User::where('role_id', 1)->where('status', 'active')->get();
            
            foreach ($admins as $admin) {
                // Create notification instance for email
                $notification = Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Price Rejected - ' . $case->case_id,
                    'message' => "Doctor has rejected the price for case {$case->case_id}. Reason: {$reason}",
                    'type' => 'price_rejected',
                    'data' => json_encode(['case_id' => $case->id, 'reason' => $reason])
                ]);
                
                Mail::to($admin->email)->send(new \App\Mail\SendNotification($notification));
            }
            
            Log::info('Price rejected notification sent to all admins', [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'admin_count' => $admins->count(),
                'reason' => $reason
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send price rejected notification: ' . $e->getMessage());
        }
    }

    /**
     * Create database notifications for all admins
     */
    private function createAdminNotifications($case, $type, $reason = null)
    {
        try {
            // Get all admin users
            $admins = User::where('role_id', 1)->where('status', 'active')->get();
            
            foreach ($admins as $admin) {
                $title = $type === 'price_accepted' ? 
                    'Price Accepted - ' . $case->case_id : 
                    'Price Rejected - ' . $case->case_id;
                
                $message = $type === 'price_accepted' ? 
                    "Doctor has accepted the price for case {$case->case_id}. Case is now in production." :
                    "Doctor has rejected the price for case {$case->case_id}. Reason: {$reason}";
                
                $data = [
                    'case_id' => $case->id,
                    'case_id_string' => $case->case_id
                ];
                
                if ($type === 'price_rejected' && $reason) {
                    $data['reason'] = $reason;
                }
                
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'data' => json_encode($data),
                    'status' => 'active'
                ]);
            }
            
            Log::info("Database notifications created for all admins", [
                'case_id' => $case->id,
                'case_id_string' => $case->case_id,
                'type' => $type,
                'admin_count' => $admins->count()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create admin notifications: ' . $e->getMessage());
        }
    }

}
