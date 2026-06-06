<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmContact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'position',
        'notes',
        'status',
        'source',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function interactions(): HasMany
    {
        return $this->hasMany(CrmInteraction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'active' => 'success',
            'inactive' => 'secondary',
            'prospect' => 'warning',
            'customer' => 'primary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getSourceBadgeAttribute(): string
    {
        $badges = [
            'website' => 'info',
            'referral' => 'success',
            'cold_call' => 'warning',
            'email' => 'primary',
            'social_media' => 'info',
            'other' => 'secondary',
        ];

        return $badges[$this->source] ?? 'secondary';
    }
}
