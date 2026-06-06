<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileUpload extends Model
{
    protected $table = 'fileuploads';
    protected $fillable = [
        'name',
        'path',
        'type',
        'size',
        'url',
        'case_id',
        'patient_id',
        'wich_rubrique',
        'storage_type',
        'status',
        'error_message',
        'google_drive_id',
        'google_drive_link',
        'file_path',
        'file_name',
        'temp_filename',
        'uploaded_at',
        'included_in_zip',
        // Chunked upload fields
        'session_id',
        'is_chunked_upload',
        'original_size',
        'compressed_size',
        'file_hash',
        'processing_metadata',
        'upload_started_at',
        'upload_completed_at',
        'optimization_status',
        'thumbnail_path'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'upload_started_at' => 'datetime',
        'upload_completed_at' => 'datetime',
        'processing_metadata' => 'array',
        'is_chunked_upload' => 'boolean',
        'original_size' => 'integer',
        'compressed_size' => 'integer'
    ];

    // Status constants
    const UPLOAD_STATUS_PENDING = 'pending';
    const UPLOAD_STATUS_UPLOADING = 'uploading';
    const UPLOAD_STATUS_COMPLETED = 'completed';
    const UPLOAD_STATUS_FAILED = 'failed';

    // Optimization status constants
    const OPTIMIZATION_PENDING = 'pending';
    const OPTIMIZATION_PROCESSING = 'processing';
    const OPTIMIZATION_COMPLETED = 'completed';
    const OPTIMIZATION_FAILED = 'failed';

    public function case()
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the upload session for chunked uploads
     */
    public function uploadSession(): BelongsTo
    {
        return $this->belongsTo(UploadSession::class, 'session_id', 'session_id');
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->type ?? '', 'image/');
    }

    /**
     * Check if file is an STL file
     */
    public function isSTL(): bool
    {
        return str_ends_with(strtolower($this->name ?? ''), '.stl') || 
               ($this->type === 'model/stl');
    }

    /**
     * Get file size in human readable format
     */
    public function getHumanReadableSize(): string
    {
        $size = $this->original_size ?? $this->size ?? 0;
        
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        }
        
        return $size . ' bytes';
    }

    /**
     * Get compression ratio if file was compressed
     */
    public function getCompressionRatio(): ?float
    {
        if (!$this->original_size || !$this->compressed_size) {
            return null;
        }
        
        return round((($this->original_size - $this->compressed_size) / $this->original_size) * 100, 2);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        
        return asset('storage/' . $this->thumbnail_path);
    }

    /**
     * Check if optimization is needed
     */
    public function needsOptimization(): bool
    {
        return $this->isImage() && 
               $this->optimization_status === self::OPTIMIZATION_PENDING;
    }

    /**
     * Mark as optimized
     */
    public function markAsOptimized(array $metadata = []): void
    {
        $this->update([
            'optimization_status' => self::OPTIMIZATION_COMPLETED,
            'processing_metadata' => array_merge($this->processing_metadata ?? [], $metadata)
        ]);
    }

    /**
     * Mark optimization as failed
     */
    public function markOptimizationFailed(string $error): void
    {
        $this->update([
            'optimization_status' => self::OPTIMIZATION_FAILED,
            'processing_metadata' => array_merge($this->processing_metadata ?? [], [
                'optimization_error' => $error,
                'failed_at' => now()->toISOString()
            ])
        ]);
    }

    /**
     * Scope for chunked uploads
     */
    public function scopeChunked($query)
    {
        return $query->where('is_chunked_upload', true);
    }

    /**
     * Scope for images
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'like', 'image/%');
    }

    /**
     * Scope for STL files
     */
    public function scopeSTL($query)
    {
        return $query->where(function ($q) {
            $q->where('name', 'like', '%.stl')
              ->orWhere('type', 'model/stl');
        });
    }

    /**
     * Scope for files needing optimization
     */
    public function scopeNeedsOptimization($query)
    {
        return $query->where('type', 'like', 'image/%')
                    ->where('optimization_status', self::OPTIMIZATION_PENDING);
    }
}
