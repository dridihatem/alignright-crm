<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use App\Models\CasePatient;
use App\Models\Setting;
use App\Jobs\OptimizeUploadedFile;
use App\Providers\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UppyUploadController extends Controller
{
    private const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB
    private const MAX_FILE_SIZE = 500 * 1024 * 1024; // 500MB
    
    /**
     * Test route to check if Uppy endpoints are accessible
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'Uppy upload controller is accessible',
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
    }

    /**
     * Handle CORS preflight requests
     */
    public function options(Request $request)
    {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-Case-ID, X-File-Type, X-Storage-Preference, Upload-Length, Upload-Metadata, Tus-Resumable, Upload-Offset')
            ->header('Access-Control-Max-Age', '86400');
    }

    /**
     * Handle file upload (supports both XHR and TUS)
     */
    public function create(Request $request)
    {
        // Optimize for large file uploads and prevent timeouts
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 0); // No time limit for uploads
        ini_set('max_input_time', 0);     // No input time limit
        set_time_limit(0);                // No script timeout
        ignore_user_abort(true);          // Continue even if user disconnects
        
        try {
            // Enhanced debugging
            Log::info('Uppy upload request detailed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type'),
                'upload_length' => $request->header('Upload-Length'),
                'tus_resumable' => $request->header('Tus-Resumable'),
                'has_files' => !empty($request->allFiles()),
                'all_files' => $request->allFiles(),
                'file_keys' => array_keys($request->allFiles()),
                'case_id_header' => $request->header('X-Case-ID'),
                'file_type_header' => $request->header('X-File-Type'),
                'storage_preference_header' => $request->header('X-Storage-Preference'),
                'doctor_id' => auth()->id(),
                'user_authenticated' => auth()->check(),
                'request_size' => $request->header('Content-Length'),
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'all_input' => $request->all(),
                'query_params' => $request->query(),
                'post_data' => $request->post(),
                'json_data' => $request->json()->all() ?? null
            ]);

            // Check authentication first
            if (!auth()->check()) {
                Log::error('User not authenticated for Uppy upload');
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $caseId = $request->header('X-Case-ID');
            
            if (!$caseId) {
                Log::warning('Missing Case-ID header', [
                    'all_headers' => $request->headers->all()
                ]);
                return response()->json(['error' => 'Missing Case-ID header'], 400);
            }

            // Verify case belongs to user
            $case = CasePatient::where('id', $caseId)
                              ->where('doctor_id', auth()->id())
                              ->first();

            if (!$case) {
                Log::warning('Case not found', ['case_id' => $caseId, 'doctor_id' => auth()->id()]);
                return response()->json(['error' => 'Case not found or access denied'], 404);
            }

            // Handle TUS upload creation (for large files)
            if ($request->header('Upload-Length')) {
                return $this->handleTusCreation($request, $caseId);
            }
            
            // Handle regular XHR upload (for small files)
            if ($request->hasFile('file')) {
                Log::info('Detected XHR file upload', [
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'file_size' => $request->file('file')->getSize(),
                    'mime_type' => $request->file('file')->getMimeType()
                ]);
                return $this->handleXhrUpload($request, $caseId);
            }

            // Check for any files with different field names
            $allFiles = $request->allFiles();
            if (!empty($allFiles)) {
                Log::warning('Files detected but not in expected field "file"', [
                    'file_fields' => array_keys($allFiles),
                    'file_details' => collect($allFiles)->map(function($file, $field) {
                        return [
                            'field' => $field,
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize()
                        ];
                    })
                ]);
                
                // Try to handle the first file found
                $firstFile = reset($allFiles);
                if ($firstFile && $firstFile->isValid()) {
                    Log::info('Attempting to process file from non-standard field');
                    // Temporarily set the file in the request as 'file'
                    $request->files->set('file', $firstFile);
                    return $this->handleXhrUpload($request, $caseId);
                }
            }

            Log::warning('No valid upload method detected', [
                'has_files' => $request->hasFile('file'),
                'files_count' => count($request->allFiles()),
                'upload_length' => $request->header('Upload-Length'),
                'content_type' => $request->header('Content-Type'),
                'all_file_fields' => array_keys($allFiles),
                'request_method' => $request->method(),
                'content_length' => $request->header('Content-Length')
            ]);
            return response()->json(['error' => 'No file or upload metadata found'], 400);

        } catch (\Exception $e) {
            Log::error('Uppy upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage(),
                'details' => app()->environment('local') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Handle TUS upload creation
     */
    private function handleTusCreation(Request $request, string $caseId)
    {
        $uploadLength = $request->header('Upload-Length');
        $uploadMetadata = $request->header('Upload-Metadata');

        if ($uploadLength > self::MAX_FILE_SIZE) {
            return response('File too large', 413);
        }

        // Parse metadata
        $metadata = $this->parseUploadMetadata($uploadMetadata);
        
        // Get file type from header (more reliable than metadata)
        $fileType = $request->header('X-File-Type', 'other_file');
        
        Log::info('TUS upload creation', [
            'case_id' => $caseId,
            'file_type_header' => $fileType,
            'metadata' => $metadata,
            'upload_length' => $uploadLength
        ]);
        
        // Generate unique upload ID
        $uploadId = Str::uuid();
        
        // Create upload session directory
        $uploadDir = "uploads/temp/{$uploadId}";
        Storage::makeDirectory($uploadDir);

        // Store upload metadata
        $uploadData = [
            'upload_id' => $uploadId,
            'case_id' => $caseId,
            'doctor_id' => auth()->id(),
            'file_name' => $metadata['filename'] ?? 'unknown',
            'file_size' => $uploadLength,
            'file_type' => $fileType, // Use file type from header
            'mime_type' => $metadata['filetype'] ?? 'application/octet-stream',
            'upload_length' => $uploadLength,
            'upload_offset' => 0,
            'created_at' => now(),
            'chunks_received' => 0,
            'status' => 'uploading'
        ];

        // Store in cache
        cache()->put("tus_upload_{$uploadId}", $uploadData, now()->addHours(24));

        Log::info('TUS upload created', [
            'upload_id' => $uploadId,
            'file_name' => $metadata['filename'] ?? 'unknown',
            'file_size' => $uploadLength
        ]);

        return response('', 201)
            ->header('Upload-Offset', '0')
            ->header('Location', "/doctor/uppy/upload/{$uploadId}")
            ->header('Tus-Resumable', '1.0.0');
    }

    /**
     * Handle regular XHR upload
     */
    private function handleXhrUpload(Request $request, string $caseId)
    {
        // Validate file upload
        $file = $request->file('file');
        
        if (!$file) {
            Log::warning('No file found in request', [
                'all_files' => $request->allFiles(),
                'file_keys' => array_keys($request->allFiles())
            ]);
            return response()->json(['error' => 'No file provided'], 400);
        }
        
        if (!$file->isValid()) {
            Log::warning('Invalid file upload', [
                'error' => $file->getErrorMessage(),
                'error_code' => $file->getError()
            ]);
            return response()->json(['error' => 'Invalid file upload: ' . $file->getErrorMessage()], 400);
        }
        
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            Log::warning('File too large', [
                'file_size' => $file->getSize(),
                'max_size' => self::MAX_FILE_SIZE
            ]);
            return response()->json(['error' => 'File too large'], 413);
        }

        // Get storage preference and file type
        $storagePreference = $request->header('X-Storage-Preference', 'local');
        $fileType = $request->header('X-File-Type', 'other');
        $googleDriveEnabled = Setting::getValue('google_drive_enabled', '0') == '1';
        
        Log::info('Upload preferences', [
            'storage_preference' => $storagePreference,
            'file_type' => $fileType,
            'google_drive_enabled' => $googleDriveEnabled
        ]);

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
        
        // Determine storage method
        $shouldUseGoogleDrive = $googleDriveEnabled && $storagePreference === 'google_drive';
        
        $filePath = null;
        $googleDriveId = null;
        $googleDriveLink = null;
        
        if ($shouldUseGoogleDrive) {
            try {
                // Upload to Google Drive
                $googleDriveService = new GoogleDriveService();
                $user = auth()->user();
                
                // Create folder structure: Cases/{CaseID}/{FileType}/
                $casesFolder = "Cases/{$caseId}";
                $patientId = CasePatient::find($caseId)->patient_id ?? null;
                
                $uploadResult = $googleDriveService->uploadForUser(
                    $file, 
                    $user, 
                    null, // shareWithEmail
                    $casesFolder,
                    $patientId
                );
                
                $googleDriveId = $uploadResult['file_id'];
                $googleDriveLink = $uploadResult['view_link'];
                $filePath = "google_drive/{$googleDriveId}";
                
                Log::info('File uploaded to Google Drive', [
                    'file_id' => $googleDriveId,
                    'view_link' => $googleDriveLink
                ]);
                
            } catch (\Exception $e) {
                Log::error('Google Drive upload failed, falling back to local storage', [
                    'error' => $e->getMessage()
                ]);
                $shouldUseGoogleDrive = false;
            }
        }
        
        // Map file types to rubrique names first
        $rubriqueName = $this->mapFileTypeToRubrique($fileType, $originalName);
        
        if (!$shouldUseGoogleDrive) {
            // Store in public disk - organize by rubrique type
            $caseDir = "case_files/{$caseId}/{$rubriqueName}";
            Storage::disk('public')->makeDirectory($caseDir);
            $filePath = $file->storeAs($caseDir, $fileName, 'public');
            $link = Storage::disk('public')->url($filePath);
            
            Log::info('File stored in public storage', [
                'case_id' => $caseId,
                'file_type' => $fileType,
                'rubrique' => $rubriqueName,
                'directory' => $caseDir,
                'file_path' => $filePath,
                'public_url' => $link
            ]);
        }
        
        // Check for duplicate file uploads (same case, original name, and size)
        $existingFile = FileUpload::where('case_id', $caseId)
            ->where('file_name', $originalName)
            ->where('size', $file->getSize())
            ->where('wich_rubrique', $rubriqueName)
            ->where('name', $originalName) // Also check the 'name' field
            ->first();

        Log::info('Duplicate check for file upload', [
            'case_id' => $caseId,
            'original_name' => $originalName,
            'file_size' => $file->getSize(),
            'rubrique' => $rubriqueName,
            'existing_file_found' => $existingFile ? true : false,
            'existing_file_id' => $existingFile ? $existingFile->id : null
        ]);

        if ($existingFile) {
            Log::warning('Duplicate file upload detected, skipping creation', [
                'case_id' => $caseId,
                'file_name' => $originalName,
                'existing_file_id' => $existingFile->id,
                'file_size' => $file->getSize()
            ]);
            
            return response()->json([
                'success' => true,
                'file_id' => $existingFile->id,
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'storage_type' => $existingFile->storage_type,
                'wich_rubrique' => $rubriqueName,
                'uploadURL' => null,
                'duplicate' => true
            ], 200);
        }

        // Create FileUpload record
        $fileUpload = FileUpload::create([
            'case_id' => $caseId,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'status' => 'completed',
            'uploaded_at' => now(),
            'name' => $originalName,
            'path' => $filePath,
            'url' => $shouldUseGoogleDrive ? $googleDriveLink : $link,
            'storage_type' => $shouldUseGoogleDrive ? 'google_drive' : 'public',
            'google_drive_id' => $googleDriveId,
            'wich_rubrique' => $rubriqueName
        ]);

        // Queue optimization job only for public storage files
        if (!$shouldUseGoogleDrive) {
            OptimizeUploadedFile::dispatch($fileUpload);
        }

        Log::info('XHR upload completed', [
            'file_id' => $fileUpload->id,
            'file_name' => $originalName,
            'file_size' => $file->getSize(),
            'storage_type' => $shouldUseGoogleDrive ? 'google_drive' : 'public',
            'file_type' => $fileType
        ]);

        return response()->json([
            'success' => true,
            'file_id' => $fileUpload->id,
            'file_name' => $originalName,
            'file_size' => $file->getSize(),
            'storage_type' => $shouldUseGoogleDrive ? 'google_drive' : 'public',
            'wich_rubrique' => $rubriqueName,
            'uploadURL' => null // Uppy compatibility
        ], 200, [
            'Content-Type' => 'application/json',
            'Connection' => 'keep-alive',
            'Cache-Control' => 'no-cache'
        ]);
    }

    /**
     * Handle TUS upload HEAD request (check upload status)
     */
    public function head(Request $request, string $uploadId)
    {
        try {
            Log::info('TUS HEAD request', ['upload_id' => $uploadId]);
            
            $uploadData = cache()->get("tus_upload_{$uploadId}");
            
            if (!$uploadData) {
                Log::warning('Upload session not found', ['upload_id' => $uploadId]);
                return response('Upload not found', 404);
            }

            Log::info('TUS HEAD response', [
                'upload_id' => $uploadId,
                'offset' => $uploadData['upload_offset'],
                'length' => $uploadData['upload_length']
            ]);

            return response('')
                ->header('Upload-Offset', $uploadData['upload_offset'])
                ->header('Upload-Length', $uploadData['upload_length'])
                ->header('Tus-Resumable', '1.0.0');
                
        } catch (\Exception $e) {
            Log::error('TUS HEAD failed', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage()
            ]);
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle TUS upload PATCH request (receive chunk)
     */
    public function patch(Request $request, string $uploadId)
    {
        // Optimize for chunk processing
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300);
        
        try {
            Log::info('TUS PATCH request', [
                'upload_id' => $uploadId,
                'offset' => $request->header('Upload-Offset'),
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length')
            ]);

            $uploadData = cache()->get("tus_upload_{$uploadId}");
            
            if (!$uploadData) {
                Log::warning('Upload session not found for PATCH', ['upload_id' => $uploadId]);
                return response('Upload not found', 404);
            }

            $uploadOffset = $request->header('Upload-Offset');
            
            if ($uploadOffset != $uploadData['upload_offset']) {
                Log::warning('Offset mismatch', [
                    'expected' => $uploadData['upload_offset'],
                    'received' => $uploadOffset
                ]);
                return response('Offset mismatch', 409);
            }

            // Get chunk data from request body
            $chunkData = $request->getContent();
            $chunkSize = strlen($chunkData);

            if ($chunkSize === 0) {
                Log::warning('No chunk data received');
                return response('No data received', 400);
            }

            Log::info('Processing chunk', [
                'upload_id' => $uploadId,
                'offset' => $uploadOffset,
                'chunk_size' => $chunkSize,
                'total_size' => $uploadData['upload_length']
            ]);

            // Save chunk to storage
            $uploadDir = "uploads/temp/{$uploadId}";
            $chunkFile = "{$uploadDir}/chunk_{$uploadData['chunks_received']}";
            
            // Ensure directory exists
            Storage::makeDirectory($uploadDir);
            
            // Write chunk data
            Storage::put($chunkFile, $chunkData);

            // Update upload progress
            $uploadData['upload_offset'] += $chunkSize;
            $uploadData['chunks_received']++;
            $uploadData['last_activity'] = now();
            
            // Update cache
            cache()->put("tus_upload_{$uploadId}", $uploadData, now()->addHours(24));

            $isComplete = $uploadData['upload_offset'] >= $uploadData['upload_length'];

            Log::info('Chunk saved', [
                'upload_id' => $uploadId,
                'new_offset' => $uploadData['upload_offset'],
                'chunks_received' => $uploadData['chunks_received'],
                'is_complete' => $isComplete
            ]);

            if ($isComplete) {
                Log::info('Upload complete, assembling file', ['upload_id' => $uploadId]);
                $this->assembleChunks($uploadId, $uploadData);
            }

            return response('')
                ->header('Upload-Offset', $uploadData['upload_offset'])
                ->header('Tus-Resumable', '1.0.0');

        } catch (\Exception $e) {
            Log::error('TUS chunk upload failed', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response('Chunk upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assemble chunks into final file
     */
    private function assembleChunks(string $uploadId, array $uploadData)
    {
        try {
            $uploadDir = "uploads/temp/{$uploadId}";
            $finalFileName = $uploadData['file_name'];
            
            // Map file type to rubrique for proper folder organization
            $fileType = $uploadData['file_type'] ?? 'other_file';
            $rubriqueName = $this->mapFileTypeToRubrique($fileType, $finalFileName);
            $finalPath = "case_files/{$uploadData['case_id']}/{$rubriqueName}/{$finalFileName}";

            // Create case directory with rubrique subdirectory in public storage
            $caseDir = "case_files/{$uploadData['case_id']}/{$rubriqueName}";
            Storage::disk('public')->makeDirectory($caseDir);

            // Open final file for writing in public storage
            $finalFile = Storage::disk('public')->path($finalPath);
            $handle = fopen($finalFile, 'wb');

            if (!$handle) {
                throw new \Exception('Could not create final file');
            }

            // Combine all chunks
            for ($i = 0; $i < $uploadData['chunks_received']; $i++) {
                $chunkPath = Storage::disk('local')->path("{$uploadDir}/chunk_{$i}");
                
                if (file_exists($chunkPath)) {
                    $chunkData = file_get_contents($chunkPath);
                    fwrite($handle, $chunkData);
                }
            }

            fclose($handle);

            // Verify file size
            $finalSize = filesize($finalFile);
            if ($finalSize !== (int)$uploadData['upload_length']) {
                throw new \Exception("File size mismatch: expected {$uploadData['upload_length']}, got {$finalSize}");
            }

            // Use already mapped rubrique name from earlier in the method
            
            // Generate public URL for the file
            $publicUrl = Storage::disk('public')->url($finalPath);

            // Check for duplicate file uploads (same case, name, and size)
            $existingFile = FileUpload::where('case_id', $uploadData['case_id'])
                ->where('file_name', $uploadData['file_name'])
                ->where('size', $finalSize)
                ->where('wich_rubrique', $rubriqueName)
                ->where('name', $uploadData['file_name']) // Also check the 'name' field
                ->first();

            if ($existingFile) {
                Log::warning('Duplicate TUS file upload detected, skipping creation', [
                    'case_id' => $uploadData['case_id'],
                    'file_name' => $uploadData['file_name'],
                    'existing_file_id' => $existingFile->id,
                    'file_size' => $finalSize,
                    'upload_id' => $uploadId
                ]);
                
                // Clean up chunks and cache
                Storage::deleteDirectory($uploadDir);
                cache()->forget("tus_upload_{$uploadId}");
                
                return; // Exit without creating duplicate record
            }

            // Create FileUpload record
            $fileUpload = FileUpload::create([
                'case_id' => $uploadData['case_id'],
                'file_name' => $uploadData['file_name'],
                'file_path' => $finalPath,
                'size' => $finalSize,
                'type' => $uploadData['mime_type'] ?? 'application/octet-stream',
                'status' => 'completed',
                'uploaded_at' => now(),
                'name' => $uploadData['file_name'],
                'path' => $finalPath,
                'url' => $publicUrl,
                'wich_rubrique' => $rubriqueName,
                'storage_type' => 'public',
                'file_type' => $uploadData['file_type'] // Store the actual file type (stl_scan, clinical_photo, etc.)
            ]);

            // Queue optimization job
            OptimizeUploadedFile::dispatch($fileUpload);

            // Clean up chunks
            Storage::deleteDirectory($uploadDir);
            cache()->forget("tus_upload_{$uploadId}");

            Log::info('TUS upload completed', [
                'upload_id' => $uploadId,
                'file_id' => $fileUpload->id,
                'final_size' => $finalSize,
                'storage_type' => 'public',
                'public_url' => $publicUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to assemble chunks', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up on failure
            Storage::deleteDirectory($uploadDir);
            cache()->forget("tus_upload_{$uploadId}");
            
            throw $e;
        }
    }

    /**
     * Parse TUS Upload-Metadata header
     */
    private function parseUploadMetadata(string $metadata = null): array
    {
        if (!$metadata) {
            return [];
        }

        $result = [];
        $pairs = explode(',', $metadata);

        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (strpos($pair, ' ') !== false) {
                list($key, $value) = explode(' ', $pair, 2);
                $result[$key] = base64_decode($value);
            }
        }

        return $result;
    }

    /**
     * Handle upload deletion
     */
    public function delete(Request $request, string $uploadId)
    {
        $uploadData = cache()->get("tus_upload_{$uploadId}");
        
        if ($uploadData) {
            // Clean up
            Storage::deleteDirectory("uploads/temp/{$uploadId}");
            cache()->forget("tus_upload_{$uploadId}");
        }

        return response('', 204)
            ->header('Tus-Resumable', '1.0.0');
    }
    
    /**
     * Map file type to rubrique name based on upload section
     */
    private function mapFileTypeToRubrique(string $fileType, string $fileName): string
    {
        switch ($fileType) {
            case 'stl_scan':
                // All STL files go to stl_scan rubrique
                return 'stl_scan';
                
            case 'clinical_photo':
                // All clinical photos go to clinical_photo rubrique
                return 'clinical_photo';
                
            case 'radiograph':
            case 'radiograph_panoramic':
            case 'radiograph_teleradiography':
                // Panoramic + teleradiography (profile) both stored under radiograph
                return 'radiograph';

            case 'finition':
                // Finition images
                return 'finition';
                
            case 'other_file':
                // All other files go to other_file rubrique
                return 'other_file';
                
            default:
                return 'other_file';
        }
    }
}

