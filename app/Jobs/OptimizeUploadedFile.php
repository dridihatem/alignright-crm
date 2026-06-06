<?php

namespace App\Jobs;

use App\Models\FileUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class OptimizeUploadedFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileUpload;
    protected $maxRetries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting file optimization', [
                'file_id' => $this->fileUpload->id,
                'file_name' => $this->fileUpload->name,
                'file_type' => $this->fileUpload->type,
                'original_size' => $this->fileUpload->original_size
            ]);

            // Mark as processing
            $this->fileUpload->update([
                'optimization_status' => FileUpload::OPTIMIZATION_PROCESSING
            ]);

            if ($this->fileUpload->isImage()) {
                $this->optimizeImage();
            } elseif ($this->fileUpload->isSTL()) {
                $this->processSTL();
            } else {
                // For other files, just mark as completed
                $this->fileUpload->markAsOptimized([
                    'processing_type' => 'none',
                    'processed_at' => now()->toISOString()
                ]);
            }

            Log::info('File optimization completed', [
                'file_id' => $this->fileUpload->id,
                'optimization_status' => $this->fileUpload->optimization_status
            ]);

        } catch (Exception $e) {
            Log::error('File optimization failed', [
                'file_id' => $this->fileUpload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->fileUpload->markOptimizationFailed($e->getMessage());
            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Optimize image files
     */
    private function optimizeImage(): void
    {
        $filePath = storage_path('app/' . $this->fileUpload->path);
        
        if (!file_exists($filePath)) {
            throw new Exception('Original file not found: ' . $filePath);
        }

        // Load image with Intervention Image v3
        $manager = new ImageManager(new Driver());
        $image = $manager->read($filePath);
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        $originalSize = filesize($filePath);

        // Create optimized version
        $optimizedPath = $this->getOptimizedPath($this->fileUpload->path);
        $optimizedFullPath = storage_path('app/' . $optimizedPath);

        // Ensure directory exists
        $directory = dirname($optimizedFullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Resize if larger than 800px
        if ($originalWidth > 800 || $originalHeight > 800) {
            $image = $image->scale(800, 800);
        }

        // Apply optimization based on format
        $extension = strtolower(pathinfo($this->fileUpload->name, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image->toJpeg(85)->save($optimizedFullPath); // 85% quality
                break;
            case 'png':
                $image->toPng()->save($optimizedFullPath); // PNG optimization
                break;
            case 'webp':
                $image->toWebp(85)->save($optimizedFullPath);
                break;
            default:
                $image->toJpeg(85)->save($optimizedFullPath);
        }

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($image);

        // Get optimized file size
        $optimizedSize = filesize($optimizedFullPath);
        $compressionRatio = round((($originalSize - $optimizedSize) / $originalSize) * 100, 2);

        // Update file record
        $this->fileUpload->update([
            'compressed_size' => $optimizedSize,
            'thumbnail_path' => $thumbnailPath,
            'processing_metadata' => [
                'optimization_type' => 'image_resize_compress',
                'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight],
                'optimized_dimensions' => ['width' => $image->width(), 'height' => $image->height()],
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'compression_ratio' => $compressionRatio,
                'quality_setting' => $extension === 'png' ? 90 : 85,
                'processed_at' => now()->toISOString()
            ]
        ]);

        // Replace original with optimized version if significant savings
        if ($compressionRatio > 10) { // If we saved more than 10%
            // Backup original
            $backupPath = $this->getBackupPath($this->fileUpload->path);
            Storage::copy($this->fileUpload->path, $backupPath);
            
            // Replace with optimized
            Storage::copy($optimizedPath, $this->fileUpload->path);
            
            // Update size in database
            $this->fileUpload->update(['size' => $optimizedSize]);
            
            Log::info('Image replaced with optimized version', [
                'file_id' => $this->fileUpload->id,
                'savings' => $compressionRatio . '%',
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize
            ]);
        }

        $this->fileUpload->markAsOptimized();
    }

    /**
     * Process STL files
     */
    private function processSTL(): void
    {
        $filePath = storage_path('app/' . $this->fileUpload->path);
        
        if (!file_exists($filePath)) {
            throw new Exception('STL file not found: ' . $filePath);
        }

        // Basic STL file validation
        $fileContent = file_get_contents($filePath, false, null, 0, 1024); // Read first 1KB
        $isASCII = strpos($fileContent, 'solid') === 0;
        $isBinary = !$isASCII;

        // Get file statistics
        $fileSize = filesize($filePath);
        
        if ($isBinary) {
            // For binary STL, we can read triangle count from header
            $handle = fopen($filePath, 'rb');
            fseek($handle, 80); // Skip 80-byte header
            $triangleCount = unpack('V', fread($handle, 4))[1]; // Read triangle count
            fclose($handle);
        } else {
            // For ASCII STL, count "facet normal" occurrences
            $triangleCount = substr_count(file_get_contents($filePath), 'facet normal');
        }

        // Update file record with STL metadata
        $this->fileUpload->update([
            'processing_metadata' => [
                'processing_type' => 'stl_analysis',
                'stl_format' => $isASCII ? 'ascii' : 'binary',
                'triangle_count' => $triangleCount,
                'file_size' => $fileSize,
                'estimated_vertices' => $triangleCount * 3,
                'complexity_score' => $this->calculateSTLComplexity($triangleCount, $fileSize),
                'processed_at' => now()->toISOString()
            ]
        ]);

        $this->fileUpload->markAsOptimized();

        Log::info('STL file processed', [
            'file_id' => $this->fileUpload->id,
            'format' => $isASCII ? 'ASCII' : 'Binary',
            'triangles' => $triangleCount,
            'size' => $fileSize
        ]);
    }

    /**
     * Generate thumbnail for images
     */
    private function generateThumbnail($image): string
    {
        $thumbnailPath = 'thumbnails/' . pathinfo($this->fileUpload->path, PATHINFO_FILENAME) . '_thumb.webp';
        $thumbnailFullPath = storage_path('app/' . $thumbnailPath);

        // Ensure directory exists
        $directory = dirname($thumbnailFullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create 150x150 thumbnail
        $thumbnail = $image->cover(150, 150);
        $thumbnail->toWebp(80)->save($thumbnailFullPath);

        return $thumbnailPath;
    }

    /**
     * Get optimized file path
     */
    private function getOptimizedPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/optimized_' . $pathInfo['basename'];
    }

    /**
     * Get backup file path
     */
    private function getBackupPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/backup_' . $pathInfo['basename'];
    }

    /**
     * Calculate STL complexity score
     */
    private function calculateSTLComplexity(int $triangleCount, int $fileSize): string
    {
        if ($triangleCount < 1000) {
            return 'low';
        } elseif ($triangleCount < 10000) {
            return 'medium';
        } elseif ($triangleCount < 100000) {
            return 'high';
        } else {
            return 'very_high';
        }
    }

    /**
     * Failed job handling
     */
    public function failed(Exception $exception): void
    {
        Log::error('File optimization job failed permanently', [
            'file_id' => $this->fileUpload->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        $this->fileUpload->markOptimizationFailed(
            'Job failed after ' . $this->attempts() . ' attempts: ' . $exception->getMessage()
        );
    }
}