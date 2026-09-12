<?php

namespace App\Contracts;

use App\Models\TenantApiKey;
use Illuminate\Database\Eloquent\Collection;

interface TenantApiKeyRepositoryInterface
{
    public function createForTenant(string $tenantId, array $data): TenantApiKey;

    public function findByIdAndTenant(string $keyId, string $tenantId): ?TenantApiKey;

    public function getActiveKeysForTenant(string $tenantId): Collection;

    public function revokeKey(string $keyId, string $tenantId): bool;
}
