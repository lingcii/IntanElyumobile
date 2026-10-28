<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $apiKey = config('services.resend.key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'RESEND_API_KEY environment variable not configured'];
        }

        // Set default verified sender address
        if (!$from) {
            $configuredFrom = env('MAIL_FROM_ADDRESS', 'support@intan-elyu.online');
            $fromName = env('MAIL_FROM_NAME', 'Intan Elyu Support');
            $from = $fromName . ' <' . $configuredFrom . '>';
        }

        $payload = [
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $htmlContent,
        ];

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->post('https://api.resend.com/emails', $payload);
        } catch (\Throwable $e) {
            return ['success' => false, 'id' => null, 'error' => $e->getMessage()];
        }

        $result = $response->json() ?? [];
        if ($response->successful() && isset($result['id'])) {
            return ['success' => true, 'id' => $result['id'], 'error' => null];
        }

        $msg = $result['message'] ?? ($result['error'] ?? "HTTP Status {$response->status()}");
        return ['success' => false, 'id' => null, 'error' => $msg];
    }
}
