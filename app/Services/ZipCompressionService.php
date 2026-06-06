<?php

namespace App\Services;

use ZipArchive;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ZipCompressionService
{
    /**
     * Create a ZIP file with all case files
     *
     * @param array $files Array of files organized by category
     * @param string $caseId Case ID for naming
     * @return string Path to the created ZIP file
     */
    public function createCaseZip(array $files, string $caseId): string
    {
        try {
            // Create temp directory for ZIP processing
            $tempDir = storage_path('app/temp_zip');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate ZIP filename
            $zipFileName = "case_{$caseId}_" . date('Y-m-d_H-i-s') . '.zip';
            $zipFilePath = $tempDir . '/' . $zipFileName;

            // Create ZIP archive
            $zip = new ZipArchive();
            $result = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== TRUE) {
                throw new Exception("Cannot create ZIP file: Error code {$result}");
            }

            Log::info('Creating ZIP archive for case', [
                'case_id' => $caseId,
                'zip_path' => $zipFilePath,
                'total_file_categories' => count($files)
            ]);

            $totalFiles = 0;
            $totalSize = 0;

            // Add files to ZIP organized by category
            foreach ($files as $category => $categoryFiles) {
                if (empty($categoryFiles)) {
                    continue;
                }

                // Create folder in ZIP for this category
                $categoryFolder = $this->sanitizeFolderName($category) . '/';
                $zip->addEmptyDir($categoryFolder);

                // Add files to category folder
                foreach ($categoryFiles as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $fileName = $this->sanitizeFileName($file->getClientOriginalName());
                        $zipPath = $categoryFolder . $fileName;
                        
                        // Add file to ZIP
                        $zip->addFile($file->getPathname(), $zipPath);
                        $totalFiles++;
                        $totalSize += $file->getSize();

                        Log::info('Added file to ZIP', [
                            'category' => $category,
                            'file_name' => $fileName,
                            'file_size' => $file->getSize(),
                            'zip_path' => $zipPath
                        ]);
                    }
                }
            }

            // Add case information file
            $caseInfoContent = $this->generateCaseInfoContent($caseId, $totalFiles, $totalSize);
            $zip->addFromString('case_info.txt', $caseInfoContent);

            // Close ZIP
            $result = $zip->close();
            
            if (!$result) {
                throw new Exception('Failed to close ZIP archive');
            }

            // Verify ZIP was created successfully
            if (!file_exists($zipFilePath) || filesize($zipFilePath) == 0) {
                throw new Exception('ZIP file was not created properly');
            }

            Log::info('ZIP archive created successfully', [
                'case_id' => $caseId,
                'zip_path' => $zipFilePath,
                'zip_size' => filesize($zipFilePath),
                'total_files' => $totalFiles,
                'total_original_size' => $totalSize
            ]);

            return $zipFilePath;

        } catch (Exception $e) {
            Log::error('Failed to create ZIP archive', [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to create ZIP archive: ' . $e->getMessage());
        }
    }

    /**
     * Create ZIP from temporary uploaded files
     *
     * @param array $fileUploads Array of FileUpload models
     * @param string $caseId Case ID
     * @return string Path to created ZIP file
     */
    public function createZipFromTempFiles(array $fileUploads, string $caseId): string
    {
        try {
            // Create temp directory for ZIP processing
            $tempDir = storage_path('app/temp_zip');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate ZIP filename
            $zipFileName = "case_{$caseId}_" . date('Y-m-d_H-i-s') . '.zip';
            $zipFilePath = $tempDir . '/' . $zipFileName;

            // Create ZIP archive
            $zip = new ZipArchive();
            $result = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== TRUE) {
                throw new Exception("Cannot create ZIP file: Error code {$result}");
            }

            Log::info('Creating ZIP from temp files', [
                'case_id' => $caseId,
                'zip_path' => $zipFilePath,
                'file_count' => count($fileUploads)
            ]);

            $totalFiles = 0;
            $totalSize = 0;
            $filesByCategory = [];

            // Group files by category
            foreach ($fileUploads as $fileUpload) {
                $category = $fileUpload->wich_rubrique ?? 'other';
                if (!isset($filesByCategory[$category])) {
                    $filesByCategory[$category] = [];
                }
                $filesByCategory[$category][] = $fileUpload;
            }

            // Add files to ZIP organized by category
            foreach ($filesByCategory as $category => $categoryFileUploads) {
                // Create folder in ZIP for this category
                $categoryFolder = $this->sanitizeFolderName($category) . '/';
                $zip->addEmptyDir($categoryFolder);

                foreach ($categoryFileUploads as $fileUpload) {
                    $tempPath = storage_path('app/temp_uploads/' . $fileUpload->temp_filename);
                    
                    if (file_exists($tempPath)) {
                        $fileName = $this->sanitizeFileName($fileUpload->file_name);
                        $zipPath = $categoryFolder . $fileName;
                        
                        // Add file to ZIP
                        $zip->addFile($tempPath, $zipPath);
                        $totalFiles++;
                        $totalSize += $fileUpload->size;

                        Log::info('Added temp file to ZIP', [
                            'category' => $category,
                            'file_name' => $fileName,
                            'temp_path' => $tempPath,
                            'zip_path' => $zipPath
                        ]);
                    } else {
                        Log::warning('Temp file not found for ZIP', [
                            'file_upload_id' => $fileUpload->id,
                            'temp_path' => $tempPath
                        ]);
                    }
                }
            }

            // Add case information file
            $caseInfoContent = $this->generateCaseInfoContent($caseId, $totalFiles, $totalSize);
            $zip->addFromString('case_info.txt', $caseInfoContent);

            // Close ZIP
            $result = $zip->close();
            
            if (!$result) {
                throw new Exception('Failed to close ZIP archive');
            }

            Log::info('ZIP from temp files created successfully', [
                'case_id' => $caseId,
                'zip_path' => $zipFilePath,
                'zip_size' => filesize($zipFilePath),
                'total_files' => $totalFiles
            ]);

            return $zipFilePath;

        } catch (Exception $e) {
            Log::error('Failed to create ZIP from temp files', [
                'case_id' => $caseId,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to create ZIP from temp files: ' . $e->getMessage());
        }
    }

    /**
     * Clean up temporary ZIP files
     *
     * @param string $zipFilePath Path to ZIP file to delete
     */
    public function cleanupZipFile(string $zipFilePath): void
    {
        try {
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
                Log::info('Cleaned up ZIP file', ['zip_path' => $zipFilePath]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to cleanup ZIP file', [
                'zip_path' => $zipFilePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sanitize folder name for ZIP
     */
    private function sanitizeFolderName(string $name): string
    {
        // Convert category names to readable folder names
        $folderNames = [
            'photo_clinic_01' => '01_Clinical_Photos',
            'photo_clinic_02' => '02_Clinical_Photos',
            'photo_clinic_03' => '03_Clinical_Photos',
            'photo_clinic_04' => '04_Clinical_Photos',
            'photo_clinic_05' => '05_Clinical_Photos',
            'photo_clinic_06' => '06_Clinical_Photos',
            'photo_clinic_07' => '07_Clinical_Photos',
            'photo_clinic_08' => '08_Clinical_Photos',
            'photo_radiographs' => 'Radiographs',
            'upper_scan' => 'STL_Scans/Upper_Scan',
            'lower_scan' => 'STL_Scans/Lower_Scan',
            'bite_scan' => 'STL_Scans/Bite_Scan',
            'other_files' => 'Other_Files'
        ];

        $folderName = $folderNames[$name] ?? ucfirst(str_replace('_', ' ', $name));
        
        // Remove invalid characters
        return preg_replace('/[^\w\s\-\.\/]/', '', $folderName);
    }

    /**
     * Sanitize file name for ZIP
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Remove invalid characters but keep extension
        $pathInfo = pathinfo($fileName);
        $baseName = preg_replace('/[^\w\s\-\.]/', '', $pathInfo['filename']);
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
        
        return $baseName . $extension;
    }

    /**
     * Generate case information content for ZIP
     */
    private function generateCaseInfoContent(string $caseId, int $totalFiles, int $totalSize): string
    {
        $content = "Case Archive Information\n";
        $content .= "========================\n\n";
        $content .= "Case ID: {$caseId}\n";
        $content .= "Archive Created: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Total Files: {$totalFiles}\n";
        $content .= "Total Size: " . $this->formatBytes($totalSize) . "\n\n";
        $content .= "File Organization:\n";
        $content .= "- Clinical Photos (01-08): Patient clinical photography\n";
        $content .= "- Radiographs: X-ray and radiographic images\n";
        $content .= "- STL Scans: 3D scan files (Upper, Lower, Bite)\n";
        $content .= "- Other Files: Additional documentation\n\n";
        $content .= "Generated by Saas Doctor Dentiste System\n";

        return $content;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}







