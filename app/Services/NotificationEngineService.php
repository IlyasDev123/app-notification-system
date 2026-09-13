<?php

namespace App\Services;

use App\Contracts\NotificationLogRepositoryInterface;
use App\Models\NotificationLog;
use Throwable;

class NotificationEngineService
{
    public function __construct(
        protected NotificationLogRepositoryInterface $logRepository
    ) {}

    /**
     * Dispatch notification through provider and persist audit log.
     */
    public function dispatch(
        string $tenantId,
        string $channel,
        string $recipient,
        string $content,
        array $options = []
    ): NotificationLog {
        $provider = $options['provider'] ?? $this->resolveDefaultProvider($channel);

        try {
            // Simulated delivery call to provider strategy/client
            $responsePayload = $this->sendViaProvider($provider, $channel, $recipient, $content, $options);

            return $this->logRepository->log([
                'tenant_id' => $tenantId,
                'channel'   => $channel,
                'provider'  => $provider,
                'recipient' => $recipient,
                'status'    => 'sent',
                'response'  => $responsePayload,
            ]);
        } catch (Throwable $e) {
            $this->logRepository->log([
                'tenant_id' => $tenantId,
                'channel'   => $channel,
                'provider'  => $provider,
                'recipient' => $recipient,
                'status'    => 'failed',
                'response'  => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }

    protected function resolveDefaultProvider(string $channel): string
    {
        return match ($channel) {
            'email' => 'mailgun',
            'sms'   => 'twilio',
            'push'  => 'fcm',
            default => 'system',
        };
    }

    protected function sendViaProvider(
        string $provider,
        string $channel,
        string $recipient,
        string $content,
        array $options
    ): array {
        return [
            'status'        => 'delivered',
            'message_id'    => 'msg_' . bin2hex(random_bytes(8)),
            'provider_used' => $provider,
        ];
    }
}
