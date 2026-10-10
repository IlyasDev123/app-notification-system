<?php

namespace App\Services;

use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\TenantProvider;
use Illuminate\Database\Eloquent\Collection;

class TenantProviderService
{
    public function __construct(
        private readonly TenantProviderRepositoryInterface $providerRepository,
    ) {}

    public function configureProvider(
        string $tenantId,
        string $channel,
        string $providerName,
        array $rawCredentials,
        int $priority = 1,
        bool $isActive = true
    ): TenantProvider {
        // The TenantProvider model uses encrypted:array cast, so Eloquent handles
        // encryption automatically. Pass the raw array directly.
        return $this->providerRepository->updateOrCreate($tenantId, $channel, [
            'provider_name' => $providerName,
            'credentials'   => $rawCredentials,
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
