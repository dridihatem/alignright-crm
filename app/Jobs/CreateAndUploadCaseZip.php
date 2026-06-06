<?php

namespace App\Jobs;

use App\Models\CasePatient;
use App\Models\FileUpload;
use App\Models\User;
use App\Services\ZipCompressionService;
use App\Providers\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Exception;

class CreateAndUploadCaseZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $caseId;
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
    public function __construct(int $caseId, int $userId)
    {
        $this->caseId = $caseId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(ZipCompressionService $zipService, GoogleDriveService $googleDriveService): void
    {
        try {
            Log::info('Starting ZIP creation and upload job', [
                'case_id' => $this->caseId,
                'user_id' => $this->userId,
                'job_id' => $this->job->getJobId()
            ]);

            // Get case and user
            $case = CasePatient::find($this->caseId);
            $user = User::find($this->userId);

            if (!$case) {
                throw new Exception('Case not found: ' . $this->caseId);
            }

            if (!$user) {
                throw new Exception('User not found: ' . $this->userId);
            }

            // Update case status to indicate ZIP processing
            $case->update(['zip_status' => 'processing']);

            // Get all file uploads for this case
            $fileUploads = FileUpload::where('case_id', $this->caseId)->get();

            if ($fileUploads->isEmpty()) {
                Log::info('No files found for case ZIP creation', ['case_id' => $this->caseId]);
                $case->update(['zip_status' => 'no_files']);
                return;
            }

            Log::info('Found files for ZIP creation', [
                'case_id' => $this->caseId,
                'file_count' => $fileUploads->count()
            ]);

            // Create ZIP from temporary files
            $zipFilePath = $zipService->createZipFromTempFiles($fileUploads->toArray(), $case->case_id);

            // Create UploadedFile object for Google Drive upload
            $zipFile = new UploadedFile(
                $zipFilePath,
                basename($zipFilePath),
                'application/zip',
                null,
                true // test mode
            );

            Log::info('ZIP file created, starting Google Drive upload', [
                'case_id' => $this->caseId,
                'zip_path' => $zipFilePath,
                'zip_size' => filesize($zipFilePath)
            ]);

            // Upload ZIP to Google Drive
            $uploadResult = $googleDriveService->uploadForUser(
                $zipFile,
                $user,
                null, // no sharing email
                "Cases", // main folder
                $case->case_id // case subfolder
            );

            // Update case with ZIP information
            $case->update([
                'zip_status' => 'completed',
                'zip_google_drive_id' => $uploadResult['id'],
                'zip_google_drive_link' => $uploadResult['webViewLink'],
                'zip_created_at' => now()
            ]);

            Log::info('ZIP uploaded to Google Drive successfully', [
                'case_id' => $this->caseId,
                'google_drive_id' => $uploadResult['id'],
                'web_view_link' => $uploadResult['webViewLink']
            ]);

            // Clean up temporary ZIP file
            $zipService->cleanupZipFile($zipFilePath);

            // Update individual file uploads to indicate they're part of ZIP
            FileUpload::where('case_id', $this->caseId)
                     ->update(['included_in_zip' => true]);

            Log::info('ZIP creation and upload job completed successfully', [
                'case_id' => $this->caseId,
                'job_id' => $this->job->getJobId()
            ]);

        } catch (Exception $e) {
            Log::error('ZIP creation and upload job failed', [
                'case_id' => $this->caseId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update case status to failed
            if (isset($case)) {
                $case->update(['zip_status' => 'failed']);
            }

            // Clean up any temporary files
            if (isset($zipFilePath) && file_exists($zipFilePath)) {
                try {
                    unlink($zipFilePath);
                } catch (Exception $cleanupError) {
                    Log::warning('Failed to cleanup ZIP file after error', [
                        'zip_path' => $zipFilePath,
                        'cleanup_error' => $cleanupError->getMessage()
                    ]);
                }
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('ZIP creation job permanently failed', [
            'case_id' => $this->caseId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update case to indicate ZIP creation failed
        $case = CasePatient::find($this->caseId);
        if ($case) {
            $case->update(['zip_status' => 'failed']);
        }
    }
}
