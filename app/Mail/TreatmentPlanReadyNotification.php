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

class TreatmentPlanReadyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    public $doctor;
    public $technician;

    /**
     * Create a new message instance.
     */
    public function __construct(CasePatient $case, User $doctor, User $technician)
    {
        $this->case = $case;
        $this->doctor = $doctor;
        $this->technician = $technician;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Treatment Plan Ready for Review - Case #{$this->case->case_id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.treatment-plan-ready',
            with: [
                'case' => $this->case,
                'doctor' => $this->doctor,
                'technician' => $this->technician,
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
