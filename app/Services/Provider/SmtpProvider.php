<?php

namespace App\Services\Provider;

use App\Contracts\NotificationProviderInterface;
use Illuminate\Support\Facades\Mail;

class SmtpProvider implements NotificationProviderInterface
{
    public function send(string $recipient, string $content, array $decryptedCredentials, array $options = []): array
    {
        config([
            'mail.mailers.smtp_runtime' => [
                'transport'  => 'smtp',
                'host'       => $decryptedCredentials['host'],
                'port'       => (int) ($decryptedCredentials['port'] ?? 587),
                'encryption' => $decryptedCredentials['encryption'] ?? 'tls',
                'username'   => $decryptedCredentials['username'] ?? null,
                'password'   => $decryptedCredentials['password'] ?? null,
                'timeout'    => 30,
            ],
        ]);

        $fromAddress = $decryptedCredentials['from_address']
            ?? $decryptedCredentials['username']
            ?? 'no-reply@example.com';

        $fromName  = $options['from_name'] ?? config('app.name');
        $subject   = $options['subject'] ?? 'Notification';
        $messageId = 'smtp-' . bin2hex(random_bytes(8));

        Mail::mailer('smtp_runtime')
            ->raw($content, function ($message) use ($recipient, $fromAddress, $fromName, $subject) {
                $message->to($recipient)
                    ->from($fromAddress, $fromName)
                    ->subject($subject);
            });

        return [
            'success'    => true,
            'message_id' => $messageId,
            'status'     => 200,
        ];
    }
}
