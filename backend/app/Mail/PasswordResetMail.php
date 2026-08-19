<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public string $token, public string $otpCode = '')
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'acekillersmile@gmail.com');
        $fromName = config('mail.from.name', 'Intan-Elyu Customer Support');

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            subject: '🔐 ' . ($this->otpCode ? $this->otpCode . ' - ' : '') . 'Reset Your Password - Intan Elyu',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = 'https://app.intan-elyu.online/?view=reset-password&token=' . $this->token . '&email=' . urlencode($this->user->email);
        $appIntentUrl = 'intent://app.intan-elyu.online/?view=reset-password&token=' . $this->token . '&email=' . urlencode($this->user->email) . '#Intent;scheme=https;package=com.intan.elyu;S.browser_fallback_url=' . urlencode($resetUrl) . ';end;';

        return new Content(
            view: 'emails.password_reset',
            with: [
                'userName'     => $this->user->name,
                'userEmail'    => $this->user->email,
                'resetUrl'     => $resetUrl,
                'appIntentUrl' => $appIntentUrl,
                'otpCode'      => $this->otpCode,
                'token'        => $this->token,
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
