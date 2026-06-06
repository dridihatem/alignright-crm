<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $role;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $password, string $role = 'user')
    {
        $this->user = $user;
        $this->password = $password;
        $this->role = $role;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $roleText = ucfirst($this->role);
        return new Envelope(
            subject: "Welcome to Dental Clinic - Your {$roleText} Account Credentials",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-credentials',
            with: [
                'user' => $this->user,
                'password' => $this->password,
                'role' => $this->role,
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
