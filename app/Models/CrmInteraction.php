<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmInteraction extends Model
{
    protected $fillable = [
        'contact_id',
        'user_id',
        'type',
        'subject',
        'description',
        'scheduled_at',
        'completed_at',
        'status',
        'priority',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeIconAttribute(): string
    {
        $icons = [
            'call' => 'ti-phone',
            'email' => 'ti-mail',
            'meeting' => 'ti-calendar',
            'note' => 'ti-note',
            'task' => 'ti-checklist',
            'follow_up' => 'ti-clock',
        ];

        return $icons[$this->type] ?? 'ti-note';
    }

    public function getPriorityBadgeAttribute(): string
    {
        $badges = [
            1 => 'secondary', // low
            2 => 'warning',   // medium
            3 => 'danger',    // high
        ];

        return $badges[$this->priority] ?? 'secondary';
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}
