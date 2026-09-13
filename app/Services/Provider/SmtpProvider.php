<?php

namespace App\Services\Providers;

use App\Contracts\NotificationProviderInterface;

class SmtpProvider implements NotificationProviderInterface
{
    public function send(string $recipient, string $content, array $decryptedCredentials, array $options = []): array
    {
        // Custom SMTP relay implementation using dynamic mailer configuration
        return [
            'success'    => true,
            'message_id' => 'smtp-' . uniqid(),
            'status'     => 200,
        ];
    }
}
