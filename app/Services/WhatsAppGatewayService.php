<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppGatewayService
{
    public function isEnabled(): bool
    {
        return SiteSetting::getBoolean('wa_gateway_enabled');
    }

    public function sendText(string $to, string $text, ?string $idempotencyKey = null): array
    {
        return $this->send([
            'action' => 'send',
            'to' => $this->normalizePhone($to),
            'text' => $text,
        ], $idempotencyKey);
    }

    /**
     * @param  array<int, array<string, string>>  $buttons
     * @return array<string, mixed>
     */
    public function sendInteractive(string $to, string $text, array $buttons, ?string $footer = null, ?string $idempotencyKey = null): array
    {
        return $this->send([
            'action' => 'send',
            'to' => $this->normalizePhone($to),
            'interactive' => [
                'type' => 'template',
                'text' => $text,
                'footer' => $footer ?: 'PT Amara Al Medina Travel',
                'buttons' => $buttons,
            ],
        ], $idempotencyKey);
    }

    public function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function send(array $payload, ?string $idempotencyKey = null): array
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Gateway WhatsApp belum diaktifkan.');
        }

        $url = SiteSetting::getEncryptedValue('wa_gateway_post_url');

        if (blank($url)) {
            throw new RuntimeException('URL gateway WhatsApp belum diisi.');
        }

        if (blank($payload['to'] ?? null)) {
            throw new RuntimeException('Nomor WhatsApp tujuan tidak valid.');
        }

        $response = $this->request($idempotencyKey)->post($url, $payload);
        $body = $response->json();

        if (! $response->successful()) {
            throw new RuntimeException('Gateway WhatsApp menolak request.');
        }

        if (is_array($body) && (($body['ok'] ?? $body['success'] ?? true) === false)) {
            throw new RuntimeException('Gateway WhatsApp gagal mengirim pesan.');
        }

        return is_array($body) ? $body : [];
    }

    private function request(?string $idempotencyKey): PendingRequest
    {
        $request = Http::asJson()
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 500, throw: false);

        $headers = [];

        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $authMode = SiteSetting::getValue('wa_gateway_auth_mode', 'none');

        if ($authMode === 'basic') {
            $request = $request->withBasicAuth(
                (string) SiteSetting::getValue('wa_gateway_basic_username', ''),
                (string) SiteSetting::getEncryptedValue('wa_gateway_basic_password', ''),
            );
        } elseif ($authMode === 'header') {
            $headerName = trim((string) SiteSetting::getValue('wa_gateway_header_name', 'X-API-Key'));
            $headerValue = SiteSetting::getEncryptedValue('wa_gateway_header_value', '');

            if ($headerName !== '' && filled($headerValue)) {
                $headers[$headerName] = $headerValue;
            }
        } elseif (in_array($authMode, ['bearer', 'jwt_static'], true)) {
            $token = SiteSetting::getEncryptedValue('wa_gateway_bearer_token', '');

            if (filled($token)) {
                $request = $request->withToken($token);
            }
        }

        return $headers === [] ? $request : $request->withHeaders($headers);
    }
}
