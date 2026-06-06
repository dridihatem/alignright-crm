<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Providers\GoogleDriveService;

class DoctorFileController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Test method to debug Google Drive connection
     */
    public function test()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'has_google_access_token' => !empty($user->google_access_token),
                'has_google_refresh_token' => !empty($user->google_refresh_token),
                'google_token_expires_at' => $user->google_token_expires_at,
                'google_access_token_length' => $user->google_access_token ? strlen($user->google_access_token) : 0,
                'google_refresh_token_length' => $user->google_refresh_token ? strlen($user->google_refresh_token) : 0
            ];
            
            // Check if credentials file exists
            $credentialsPath = storage_path('app/credentials.json');
            $credentialsExist = file_exists($credentialsPath);
            
            return response()->json([
                'success' => true,
                'user_data' => $userData,
                'credentials_exist' => $credentialsExist,
                'credentials_path' => $credentialsPath,
                'storage_path' => storage_path(),
                'app_path' => app_path()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Upload a file directly
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:102400', // 100MB max
                'case_id' => 'required|string', // Add case_id validation
            ]);

            $file = $request->file('file');
            $caseId = $request->input('case_id'); // Get case_id from request
            
            Log::info('File upload started', [
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'case_id' => $caseId
            ]);

            $user = auth()->user();
            
            // Check if user has Google Drive tokens
            if (!$user->google_access_token || !$user->google_refresh_token) {
                Log::warning('User does not have Google Drive tokens configured', [
                    'user_id' => $user->id,
                    'has_access_token' => !empty($user->google_access_token),
                    'has_refresh_token' => !empty($user->google_refresh_token)
                ]);
                
                // Fallback to local storage temporarily
                $fileName = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();
                
                // Ensure temp_uploads directory exists
                if (!Storage::disk('local')->exists('temp_uploads')) {
                    Storage::disk('local')->makeDirectory('temp_uploads');
                }
                
                $path = $file->storeAs('temp_uploads', $fileName, 'local');
                    
                return response()->json([
                    'success' => true,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'warning' => 'File stored locally - Google Drive not configured'
                ]);
            }
            
            // Upload directly to Google Drive with case_id folder structure
            $uploadResult = $this->googleDriveService->uploadForUser(
                $file, 
                $user, 
                null, 
                "Cases", // Main cases folder
                $caseId  // Use case_id for the subfolder
            );
            
            return response()->json([
                'success' => true,
                'google_drive_id' => $uploadResult['id'],
                'web_view_link' => $uploadResult['webViewLink'],
                'file_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'case_id' => $caseId
            ]);
            
        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize(),
                'case_id' => $request->input('case_id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to local storage on error
            try {
                $file = $request->file('file');
                $caseId = $request->input('case_id');
                $fileName = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();
                
                // Ensure temp_uploads directory exists
                if (!Storage::disk('local')->exists('temp_uploads')) {
                    Storage::disk('local')->makeDirectory('temp_uploads');
                }
                
                $path = $file->storeAs('temp_uploads', $fileName, 'local');
                
                return response()->json([
                    'success' => true,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'case_id' => $caseId,
                    'warning' => 'File stored locally due to Google Drive error: ' . $e->getMessage()
                ]);
            } catch (\Exception $fallbackError) {
                Log::error('Fallback storage also failed: ' . $fallbackError->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Upload a file chunk
     */
    public function uploadChunk(Request $request)
    {
        try {
            $request->validate([
                'chunk' => 'required|file',
                'chunk_index' => 'required|integer',
                'total_chunks' => 'required|integer',
                'file_name' => 'required|string',
            ]);

            $chunk = $request->file('chunk');
            $chunkIndex = $request->input('chunk_index');
            $totalChunks = $request->input('total_chunks');
            $fileName = $request->input('file_name');
            
            // Create unique identifier for this file
            $fileId = md5($fileName . time());
            $chunkDir = "temp_chunks/{$fileId}";
            
            // Ensure temp_chunks directory exists
            if (!Storage::disk('local')->exists('temp_chunks')) {
                Storage::disk('local')->makeDirectory('temp_chunks');
            }
            
            // Store chunk
            $chunkPath = $chunk->storeAs($chunkDir, "chunk_{$chunkIndex}", 'local');
            
            // Store metadata
            $metadata = [
                'file_name' => $fileName,
                'total_chunks' => $totalChunks,
                'uploaded_chunks' => [$chunkIndex],
                'created_at' => now()->timestamp,
                'doctor_id' => auth()->id()
            ];
            
            $metadataPath = "{$chunkDir}/metadata.json";
            Storage::disk('local')->put($metadataPath, json_encode($metadata));
            
            return response()->json([
                'success' => true,
                'chunk_index' => $chunkIndex,
                'file_id' => $fileId,
                'chunk_path' => $chunkPath
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chunk upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Chunk upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Combine uploaded chunks into a single file and upload to Google Drive
     */
    public function combineChunks(Request $request)
    {
        try {
            $request->validate([
                'chunks' => 'required|array',
                'file_name' => 'required|string',
                'case_id' => 'required|string', // Add case_id validation
            ]);

            $chunks = $request->input('chunks');
            $fileName = $request->input('file_name');
            $caseId = $request->input('case_id'); // Get case_id from request
            
            Log::info('Combining chunks started', [
                'user_id' => auth()->id(),
                'file_name' => $fileName,
                'case_id' => $caseId,
                'chunks_count' => count($chunks)
            ]);
            
            if (empty($chunks)) {
                throw new \Exception('No chunks provided');
            }
            
            // Get file ID from first chunk
            $fileId = $chunks[0]['file_id'] ?? null;
            if (!$fileId) {
                throw new \Exception('Invalid file ID');
            }
            
            $chunkDir = "temp_chunks/{$fileId}";
            $metadataPath = "{$chunkDir}/metadata.json";
            
            // Check if all chunks are uploaded
            $metadata = json_decode(Storage::disk('local')->get($metadataPath), true);
            $uploadedChunks = $metadata['uploaded_chunks'] ?? [];
            $totalChunks = $metadata['total_chunks'] ?? 0;
            
            if (count($uploadedChunks) !== $totalChunks) {
                throw new \Exception('Not all chunks have been uploaded');
            }
            
            // Combine chunks
            $finalPath = "temp_uploads/" . time() . "_" . Str::random(10) . "_" . $fileName;
            
            // Ensure temp_uploads directory exists
            if (!Storage::disk('local')->exists('temp_uploads')) {
                Storage::disk('local')->makeDirectory('temp_uploads');
            }
            
            $finalFile = Storage::disk('local')->path($finalPath);
            
            // Ensure directory exists
            $finalDir = dirname($finalFile);
            if (!is_dir($finalDir)) {
                mkdir($finalDir, 0755, true);
            }
            
            // Open final file for writing
            $finalHandle = fopen($finalFile, 'wb');
            
            // Read and combine chunks in order
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = Storage::disk('local')->path("{$chunkDir}/chunk_{$i}");
                
                if (!file_exists($chunkPath)) {
                    throw new \Exception("Chunk {$i} not found");
                }
                
                $chunkData = file_get_contents($chunkPath);
                fwrite($finalHandle, $chunkData);
            }
            
            fclose($finalHandle);
            
            // Create a file object from the combined file
            $combinedFile = new \Illuminate\Http\UploadedFile(
                $finalFile,
                $fileName,
                mime_content_type($finalFile),
                null,
                true
            );
            
            // Try to upload to Google Drive, fallback to local storage
            try {
                $user = auth()->user();
                
                if ($user->google_access_token && $user->google_refresh_token) {
                    // Upload to Google Drive with case_id folder structure
                    $uploadResult = $this->googleDriveService->uploadForUser(
                        $combinedFile, 
                        $user, 
                        null, 
                        "Cases", // Main cases folder
                        $caseId  // Use case_id for the subfolder
                    );
                    
                    // Clean up chunks and temporary file
                    Storage::disk('local')->deleteDirectory($chunkDir);
                    Storage::disk('local')->delete($finalPath);
                    
                    return response()->json([
                        'success' => true,
                        'google_drive_id' => $uploadResult['id'],
                        'web_view_link' => $uploadResult['webViewLink'],
                        'file_name' => $fileName,
                        'size' => filesize($finalFile),
                        'case_id' => $caseId
                    ]);
                } else {
                    // Fallback to local storage
                    $finalPath = "uploads/" . date('Y/m/d') . "/" . time() . "_" . Str::random(10) . "_" . $fileName;
                    
                    // Ensure uploads directory exists
                    if (!Storage::disk('local')->exists('uploads')) {
                        Storage::disk('local')->makeDirectory('uploads');
                    }
                    
                    Storage::disk('local')->move($finalPath, $finalPath);
                    
                    // Clean up chunks
                    Storage::disk('local')->deleteDirectory($chunkDir);
                    
                    return response()->json([
                        'success' => true,
                        'file_path' => $finalPath,
                        'file_name' => $fileName,
                        'size' => filesize($finalFile),
                        'case_id' => $caseId,
                        'warning' => 'File stored locally - Google Drive not configured'
                    ]);
                }
            } catch (\Exception $uploadError) {
                Log::error('Google Drive upload failed, falling back to local storage: ' . $uploadError->getMessage());
                
                // Fallback to local storage
                $finalPath = "uploads/" . date('Y/m/d') . "/" . time() . "_" . Str::random(10) . "_" . $fileName;
                
                // Ensure uploads directory exists
                if (!Storage::disk('local')->exists('uploads')) {
                    Storage::disk('local')->makeDirectory('uploads');
                }
                
                Storage::disk('local')->move($finalPath, $finalPath);
                
                // Clean up chunks
                Storage::disk('local')->deleteDirectory($chunkDir);
                
                return response()->json([
                    'success' => true,
                    'file_path' => $finalPath,
                    'file_name' => $fileName,
                    'size' => filesize($finalFile),
                    'case_id' => $caseId,
                    'warning' => 'File stored locally due to Google Drive error: ' . $uploadError->getMessage()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Chunk combination error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'case_id' => $request->input('case_id'),
                'file_name' => $request->input('file_name')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to combine chunks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up temporary files (cron job)
     */
    public function cleanupTempFiles()
    {
        try {
            $tempDirs = ['temp_uploads', 'temp_chunks'];
            $deletedCount = 0;
            
            foreach ($tempDirs as $dir) {
                if (Storage::disk('local')->exists($dir)) {
                    // Delete files older than 24 hours
                    $files = Storage::disk('local')->files($dir);
                    
                    foreach ($files as $file) {
                        $filePath = Storage::disk('local')->path($file);
                        $fileAge = time() - filemtime($filePath);
                        
                        if ($fileAge > 86400) { // 24 hours
                            Storage::disk('local')->delete($file);
                            $deletedCount++;
                        }
                    }
                }
            }
            
            Log::info("Cleaned up {$deletedCount} temporary files");
            return $deletedCount;
            
        } catch (\Exception $e) {
            Log::error('Cleanup error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Remove file from FileUpload table and storage
     */
    public function removeFile($id)
    {
        try {
            $fileUpload = \App\Models\FileUpload::findOrFail($id);
            
            // Check if user is authorized (only doctors can remove files)
            $user = auth()->user();
            $userRole = strtolower($user->role->name ?? '');
            
            Log::info('Remove file authorization check', [
                'user_id' => $user->id,
                'user_role' => $user->role->name,
                'user_role_lower' => $userRole,
                'file_id' => $id
            ]);
            
            if (!in_array($userRole, ['doctor', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('master.unauthorized_action') . ' - Role: ' . $user->role->name
                ], 403);
            }

            // Check if case is in production - prevent file removal
            $case = \App\Models\CasePatient::find($fileUpload->case_id);
            if ($case && $case->status === 'in_production') {
                Log::warning('File removal blocked - case in production', [
                    'file_id' => $id,
                    'case_id' => $case->id,
                    'case_status' => $case->status,
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => __('master.cannot_remove_files_in_production')
                ], 403);
            }

            // Check if file exists
            if (!$fileUpload) {
                return response()->json([
                    'success' => false,
                    'message' => __('master.file_not_found')
                ], 404);
            }

            $fileName = $fileUpload->name;
            $filePath = $fileUpload->path;
            $storageType = $fileUpload->storage_type;

            // If it's a physical file (not a link), try to delete it from storage
            if ($storageType === 'public' && $filePath) {
                // Extract relative path from URL if it's a storage URL
                $relativePath = str_replace(asset('storage/'), '', $filePath);
                
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                    Log::info('Physical file deleted from public storage', [
                        'file_id' => $id,
                        'file_path' => $relativePath
                    ]);
                }
            } elseif ($storageType === 'local' && $filePath) {
                if (Storage::disk('local')->exists($filePath)) {
                    Storage::disk('local')->delete($filePath);
                    Log::info('Physical file deleted from local storage', [
                        'file_id' => $id,
                        'file_path' => $filePath
                    ]);
                }
            }

            // Delete the FileUpload record
            $fileUpload->delete();

            Log::info('File removed successfully', [
                'file_id' => $id,
                'file_name' => $fileName,
                'removed_by' => auth()->id(),
                'storage_type' => $storageType
            ]);

            return response()->json([
                'success' => true,
                'message' => __('master.file_removed_successfully')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('File not found for removal', [
                'file_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('master.file_not_found')
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error removing file', [
                'file_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('master.error_removing_file') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}
