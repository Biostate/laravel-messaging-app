<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebhookSiteService
{
    private ?string $authKey;

    public function __construct()
    {
        $this->authKey = config('services.webhook-site.auth_key');
    }

    public function sendMessage(string $phoneNumber, string $content): ?array
    {
        if (! $this->authKey) {
            throw new \Exception('Webhook auth key is not configured');
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-ins-auth-key' => $this->authKey,
            ])->post(config('services.webhook-site.base_url').'/'.config('services.webhook-site.unique_id'), [
                'to' => $phoneNumber,
                'content' => $content,
            ]);

            $response->throw();

            $data = $response->json();

            if (! $data || ! isset($data['messageId'])) {
                throw new \Exception('Invalid response format from webhook service');
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Failed to send message via webhook: '.$e->getMessage());
        }
    }
}
