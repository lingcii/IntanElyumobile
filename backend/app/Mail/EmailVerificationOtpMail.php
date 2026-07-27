<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $otp
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔒 {$this->otp} is your Intan Elyu Verification Code",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification_otp',
            with: [
                'name' => $this->name,
                'otp'  => $this->otp,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
