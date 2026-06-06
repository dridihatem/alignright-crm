<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;
use Intervention\Image\Facades\Image;

class ImageProcessingService
{
    /**
     * Maximum file size in bytes (10MB)
     */
    const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Maximum image dimensions
     */
    const MAX_WIDTH = 1920;
    const MAX_HEIGHT = 1080;

    /**
     * Quality settings for different file types
     */
    const JPEG_QUALITY = 85;
    const PNG_QUALITY = 8; // 0-9, where 9 is best compression

    /**
     * Compress and resize an uploaded image file
     */
    public function processImage(UploadedFile $file, array $options = [])
    {
        try {
            // Get file info
            $originalSize = $file->getSize();
            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());

            // Check if it's an image
            if (!$this->isImage($mimeType)) {
                throw new Exception('File is not a valid image');
            }

            // If file is already small enough, return as is
            if ($originalSize <= self::MAX_FILE_SIZE) {
                return $file;
            }

            // Get processing options
            $maxWidth = $options['max_width'] ?? self::MAX_WIDTH;
            $maxHeight = $options['max_height'] ?? self::MAX_HEIGHT;
            $quality = $options['quality'] ?? self::JPEG_QUALITY;

            // Create image instance
            $image = Image::make($file->getRealPath());

            // Resize if necessary
            $image = $this->resizeImage($image, $maxWidth, $maxHeight);

            // Compress based on file type
            $image = $this->compressImage($image, $extension, $quality);

            // Create temporary file
            $tempPath = tempnam(sys_get_temp_dir(), 'compressed_');
            $image->save($tempPath, $quality, $extension === 'png' ? 'png' : 'jpg');

            // Create new UploadedFile instance
            $compressedFile = new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                $file->getMimeType(),
                null,
                true
            );

            // Log compression results
            $compressedSize = filesize($tempPath);
            $compressionRatio = round((1 - ($compressedSize / $originalSize)) * 100, 2);
            
            Log::info("Image compressed: {$file->getClientOriginalName()}", [
                'original_size' => $this->formatBytes($originalSize),
                'compressed_size' => $this->formatBytes($compressedSize),
                'compression_ratio' => $compressionRatio . '%',
                'dimensions' => $image->width() . 'x' . $image->height()
            ]);

            return $compressedFile;

        } catch (Exception $e) {
            Log::error('Error processing image: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);
            throw new Exception('Failed to process image: ' . $e->getMessage());
        }
    }

    /**
     * Process multiple image files
     */
    public function processMultipleImages(array $files, array $options = [])
    {
        $processedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $processedFiles[] = $this->processImage($file, $options);
                } catch (Exception $e) {
                    Log::error('Failed to process image: ' . $e->getMessage());
                    // Return original file if processing fails
                    $processedFiles[] = $file;
                }
            }
        }

        return $processedFiles;
    }

    /**
     * Resize image while maintaining aspect ratio
     */
    private function resizeImage($image, $maxWidth, $maxHeight)
    {
        $width = $image->width();
        $height = $image->height();

        // Only resize if image is larger than maximum dimensions
        if ($width > $maxWidth || $height > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        return $image;
    }

    /**
     * Compress image based on file type
     */
    private function compressImage($image, $extension, $quality)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image->encode('jpg', $quality);
                break;
            case 'png':
                $image->encode('png', self::PNG_QUALITY);
                break;
            case 'webp':
                $image->encode('webp', $quality);
                break;
            default:
                // For other formats, use JPEG compression
                $image->encode('jpg', $quality);
        }

        return $image;
    }

    /**
     * Check if file is an image
     */
    private function isImage($mimeType)
    {
        return in_array($mimeType, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp'
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get recommended compression settings based on file size
     */
    public function getCompressionSettings($fileSize)
    {
        if ($fileSize <= 1024 * 1024) { // 1MB
            return [
                'max_width' => 1920,
                'max_height' => 1080,
                'quality' => 90
            ];
        } elseif ($fileSize <= 5 * 1024 * 1024) { // 5MB
            return [
                'max_width' => 1600,
                'max_height' => 900,
                'quality' => 85
            ];
        } else { // > 5MB
            return [
                'max_width' => 1280,
                'max_height' => 720,
                'quality' => 80
            ];
        }
    }

    /**
     * Validate file before processing
     */
    public function validateImage(UploadedFile $file)
    {
        $errors = [];

        // Check file size
        if ($file->getSize() > 50 * 1024 * 1024) { // 50MB limit
            $errors[] = 'File size exceeds maximum limit of 50MB';
        }

        // Check file type
        if (!$this->isImage($file->getMimeType())) {
            $errors[] = 'File must be a valid image (JPEG, PNG, GIF, WebP)';
        }

        // Check for malicious files
        if (!$file->isValid()) {
            $errors[] = 'Invalid file upload';
        }

        return $errors;
    }
}
