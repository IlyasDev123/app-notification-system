<?php

namespace App\Services;

use App\Contracts\EncryptionServiceInterface;
use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\NotificationLog;
use Exception;

class NotificationEngineService
{
    public function __construct(
        private readonly TenantProviderRepositoryInterface $providerRepository,
        private readonly EncryptionServiceInterface $encryptionService,
        private readonly NotificationProviderFactory $providerFactory
    ) {}

    public function dispatch(
        string $tenantId,
        string $channel,
        string $recipient,
        string $content,
        array $options = []
    ): NotificationLog {
        $tenantProvider = $this->providerRepository->findByTenantAndChannel($tenantId, $channel);

        if (!$tenantProvider || !$tenantProvider->is_active) {
            throw new Exception("No active provider configured for tenant [{$tenantId}] on channel [{$channel}].");
        }

        $credentials = $this->encryptionService->decryptArray($tenantProvider->credentials);
        $provider = $this->providerFactory->make($tenantProvider->provider_name);

        $response = $provider->send($recipient, $content, $credentials, $options);

        return new NotificationLog([
            'tenant_id' => $tenantId,
            'channel'   => $channel,
            'provider'  => $tenantProvider->provider_name,
            'recipient' => $recipient,
            'status'    => $response['status'] ?? 'sent',
            'response'  => $response,
        ]);
    }
}
