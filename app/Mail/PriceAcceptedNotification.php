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

class PriceAcceptedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    public $doctor;
    public $admin;
    public $price;

    /**
     * Create a new message instance.
     */
    public function __construct(CasePatient $case, User $doctor, User $admin, $price)
    {
        $this->case = $case;
        $this->doctor = $doctor;
        $this->admin = $admin;
        $this->price = $price;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Price Accepted for Case #{$this->case->case_id} - Ready for Treatment Plan",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.price-accepted',
            with: [
                'case' => $this->case,
                'doctor' => $this->doctor,
                'admin' => $this->admin,
                'price' => $this->price,
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
