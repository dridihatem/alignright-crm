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

class NewCaseForPricingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    public $doctor;

    /**
     * Create a new message instance.
     */
    public function __construct(CasePatient $case, User $doctor)
    {
        $this->case = $case;
        $this->doctor = $doctor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Case Requires Pricing - Case #{$this->case->case_id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-case-for-pricing',
            with: [
                'case' => $this->case,
                'doctor' => $this->doctor,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
