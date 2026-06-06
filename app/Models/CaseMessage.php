<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseMessage extends Model
{
    protected $fillable = [
        'case_id',
        'channel',
        'sender_id',
        'sender_role',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Channel => [roleA, roleB] participating in the conversation.
     */
    public const CHANNELS = [
        'admin_doctor'          => ['admin', 'doctor'],
        'doctor_technician'     => ['doctor', 'technician'],
        'doctor_laboratory'     => ['doctor', 'laboratory'],
        'technician_laboratory' => ['technician', 'laboratory'],
    ];

    public function case()
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Channels a given role is allowed to participate in.
     */
    public static function channelsForRole(string $role): array
    {
        $channels = [];
        foreach (self::CHANNELS as $key => $roles) {
            if (in_array($role, $roles, true)) {
                $channels[] = $key;
            }
        }
        return $channels;
    }

    /**
     * The role on the other side of a channel for the given sender role.
     */
    public static function counterpartRole(string $channel, string $senderRole): ?string
    {
        $roles = self::CHANNELS[$channel] ?? null;
        if (!$roles) {
            return null;
        }
        foreach ($roles as $r) {
            if ($r !== $senderRole) {
                return $r;
            }
        }
        return null;
    }

    /**
     * Resolve the user id assigned to a given role on a case.
     */
    public static function userIdForRole(CasePatient $case, string $role): ?int
    {
        return match ($role) {
            'doctor'     => $case->doctor_id,
            'technician' => $case->technician_id,
            'laboratory' => $case->laboratory_id,
            default      => null, // admin is not tied to a single case
        };
    }

    /**
     * Total unread messages addressed to the given user across all their threads.
     */
    public static function unreadForUser($user): int
    {
        $role = optional($user->role)->name ?? '';
        $channels = self::channelsForRole($role);
        if (empty($channels)) {
            return 0;
        }

        $query = self::whereIn('channel', $channels)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at');

        if ($role === 'admin') {
            $query->where('channel', 'admin_doctor');
        } else {
            $column = match ($role) {
                'doctor'     => 'doctor_id',
                'technician' => 'technician_id',
                'laboratory' => 'laboratory_id',
                default      => null,
            };
            if (!$column) {
                return 0;
            }
            $query->whereIn('case_id', CasePatient::where($column, $user->id)->pluck('id'));
        }

        return $query->count();
    }
}
