<?php

namespace App\Http\Controllers;

use App\Models\CaseMessage;
use App\Models\CasePatient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessagesController extends Controller
{
    /**
     * Messenger-style inbox: list of conversations the user participates in.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = optional($user->role)->name ?? '';

        $conversations = $this->buildConversations($user, $role);

        return view('messages.index', compact('conversations', 'role'));
    }

    /**
     * JSON list of conversations (used to refresh the sidebar).
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        $role = optional($user->role)->name ?? '';

        return response()->json(['conversations' => $this->buildConversations($user, $role)]);
    }

    private function buildConversations($user, string $role): array
    {
        $channels = CaseMessage::channelsForRole($role);
        if (empty($channels)) {
            return [];
        }

        // Distinct (case_id, channel) threads that already have messages
        $threads = CaseMessage::select('case_id', 'channel')
            ->whereIn('channel', $channels)
            ->groupBy('case_id', 'channel')
            ->get();

        if ($threads->isEmpty()) {
            return [];
        }

        $caseIds = $threads->pluck('case_id')->unique()->all();
        $cases = CasePatient::with(['patient', 'doctor', 'technician', 'laboratory'])
            ->whereIn('id', $caseIds)
            ->get()
            ->keyBy('id');

        $showRoute = match ($role) {
            'admin'      => 'admin.cases.show',
            'doctor'     => 'doctor.cases.show',
            'technician' => 'technician.cases.show',
            'laboratory' => 'laboratory.cases.show',
            default      => null,
        };

        $result = [];

        foreach ($threads as $thread) {
            $case = $cases->get($thread->case_id);
            if (!$case || !$this->canAccess($case, $thread->channel, $user, $role)) {
                continue;
            }

            $last = CaseMessage::where('case_id', $thread->case_id)
                ->where('channel', $thread->channel)
                ->latest()
                ->first();

            $unread = CaseMessage::where('case_id', $thread->case_id)
                ->where('channel', $thread->channel)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();

            $counterpart = CaseMessage::counterpartRole($thread->channel, $role);
            // Doctors see counterparts by role label only (no personal names/avatars).
            [$name, $avatar] = $this->counterpartInfo($case, $counterpart, $role === 'doctor');

            $result[] = [
                'case_id'           => $case->id,
                'channel'           => $thread->channel,
                'case_label'        => $case->case_id,
                'patient'           => trim(($case->patient->name ?? '') . ' ' . ($case->patient->surname ?? '')),
                'counterpart_role'  => $counterpart,
                'counterpart_label' => $counterpart ? __('master.role_' . $counterpart) : '',
                'name'              => $name,
                'avatar'            => $avatar,
                'last'              => $last ? Str::limit($last->body, 42) : '',
                'last_time'         => $last ? $last->created_at->diffForHumans() : '',
                'last_ts'           => $last ? $last->created_at->timestamp : 0,
                'unread'            => $unread,
                'case_url'          => $showRoute ? route($showRoute, $case->id) : null,
            ];
        }

        usort($result, fn ($a, $b) => $b['last_ts'] <=> $a['last_ts']);

        return $result;
    }

    private function canAccess(CasePatient $case, string $channel, $user, string $role): bool
    {
        $roles = CaseMessage::CHANNELS[$channel] ?? null;
        if (!$roles || !in_array($role, $roles, true)) {
            return false;
        }

        if ($role !== 'admin') {
            $assigned = CaseMessage::userIdForRole($case, $role);
            if (!$assigned || (int) $assigned !== (int) $user->id) {
                return false;
            }
        }

        $counterpart = CaseMessage::counterpartRole($channel, $role);
        if ($counterpart && $counterpart !== 'admin' && !CaseMessage::userIdForRole($case, $counterpart)) {
            return false;
        }

        return true;
    }

    /**
     * @return array{0:string,1:?string} [display name, avatar url]
     */
    private function counterpartInfo(CasePatient $case, ?string $counterpart, bool $anonymize = false): array
    {
        if (!$counterpart || $counterpart === 'admin') {
            return [__('master.role_admin'), null];
        }

        // Doctors only see the role label, never the technician/laboratory name.
        if ($anonymize) {
            return [__('master.role_' . $counterpart), null];
        }

        $u = $case->{$counterpart} ?? null;
        if ($u) {
            return [trim(($u->name ?? '') . ' ' . ($u->surname ?? '')), $u->photo_url ?? null];
        }

        return [__('master.role_' . $counterpart), null];
    }
}
