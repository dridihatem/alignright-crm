<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\CasePatient;
use App\Models\User;

class CaseAssignedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    public $assignedUser;
    public $assignedRole;

    /**
     * Create a new message instance.
     */
    public function __construct(CasePatient $case, User $assignedUser, string $assignedRole = 'technician')
    {
        $this->case = $case;
        $this->assignedUser = $assignedUser;
        $this->assignedRole = $assignedRole;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $roleText = ucfirst($this->assignedRole);
        return new Envelope(
            subject: "New Case Assignment - Case #{$this->case->case_id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.case-assigned',
            with: [
                'case' => $this->case,
                'assignedUser' => $this->assignedUser,
                'assignedRole' => $this->assignedRole,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
