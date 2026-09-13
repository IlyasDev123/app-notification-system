<?php

namespace App\Services\Providers;

use App\Contracts\NotificationProviderInterface;
use Illuminate\Support\Facades\Http;

class TwilioProvider implements NotificationProviderInterface
{
    public function send(string $recipient, string $content, array $decryptedCredentials, array $options = []): array
    {
        $accountSid = $decryptedCredentials['account_sid'] ?? '';
        $authToken  = $decryptedCredentials['auth_token'] ?? '';
        $fromNumber = $decryptedCredentials['from_number'] ?? '';

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $fromNumber,
                'To'   => $recipient,
                'Body' => $content,
            ]);

        return [
            'success'    => $response->successful(),
            'message_id' => $response->json('sid'),
            'status'     => $response->status(),
        ];
    }
}
