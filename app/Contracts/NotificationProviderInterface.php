<?php

namespace App\Contracts;

interface NotificationProviderInterface
{
    /**
     * Send notification using vendor-specific payload and credentials.
     */
    public function send(string $recipient, string $content, array $decryptedCredentials, array $options = []): array;
}
