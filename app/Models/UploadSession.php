<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UploadSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'case_id',
        'user_id',
        'original_filename',
        'file_type',
        'mime_type',
        'total_size',
        'total_chunks',
        'chunk_size',
        'uploaded_chunks',
        'status',
        'file_category',
        'metadata',
        'final_file_path',
        'temp_directory',
        'started_at',
        'completed_at',
        'expires_at',
        'error_message',
        'retry_count'
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'total_size' => 'integer',
        'total_chunks' => 'integer',
        'chunk_size' => 'integer',
        'uploaded_chunks' => 'integer',
        'retry_count' => 'integer'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_UPLOADING = 'uploading';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ASSEMBLING = 'assembling';
    const STATUS_PROCESSING = 'processing';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->session_id)) {
                $model->session_id = Str::uuid();
            }
            
            if (empty($model->expires_at)) {
                // Sessions expire after 24 hours
                $model->expires_at = Carbon::now()->addHours(24);
            }
        });
    }

    /**
     * Get the case that owns the upload session
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    /**
     * Get the user that owns the upload session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chunks for this upload session
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(FileChunk::class, 'session_id', 'session_id');
    }

    /**
     * Get completed chunks
     */
    public function completedChunks(): HasMany
    {
        return $this->chunks()->where('status', FileChunk::STATUS_UPLOADED);
    }

    /**
     * Check if upload is complete
     */
    public function isComplete(): bool
    {
        return $this->uploaded_chunks >= $this->total_chunks;
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    /**
     * Get upload progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_chunks == 0) {
            return 0;
        }
        
        return round(($this->uploaded_chunks / $this->total_chunks) * 100, 2);
    }

    /**
     * Calculate uploaded size
     */
    public function getUploadedSize(): int
    {
        return $this->chunks()
            ->where('status', FileChunk::STATUS_UPLOADED)
            ->sum('chunk_size');
    }

    /**
     * Get estimated time remaining
     */
    public function getEstimatedTimeRemaining(): ?int
    {
        if (!$this->started_at || $this->uploaded_chunks == 0) {
            return null;
        }

        $elapsed = Carbon::now()->diffInSeconds($this->started_at);
        $uploadedRatio = $this->uploaded_chunks / $this->total_chunks;
        
        if ($uploadedRatio == 0) {
            return null;
        }

        $totalEstimated = $elapsed / $uploadedRatio;
        return max(0, $totalEstimated - $elapsed);
    }

    /**
     * Update upload progress
     */
    public function updateProgress(): void
    {
        $this->uploaded_chunks = $this->completedChunks()->count();
        
        if ($this->isComplete() && $this->status === self::STATUS_UPLOADING) {
            $this->status = self::STATUS_ASSEMBLING;
        }
        
        $this->save();
    }

    /**
     * Mark session as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_UPLOADING,
            'started_at' => Carbon::now()
        ]);
    }

    /**
     * Mark session as completed
     */
    public function markAsCompleted(string $finalFilePath = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => Carbon::now(),
            'final_file_path' => $finalFilePath
        ]);
    }

    /**
     * Mark session as failed
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1
        ]);
    }

    /**
     * Get temp directory path
     */
    public function getTempDirectoryPath(): string
    {
        if (!$this->temp_directory) {
            $tempDir = storage_path('app/temp/uploads/' . $this->session_id);
            $this->update(['temp_directory' => $tempDir]);
            return $tempDir;
        }
        
        return $this->temp_directory;
    }

    /**
     * Clean up temp files
     */
    public function cleanup(): void
    {
        if ($this->temp_directory && is_dir($this->temp_directory)) {
            // Remove all files in temp directory
            $files = glob($this->temp_directory . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            
            // Remove directory
            rmdir($this->temp_directory);
        }
        
        // Delete chunks from database
        $this->chunks()->delete();
        
        // Delete session
        $this->delete();
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_UPLOADING,
            self::STATUS_ASSEMBLING,
            self::STATUS_PROCESSING
        ]);
    }

    /**
     * Scope for expired sessions
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }
}