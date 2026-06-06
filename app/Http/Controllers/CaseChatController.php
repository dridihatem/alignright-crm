<?php

namespace App\Http\Controllers;

use App\Models\CaseMessage;
use App\Models\CasePatient;
use App\Models\Notification;
use Illuminate\Http\Request;

class CaseChatController extends Controller
{
    /**
     * Fetch messages for a case+channel (and mark incoming ones as read).
     */
    public function messages(Request $request, $caseId, $channel)
    {
        $case = CasePatient::findOrFail($caseId);
        $user = $request->user();

        $this->authorizeChannel($case, $channel, $user);

        // Doctors see counterparts by role only (no personal names/avatars).
        $anonymize = $this->roleName($user) === 'doctor';

        // Mark messages addressed to me as read
        CaseMessage::where('case_id', $case->id)
            ->where('channel', $channel)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = CaseMessage::with('sender')
            ->where('case_id', $case->id)
            ->where('channel', $channel)
            ->orderBy('created_at')
            ->get()
            ->map(function ($m) use ($user, $anonymize) {
                $isMine = (int) $m->sender_id === (int) $user->id;

                if ($anonymize && !$isMine) {
                    $senderName = $m->sender_role ? __('master.role_' . $m->sender_role) : __('master.not_available');
                    $avatar = null;
                } else {
                    $senderName = $m->sender
                        ? trim(($m->sender->name ?? '') . ' ' . ($m->sender->surname ?? ''))
                        : __('master.not_available');
                    $avatar = $m->sender->photo_url ?? null;
                }

                return [
                    'id'        => $m->id,
                    'body'      => $m->body,
                    'mine'      => $isMine,
                    'sender'    => $senderName,
                    'role'      => $m->sender_role,
                    'avatar'    => $avatar,
                    'time'      => $m->created_at->format('d M Y H:i'),
                    'timeago'   => $m->created_at->diffForHumans(),
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    /**
     * Store a new message in a case+channel and notify the counterpart.
     */
    public function send(Request $request, $caseId, $channel)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $case = CasePatient::findOrFail($caseId);
        $user = $request->user();
        $role = $this->roleName($user);

        $this->authorizeChannel($case, $channel, $user);

        $message = CaseMessage::create([
            'case_id'     => $case->id,
            'channel'     => $channel,
            'sender_id'   => $user->id,
            'sender_role' => $role,
            'body'        => $request->input('body'),
        ]);

        $this->notifyCounterpart($case, $channel, $role, $user, $message);

        return response()->json([
            'message' => [
                'id'      => $message->id,
                'body'    => $message->body,
                'mine'    => true,
                'sender'  => trim(($user->name ?? '') . ' ' . ($user->surname ?? '')),
                'role'    => $role,
                'avatar'  => $user->photo_url ?? null,
                'time'    => $message->created_at->format('d M Y H:i'),
                'timeago' => $message->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Verify the user may participate in the given channel for the case.
     */
    private function authorizeChannel(CasePatient $case, string $channel, $user): void
    {
        $roles = CaseMessage::CHANNELS[$channel] ?? abort(404);
        $role = $this->roleName($user);

        abort_unless(in_array($role, $roles, true), 403);

        // The user must be the participant assigned to this case for their role (admin can access any case).
        if ($role !== 'admin') {
            $assignedId = CaseMessage::userIdForRole($case, $role);
            abort_unless($assignedId && (int) $assignedId === (int) $user->id, 403);
        }

        // The counterpart must be assigned for the channel to be usable (admin counterpart is always available).
        $counterpart = CaseMessage::counterpartRole($channel, $role);
        if ($counterpart && $counterpart !== 'admin') {
            abort_unless(CaseMessage::userIdForRole($case, $counterpart), 403);
        }
    }

    /**
     * Create a notification for the counterpart of the conversation.
     */
    private function notifyCounterpart(CasePatient $case, string $channel, string $senderRole, $sender, CaseMessage $message): void
    {
        $counterpart = CaseMessage::counterpartRole($channel, $senderRole);
        if (!$counterpart || $counterpart === 'admin') {
            // Admins have no notification dropdown; they see chat inside the case view.
            return;
        }

        $recipientId = CaseMessage::userIdForRole($case, $counterpart);
        if (!$recipientId) {
            return;
        }

        $senderName = trim(($sender->name ?? '') . ' ' . ($sender->surname ?? ''));
        $excerpt = \Illuminate\Support\Str::limit($message->body, 80);

        $data = [
            'title'   => $case->case_id . ' - ' . __('master.new_message'),
            'message' => $senderName . ': ' . $excerpt,
            'type'    => 'case_message',
            'status'  => 'active',
            'case_id' => $case->id,
        ];

        $data[$counterpart . '_id'] = $recipientId;

        Notification::create($data);
    }

    private function roleName($user): string
    {
        return optional($user->role)->name ?? '';
    }
}
