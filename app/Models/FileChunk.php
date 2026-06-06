<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FileChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'chunk_number',
        'chunk_size',
        'chunk_hash',
        'chunk_path',
        'status',
        'uploaded_at',
        'retry_count',
        'error_message'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'chunk_number' => 'integer',
        'chunk_size' => 'integer',
        'retry_count' => 'integer'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_VERIFIED = 'verified';
    const STATUS_FAILED = 'failed';

    /**
     * Get the upload session that owns this chunk
     */
    public function uploadSession(): BelongsTo
    {
        return $this->belongsTo(UploadSession::class, 'session_id', 'session_id');
    }

    /**
     * Mark chunk as uploaded
     */
    public function markAsUploaded(string $chunkPath, string $hash = null): void
    {
        $this->update([
            'status' => self::STATUS_UPLOADED,
            'chunk_path' => $chunkPath,
            'chunk_hash' => $hash,
            'uploaded_at' => Carbon::now()
        ]);
    }

    /**
     * Mark chunk as verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED
        ]);
    }

    /**
     * Mark chunk as failed
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
     * Check if chunk exists on disk
     */
    public function existsOnDisk(): bool
    {
        return $this->chunk_path && file_exists($this->chunk_path);
    }

    /**
     * Verify chunk integrity
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->existsOnDisk() || !$this->chunk_hash) {
            return false;
        }

        $actualHash = md5_file($this->chunk_path);
        return $actualHash === $this->chunk_hash;
    }

    /**
     * Get chunk file size
     */
    public function getActualSize(): int
    {
        if (!$this->existsOnDisk()) {
            return 0;
        }

        return filesize($this->chunk_path);
    }

    /**
     * Delete chunk file from disk
     */
    public function deleteFile(): void
    {
        if ($this->existsOnDisk()) {
            unlink($this->chunk_path);
        }
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clean up chunk file when model is deleted
        static::deleting(function ($chunk) {
            $chunk->deleteFile();
        });
    }

    /**
     * Scope for uploaded chunks
     */
    public function scopeUploaded($query)
    {
        return $query->where('status', self::STATUS_UPLOADED);
    }

    /**
     * Scope for failed chunks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending chunks
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}