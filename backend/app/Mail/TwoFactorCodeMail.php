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
    public function __construct(public User $user, public string $code, public string $action = 'activation')
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->action === 'deactivation'
            ? '⚠️ Intan Elyu 2FA Deactivation Security Code: ' . $this->code
            : '🔐 Your Intan Elyu 2FA Verification Code: ' . $this->code;

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $subtitle = $this->action === 'deactivation'
            ? 'Deactivation Security Verification'
            : 'Two-Factor Security Verification';

        $instruction = $this->action === 'deactivation'
            ? 'Use the 6-digit verification code below to authorize deactivating Two-Factor Authentication on your account:'
            : 'Use the 6-digit verification code below to verify your device login and secure your account:';

        $warning = $this->action === 'deactivation'
            ? "This security code will expire in <strong style='color: #0f172a;'>10 minutes</strong>.<br>If you did not request to deactivate 2FA, someone may be attempting to alter your security settings. Please secure your account immediately."
            : "This security code will expire in <strong style='color: #0f172a;'>10 minutes</strong>.<br>If you did not request this 2FA verification, please secure your password immediately.";

        return new Content(
            htmlString: "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; padding: 32px 16px; margin: 0;'>
                <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);'>
                    <!-- Royal Blue Header Banner -->
                    <div style='background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%); padding: 32px 24px; text-align: center;'>
                        <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.3px;'>Intan Elyu Tourism</h2>
                        <p style='color: rgba(255, 255, 255, 0.9); font-size: 13px; margin: 4px 0 0 0; font-weight: 600;'>{$subtitle}</p>
                    </div>

                    <!-- Clean White Body -->
                    <div style='padding: 32px 28px; background: #ffffff;'>
                        <p style='font-size: 15px; color: #0f172a; margin: 0 0 12px 0;'>Hello <strong>" . e($this->user->name) . "</strong>,</p>
                        <p style='font-size: 14px; color: #475569; line-height: 1.6; margin: 0 0 20px 0;'>{$instruction}</p>
                        
                        <!-- High Visibility Code Box -->
                        <div style='background: #eff6ff; border: 2px dashed #3b82f6; border-radius: 14px; padding: 20px; text-align: center; margin: 24px 0;'>
                            <span style='font-size: 34px; font-weight: 900; letter-spacing: 10px; color: #1e3a8a; font-family: monospace; display: inline-block;'>" . e($this->code) . "</span>
                        </div>
                        
                        <p style='font-size: 12.5px; color: #64748b; text-align: center; line-height: 1.5; margin: 0;'>
                            {$warning}
                        </p>
                    </div>

                    <!-- Clean Slate Footer -->
                    <div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center;'>
                        <p style='font-size: 11.5px; color: #94a3b8; margin: 0;'>
                            © " . date('Y') . " Intan Elyu Tourism Management System. All rights reserved.
                        </p>
                    </div>
                </div>
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
