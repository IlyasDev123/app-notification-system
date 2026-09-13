<?php

namespace App\Services\Providers;

use App\Contracts\NotificationProviderInterface;
use Illuminate\Support\Facades\Http;

class MailgunProvider implements NotificationProviderInterface
{
    public function send(string $recipient, string $content, array $decryptedCredentials, array $options = []): array
    {
        $domain = $decryptedCredentials['domain'] ?? '';
        $apiKey = $decryptedCredentials['api_key'] ?? '';

        $response = Http::withBasicAuth('api', $apiKey)
            ->asForm()
            ->post("https://api.mailgun.net/v3/{$domain}/messages", [
                'from'    => $options['from'] ?? "No-Reply <mailgun@{$domain}>",
                'to'      => $recipient,
                'subject' => $options['subject'] ?? 'Notification',
                'text'    => $content,
            ]);

        return [
            'success'    => $response->successful(),
            'message_id' => $response->json('id'),
            'status'     => $response->status(),
        ];
    }
}
