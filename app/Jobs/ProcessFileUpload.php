<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\CasePatient;
use App\Providers\GoogleDriveService;
use App\Services\ImageProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileUploadId;
    protected $userId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 1800; // 30 minutes

    /**
     * Create a new job instance.
     */
    public function __construct($fileUploadId, $userId)
    {
        $this->fileUploadId = $fileUploadId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleDriveService $googleDriveService, ImageProcessingService $imageProcessingService)
    {
        try {
            // Get the file upload record
            $fileUpload = FileUpload::find($this->fileUploadId);
            
            if (!$fileUpload) {
                Log::error('FileUpload record not found', ['id' => $this->fileUploadId]);
                return;
            }

            // Update status to processing
            $fileUpload->update(['status' => 'processing']);

            // Get the user
            $user = \App\Models\User::find($this->userId);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Get the case
            $case = CasePatient::find($fileUpload->case_id);
            if (!$case) {
                throw new Exception('Case not found');
            }

            // Check if temp file exists
            $tempPath = storage_path('app/temp_uploads/' . $fileUpload->temp_filename);
            if (!file_exists($tempPath)) {
                throw new Exception('Temporary file not found: ' . $tempPath);
            }

            Log::info('Processing file upload', [
                'file_upload_id' => $this->fileUploadId,
                'case_id' => $fileUpload->case_id,
                'rubrique' => $fileUpload->wich_rubrique,
                'original_name' => $fileUpload->file_name,
                'temp_file' => $tempPath,
                'file_size' => filesize($tempPath)
            ]);

            // Create a temporary UploadedFile object
            $tempFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                $fileUpload->file_name,
                mime_content_type($tempPath),
                null,
                true // test mode
            );

            // Process image if it's not an STL file
            if (!str_contains($fileUpload->wich_rubrique, 'scan')) {
                Log::info('Processing image file', ['file_name' => $fileUpload->file_name]);
                $tempFile = $imageProcessingService->processImage($tempFile, [
                    'quality' => 85,
                    'max_width' => 1920,
                    'max_height' => 1080
                ]);
            }

            // Upload to Google Drive
            Log::info('Starting Google Drive upload', [
                'file_name' => $fileUpload->file_name,
                'file_size' => $tempFile->getSize()
            ]);

            $uploadResult = $googleDriveService->uploadForUser(
                $tempFile,
                $user,
                null,
                "Cases",
                $case->case_id
            );

            // Update FileUpload record with Google Drive information
            $fileUpload->update([
                'google_drive_id' => $uploadResult['id'],
                'google_drive_link' => $uploadResult['webViewLink'],
                'file_path' => $uploadResult['webContentLink'],
                'status' => 'completed',
                'uploaded_at' => now()
            ]);

            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            Log::info('File upload completed successfully', [
                'file_upload_id' => $this->fileUploadId,
                'google_drive_id' => $uploadResult['id'],
                'case_id' => $fileUpload->case_id
            ]);

        } catch (Exception $e) {
            Log::error('File upload job failed', [
                'file_upload_id' => $this->fileUploadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update FileUpload record with error
            if (isset($fileUpload)) {
                $fileUpload->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            // Clean up temporary file on error
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }

            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception)
    {
        Log::error('File upload job failed permanently', [
            'file_upload_id' => $this->fileUploadId,
            'error' => $exception->getMessage()
        ]);

        // Update FileUpload record to reflect permanent failure
        $fileUpload = FileUpload::find($this->fileUploadId);
        if ($fileUpload) {
            $fileUpload->update([
                'status' => 'failed',
                'error_message' => 'Upload failed after ' . $this->tries . ' attempts: ' . $exception->getMessage()
            ]);
        }
    }
}

