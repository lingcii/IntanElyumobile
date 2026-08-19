<?php

namespace App\Services;

class ResendMailService
{
    /**
     * Send email via Resend HTTPS API (Port 443 — 100% Railway compatible)
     *
     * @param string $to
     * @param string $subject
     * @param string $htmlContent
     * @param string|null $from
     * @return array ['success' => bool, 'id' => string|null, 'error' => string|null]
     */
    public static function send(string $to, string $subject, string $htmlContent, ?string $from = null): array
    {
        $apiKey = env('RESEND_API_KEY') ?: config('mail.mailers.resend.key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'RESEND_API_KEY environment variable not configured'];
        }

        // If from is not set, use custom verified domain if set or fallback to onboarding@resend.dev
        if (!$from) {
            $configuredFrom = env('MAIL_FROM_ADDRESS');
            if ($configuredFrom && str_contains($configuredFrom, 'intan-elyu.online')) {
                $from = 'Intan Elyu Support <' . $configuredFrom . '>';
            } else {
                $from = 'Intan Elyu <onboarding@resend.dev>';
            }
        }

        $payload = [
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $htmlContent,
        ];

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'error' => 'cURL Error: ' . $curlErr];
        }

        $result = json_decode($response, true) ?? [];
        if ($httpCode >= 200 && $httpCode < 300 && isset($result['id'])) {
            return ['success' => true, 'id' => $result['id'], 'error' => null];
        }

        $msg = $result['message'] ?? ($result['error'] ?? "HTTP Status $httpCode");
        return ['success' => false, 'error' => $msg];
    }
}
