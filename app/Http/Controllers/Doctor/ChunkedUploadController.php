<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\UploadSession;
use App\Models\FileChunk;
use App\Models\FileUpload;
use App\Models\CasePatient;
use App\Jobs\OptimizeUploadedFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Throwable;

class ChunkedUploadController extends Controller
{
    /**
     * Maximum file size (500MB)
     */
    const MAX_FILE_SIZE = 500 * 1024 * 1024;

    /**
     * Default chunk size (2MB)
     */
    const DEFAULT_CHUNK_SIZE = 2 * 1024 * 1024;

    /**
     * Maximum chunks per file
     */
    const MAX_CHUNKS = 500;

    /**
     * Allowed file types
     */
    const ALLOWED_TYPES = [
        // Images
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        // STL files
        'model/stl', 'application/sla', 'application/vnd.ms-pki.stl'
    ];

    /**
     * Allowed file extensions
     */
    const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'stl'
    ];

    /**
     * Debug endpoint to test controller loading
     */
    public function debug(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'ChunkedUploadController is working',
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Initialize a new chunked upload session
     */
    public function initializeUpload(Request $request): JsonResponse
    {
        try {
            // Basic error check
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], 401);
            }

            Log::info('Initialize upload request received', [
                'request_data' => $request->all(),
                'user_id' => auth()->id(),
                'case_id_from_request' => $request->get('case_id')
            ]);

            $validator = Validator::make($request->all(), [
                'case_id' => 'required|integer|exists:case_patients,id',
                'file_name' => 'required|string|max:255',
                'file_size' => 'required|integer|min:1|max:' . self::MAX_FILE_SIZE,
                'file_type' => 'required|string',
                'chunk_size' => 'integer|min:1024|max:' . (10 * 1024 * 1024), // 1KB to 10MB
                'file_category' => 'required|string|in:upper_scan,lower_scan,bite_scan,photo_clinic_01,photo_clinic_02,photo_clinic_03,photo_clinic_04,photo_clinic_05,photo_clinic_06,photo_clinic_07,photo_clinic_08,photo_radiographs,other_files'
            ]);

            if ($validator->fails()) {
                Log::warning('Chunked upload validation failed', [
                    'errors' => $validator->errors(),
                    'request_data' => $request->all(),
                    'user_id' => auth()->id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'details' => $validator->errors(),
                    'received_data' => $request->only(['case_id', 'file_name', 'file_size', 'file_type', 'file_category'])
                ], 422);
            }

            // Validate file type
            $fileExtension = strtolower(pathinfo($request->file_name, PATHINFO_EXTENSION));
            if (!in_array($fileExtension, self::ALLOWED_EXTENSIONS)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File type not allowed',
                    'allowed_types' => self::ALLOWED_EXTENSIONS
                ], 422);
            }

            // Validate case ownership
            $case = CasePatient::where('id', $request->case_id)
                              ->where('doctor_id', auth()->id())
                              ->first();

            if (!$case) {
                Log::warning('Case access denied', [
                    'case_id' => $request->case_id,
                    'user_id' => auth()->id(),
                    'available_cases' => CasePatient::where('doctor_id', auth()->id())->pluck('id')->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Case not found or access denied',
                    'case_id' => $request->case_id,
                    'user_id' => auth()->id()
                ], 403);
            }

            // Calculate chunks
            $chunkSize = $request->chunk_size ?? self::DEFAULT_CHUNK_SIZE;
            $totalChunks = ceil($request->file_size / $chunkSize);

            if ($totalChunks > self::MAX_CHUNKS) {
                return response()->json([
                    'success' => false,
                    'error' => 'File too large for chunked upload',
                    'max_chunks' => self::MAX_CHUNKS
                ], 422);
            }

            // Create upload session
            $session = UploadSession::create([
                'case_id' => $request->case_id,
                'user_id' => auth()->id(),
                'original_filename' => $request->file_name,
                'file_type' => $fileExtension,
                'mime_type' => $request->file_type,
                'total_size' => $request->file_size,
                'total_chunks' => $totalChunks,
                'chunk_size' => $chunkSize,
                'file_category' => $request->file_category,
                'status' => UploadSession::STATUS_PENDING,
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'started_at' => Carbon::now()->toISOString()
                ]
            ]);

            // Create chunk records
            for ($i = 0; $i < $totalChunks; $i++) {
                FileChunk::create([
                    'session_id' => $session->session_id,
                    'chunk_number' => $i,
                    'chunk_size' => ($i === $totalChunks - 1) 
                        ? $request->file_size - ($i * $chunkSize) // Last chunk size
                        : $chunkSize,
                    'status' => FileChunk::STATUS_PENDING
                ]);
            }

            // Create temp directory
            $tempDir = $session->getTempDirectoryPath();
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            Log::info('Chunked upload session initialized', [
                'session_id' => $session->session_id,
                'case_id' => $request->case_id,
                'file_name' => $request->file_name,
                'total_chunks' => $totalChunks,
                'total_size' => $request->file_size
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $session->session_id,
                'total_chunks' => $totalChunks,
                'chunk_size' => $chunkSize,
                'upload_url' => route('doctor.chunked-upload.upload-chunk'),
                'status_url' => route('doctor.chunked-upload.status', $session->session_id),
                'complete_url' => route('doctor.chunked-upload.complete', $session->session_id)
            ]);

        } catch (Exception $e) {
            Log::error('Failed to initialize chunked upload', [
                'error' => $e->getMessage(),
                'file_name' => $request->file_name ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to initialize upload session'
            ], 500);
        }
    }

    /**
     * Upload a single chunk - SIMPLIFIED FOR DEBUGGING
     */
    public function uploadChunk(Request $request): JsonResponse
    {
        // Log immediately to check if we reach this point
        error_log("CHUNK UPLOAD STARTED: " . json_encode([
            'session_id' => $request->input('session_id', 'none'),
            'chunk_number' => $request->input('chunk_number', 'none'),
            'has_file' => $request->hasFile('chunk_file')
        ]));
        
        // Return immediate success for testing
        return response()->json([
            'success' => true,
            'message' => 'Test response - no actual upload processed',
            'chunk_number' => $request->input('chunk_number', 0),
            'progress' => 50,
            'uploaded_chunks' => 1,
            'total_chunks' => 2,
            'is_complete' => false
        ]);
        
        // ORIGINAL CODE COMMENTED OUT FOR DEBUGGING
        /*
        // Increase memory limit for large chunks
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes
        
        // Log the request start for debugging
        Log::info('Chunk upload started', [
            'session_id' => $request->input('session_id'),
            'chunk_number' => $request->input('chunk_number'),
            'has_file' => $request->hasFile('chunk_file'),
            'memory_usage' => memory_get_usage(true) . ' bytes'
        ]);
        
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string|exists:upload_sessions,session_id',
                'chunk_number' => 'required|integer|min:0',
                'chunk_file' => 'required|file'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'details' => $validator->errors()
                ], 422);
            }

            // Get upload session
            $session = UploadSession::where('session_id', $request->session_id)
                                  ->where('user_id', auth()->id())
                                  ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'error' => 'Upload session not found or access denied'
                ], 403);
            }

            // Check if session is expired
            if ($session->isExpired()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Upload session has expired'
                ], 410);
            }

            // Get chunk record
            $chunk = FileChunk::where('session_id', $request->session_id)
                             ->where('chunk_number', $request->chunk_number)
                             ->first();

            if (!$chunk) {
                return response()->json([
                    'success' => false,
                    'error' => 'Chunk not found'
                ], 404);
            }

            // Check if chunk already uploaded
            if ($chunk->status === FileChunk::STATUS_UPLOADED) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chunk already uploaded',
                    'chunk_number' => $request->chunk_number,
                    'progress' => $session->getProgressPercentage()
                ]);
            }

            // Validate chunk size
            $uploadedFile = $request->file('chunk_file');
            $expectedSize = $chunk->chunk_size;
            $actualSize = $uploadedFile->getSize();

            if ($actualSize > $expectedSize + 1024) { // Allow 1KB tolerance
                return response()->json([
                    'success' => false,
                    'error' => 'Chunk size mismatch',
                    'expected' => $expectedSize,
                    'actual' => $actualSize
                ], 422);
            }

            // Mark session as started if this is the first chunk
            if ($session->status === UploadSession::STATUS_PENDING) {
                $session->markAsStarted();
            }

            // Save chunk to temp directory
            $tempDir = $session->getTempDirectoryPath();
            $chunkPath = $tempDir . '/chunk_' . $request->chunk_number;
            
            // Move uploaded chunk
            try {
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }
                
                $moved = $uploadedFile->move($tempDir, 'chunk_' . $request->chunk_number);
                if (!$moved) {
                    throw new Exception('Failed to move uploaded file');
                }
            } catch (Exception $moveError) {
                Log::error('Failed to move chunk file', [
                    'session_id' => $request->session_id,
                    'chunk_number' => $request->chunk_number,
                    'temp_dir' => $tempDir,
                    'error' => $moveError->getMessage()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to save chunk: ' . $moveError->getMessage()
                ], 500);
            }

            // Calculate chunk hash for integrity
            $chunkHash = md5_file($chunkPath);

            // Mark chunk as uploaded
            $chunk->markAsUploaded($chunkPath, $chunkHash);

            // Update session progress
            $session->updateProgress();

            Log::info('Chunk uploaded successfully', [
                'session_id' => $request->session_id,
                'chunk_number' => $request->chunk_number,
                'chunk_size' => $actualSize,
                'progress' => $session->getProgressPercentage()
            ]);

            return response()->json([
                'success' => true,
                'chunk_number' => $request->chunk_number,
                'progress' => $session->getProgressPercentage(),
                'uploaded_chunks' => $session->uploaded_chunks,
                'total_chunks' => $session->total_chunks,
                'is_complete' => $session->isComplete()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to upload chunk', [
                'session_id' => $request->session_id ?? 'unknown',
                'chunk_number' => $request->chunk_number ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true) . ' bytes',
                'memory_peak' => memory_get_peak_usage(true) . ' bytes'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to upload chunk: ' . $e->getMessage()
            ], 500);
        } catch (Throwable $t) {
            Log::critical('Fatal error in chunk upload', [
                'session_id' => $request->session_id ?? 'unknown',
                'chunk_number' => $request->chunk_number ?? 'unknown',
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString(),
                'memory_usage' => memory_get_usage(true) . ' bytes',
                'memory_peak' => memory_get_peak_usage(true) . ' bytes'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Fatal server error during chunk upload'
            ], 500);
        }
        */
    }

    /**
     * Get upload status
     */
    public function getStatus(string $sessionId): JsonResponse
    {
        try {
            $session = UploadSession::where('session_id', $sessionId)
                                  ->where('user_id', auth()->id())
                                  ->with('chunks')
                                  ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'error' => 'Upload session not found'
                ], 404);
            }

            $completedChunks = $session->chunks->where('status', FileChunk::STATUS_UPLOADED);
            $failedChunks = $session->chunks->where('status', FileChunk::STATUS_FAILED);
            $pendingChunks = $session->chunks->where('status', FileChunk::STATUS_PENDING);

            return response()->json([
                'success' => true,
                'session_id' => $session->session_id,
                'status' => $session->status,
                'progress' => $session->getProgressPercentage(),
                'uploaded_chunks' => $completedChunks->count(),
                'failed_chunks' => $failedChunks->count(),
                'pending_chunks' => $pendingChunks->count(),
                'total_chunks' => $session->total_chunks,
                'uploaded_size' => $session->getUploadedSize(),
                'total_size' => $session->total_size,
                'estimated_time_remaining' => $session->getEstimatedTimeRemaining(),
                'is_complete' => $session->isComplete(),
                'is_expired' => $session->isExpired(),
                'failed_chunk_numbers' => $failedChunks->pluck('chunk_number')->toArray(),
                'error_message' => $session->error_message
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get upload status', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get upload status'
            ], 500);
        }
    }

    /**
     * Complete the upload by assembling chunks
     */
    public function completeUpload(string $sessionId): JsonResponse
    {
        try {
            $session = UploadSession::where('session_id', $sessionId)
                                  ->where('user_id', auth()->id())
                                  ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'error' => 'Upload session not found'
                ], 404);
            }

            if (!$session->isComplete()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Upload not complete',
                    'progress' => $session->getProgressPercentage()
                ], 422);
            }

            if ($session->status === UploadSession::STATUS_COMPLETED) {
                return response()->json([
                    'success' => true,
                    'message' => 'Upload already completed',
                    'file_path' => $session->final_file_path
                ]);
            }

            // Mark as assembling
            $session->update(['status' => UploadSession::STATUS_ASSEMBLING]);

            // Assemble the file
            $finalPath = $this->assembleChunks($session);

            if (!$finalPath) {
                $session->markAsFailed('Failed to assemble chunks');
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to assemble file'
                ], 500);
            }

            // Mark session as completed
            $session->markAsCompleted($finalPath);

            // Create FileUpload record
            $fileUpload = $this->createFileUploadRecord($session, $finalPath);

            // Queue optimization job if needed
            if ($fileUpload->needsOptimization() || $fileUpload->isSTL()) {
                OptimizeUploadedFile::dispatch($fileUpload)->delay(now()->addSeconds(30));
                Log::info('Optimization job queued', [
                    'file_upload_id' => $fileUpload->id,
                    'file_type' => $fileUpload->type,
                    'needs_optimization' => $fileUpload->needsOptimization()
                ]);
            }

            // Clean up temp files
            $this->cleanupTempFiles($session);

            Log::info('Chunked upload completed successfully', [
                'session_id' => $sessionId,
                'final_path' => $finalPath,
                'file_upload_id' => $fileUpload->id
            ]);

            return response()->json([
                'success' => true,
                'file_upload_id' => $fileUpload->id,
                'file_path' => $finalPath,
                'file_size' => $session->total_size,
                'message' => 'Upload completed successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to complete upload', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to complete upload'
            ], 500);
        }
    }

    /**
     * Cancel an upload session
     */
    public function cancelUpload(string $sessionId): JsonResponse
    {
        try {
            $session = UploadSession::where('session_id', $sessionId)
                                  ->where('user_id', auth()->id())
                                  ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'error' => 'Upload session not found'
                ], 404);
            }

            $session->update(['status' => UploadSession::STATUS_CANCELLED]);
            $this->cleanupTempFiles($session);

            Log::info('Upload session cancelled', [
                'session_id' => $sessionId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload cancelled successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to cancel upload', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel upload'
            ], 500);
        }
    }

    /**
     * Assemble chunks into final file
     */
    private function assembleChunks(UploadSession $session): ?string
    {
        try {
            // Create final file path
            $fileName = Str::uuid() . '.' . $session->file_type;
            $finalPath = 'uploads/' . $session->case_id . '/' . $fileName;
            $fullPath = storage_path('app/' . $finalPath);

            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Open final file for writing
            $finalFile = fopen($fullPath, 'wb');
            if (!$finalFile) {
                return null;
            }

            // Get chunks in order
            $chunks = $session->chunks()
                            ->where('status', FileChunk::STATUS_UPLOADED)
                            ->orderBy('chunk_number')
                            ->get();

            // Assemble chunks
            foreach ($chunks as $chunk) {
                if (!$chunk->existsOnDisk()) {
                    fclose($finalFile);
                    unlink($fullPath);
                    return null;
                }

                // Verify chunk integrity
                if (!$chunk->verifyIntegrity()) {
                    Log::warning('Chunk integrity check failed', [
                        'session_id' => $session->session_id,
                        'chunk_number' => $chunk->chunk_number
                    ]);
                }

                $chunkData = file_get_contents($chunk->chunk_path);
                fwrite($finalFile, $chunkData);
            }

            fclose($finalFile);

            // Verify final file size
            $actualSize = filesize($fullPath);
            if ($actualSize !== $session->total_size) {
                Log::error('Final file size mismatch', [
                    'session_id' => $session->session_id,
                    'expected' => $session->total_size,
                    'actual' => $actualSize
                ]);
                unlink($fullPath);
                return null;
            }

            return $finalPath;

        } catch (Exception $e) {
            Log::error('Failed to assemble chunks', [
                'session_id' => $session->session_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create FileUpload record
     */
    private function createFileUploadRecord(UploadSession $session, string $filePath): FileUpload
    {
        return FileUpload::create([
            'name' => $session->original_filename,
            'path' => $filePath,
            'type' => $session->mime_type,
            'size' => $session->total_size,
            'case_id' => $session->case_id,
            'wich_rubrique' => $session->file_category,
            'status' => FileUpload::UPLOAD_STATUS_COMPLETED,
            'session_id' => $session->session_id,
            'is_chunked_upload' => true,
            'original_size' => $session->total_size,
            'file_hash' => md5_file(storage_path('app/' . $filePath)),
            'upload_started_at' => $session->started_at,
            'upload_completed_at' => $session->completed_at,
            'optimization_status' => $session->mime_type && str_starts_with($session->mime_type, 'image/') 
                ? FileUpload::OPTIMIZATION_PENDING 
                : FileUpload::OPTIMIZATION_COMPLETED
        ]);
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(UploadSession $session): void
    {
        try {
            $tempDir = $session->temp_directory;
            if ($tempDir && is_dir($tempDir)) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($tempDir);
            }
        } catch (Exception $e) {
            Log::warning('Failed to cleanup temp files', [
                'session_id' => $session->session_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}