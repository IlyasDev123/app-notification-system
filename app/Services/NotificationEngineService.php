<?php

namespace App\Services;

use App\Contracts\NotificationLogRepositoryInterface;
use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\NotificationLog;
use RuntimeException;
use Throwable;

class NotificationEngineService
{
    public function __construct(
        protected NotificationLogRepositoryInterface $logRepository,
        protected TenantProviderRepositoryInterface $providerRepository,
        protected NotificationProviderFactory $providerFactory,
    ) {}

    public function dispatch(
        string $tenantId,
        string $channel,
        string $recipient,
        string $content,
        array $options = []
    ): NotificationLog {
        $tenantProvider = $this->providerRepository->findByTenantAndChannel($tenantId, $channel);

        if (! $tenantProvider || ! $tenantProvider->is_active) {
            throw new RuntimeException(
                "No active provider configured for tenant [{$tenantId}] on channel [{$channel}]."
            );
        }

        $providerName  = $options['provider'] ?? $tenantProvider->provider_name;
        $credentials   = $tenantProvider->credentials; // auto-decrypted by encrypted:array cast

        try {
            $provider        = $this->providerFactory->make($providerName);
            $responsePayload = $provider->send($recipient, $content, $credentials, $options);

            return $this->logRepository->log([
                'tenant_id' => $tenantId,
                'channel'   => $channel,
                'provider'  => $providerName,
                'recipient' => $recipient,
                'status'    => 'sent',
                'response'  => $responsePayload,
            ]);
        } catch (Throwable $e) {
            $this->logRepository->log([
                'tenant_id' => $tenantId,
                'channel'   => $channel,
                'provider'  => $providerName,
                'recipient' => $recipient,
                'status'    => 'failed',
                'response'  => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }
}
