<?php

namespace App\Repositories;

use App\Contracts\TenantApiKeyRepositoryInterface;
use App\Models\TenantApiKey;
use Illuminate\Database\Eloquent\Collection;

class EloquentTenantApiKeyRepository implements TenantApiKeyRepositoryInterface
{
    public function createForTenant(string $tenantId, array $data): TenantApiKey
    {
        return TenantApiKey::create(array_merge($data, [
            'tenant_id' => $tenantId,
        ]));
    }

    public function findByIdAndTenant(string $keyId, string $tenantId): ?TenantApiKey
    {
        return TenantApiKey::where('tenant_id', $tenantId)
            ->where('id', $keyId)
            ->first();
    }

    public function getActiveKeysForTenant(string $tenantId): Collection
    {
        return TenantApiKey::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    public function revokeKey(string $keyId, string $tenantId): bool
    {
        $key = $this->findByIdAndTenant($keyId, $tenantId);

        if (!$key) {
            return false;
        }

        return $key->update(['is_active' => false]);
    }
}