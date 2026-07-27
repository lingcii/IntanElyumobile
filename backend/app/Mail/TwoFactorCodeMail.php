<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public string $code)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Your Intan Elyu 2FA Verification Code: ' . $this->code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "
            <div style='font-family: Helvetica, Arial, sans-serif; max-width: 520px; margin: 0 auto; background: #0f172a; color: #ffffff; padding: 32px; border-radius: 20px; border: 1px solid #1e293b; box-shadow: 0 10px 30px rgba(0,0,0,0.5);'>
                <div style='text-align: center; margin-bottom: 24px;'>
                    <h2 style='color: #38bdf8; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;'>Intan Elyu Tourism</h2>
                    <p style='color: #94a3b8; font-size: 13px; margin-top: 4px; font-weight: 600;'>Two-Factor Security Verification</p>
                </div>
                <p style='font-size: 15px; color: #e2e8f0;'>Hello <strong>" . e($this->user->name) . "</strong>,</p>
                <p style='font-size: 14px; color: #cbd5e1; line-height: 1.6;'>Use the 6-digit verification code below to verify your device login and secure your account:</p>
                <div style='background: rgba(56, 189, 248, 0.1); border: 2px dashed #38bdf8; border-radius: 16px; padding: 20px; text-align: center; margin: 24px 0;'>
                    <span style='font-size: 34px; font-weight: 900; letter-spacing: 10px; color: #38bdf8; font-family: monospace;'>" . e($this->code) . "</span>
                </div>
                <p style='font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.5;'>
                    This security code will expire in <strong>10 minutes</strong>.<br>If you did not request this 2FA verification, please secure your password immediately.
                </p>
                <hr style='border: none; border-top: 1px solid rgba(255,255,255,0.08); margin: 24px 0;'>
                <p style='font-size: 11px; color: rgba(148,163,184,0.6); text-align: center; margin: 0;'>
                    © " . date('Y') . " Intan Elyu Tourism Management System. All rights reserved.
                </p>
            </div>
            ",
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
