<?php

namespace App\Services;

use App\Contracts\TenantProviderRepositoryInterface;
use App\Contracts\EncryptionServiceInterface;
use App\Models\TenantProvider;
use Illuminate\Database\Eloquent\Collection;

class TenantProviderService
{
    public function __construct(
        private readonly TenantProviderRepositoryInterface $providerRepository,
        private readonly EncryptionServiceInterface $encryptionService
    ) {}

    public function configureProvider(
        string $tenantId,
        string $channel,
        string $providerName,
        array $rawCredentials,
        int $priority = 1,
        bool $isActive = true
    ): TenantProvider {
        // 1. Dynamic Encryption of Sensitive Vendor API Credentials
        $encryptedCredentials = $this->encryptionService->encryptArray($rawCredentials);

        // 2. Persist Provider Settings via Repository
        return $this->providerRepository->updateOrCreate($tenantId, $channel, [
            'provider_name' => $providerName,
            'credentials'   => $encryptedCredentials,
            'priority'      => $priority,
            'is_active'     => $isActive,
        ]);
    }

    public function listProvidersForTenant(string $tenantId): Collection
    {
        return $this->providerRepository->getByTenantId($tenantId);
    }

    public function removeProvider(string $tenantId, string $providerId): bool
    {
        return $this->providerRepository->delete($tenantId, $providerId);
    }
}