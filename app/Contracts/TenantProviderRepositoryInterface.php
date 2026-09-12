<?php

namespace App\Contracts;
use App\Models\TenantProvider;
use Illuminate\Database\Eloquent\Collection;

interface TenantProviderRepositoryInterface
{
    public function findByTenantAndChannel(string $tenantId, string $channel): ?TenantProvider;

    public function getByTenantId(string $tenantId): Collection;

    public function updateOrCreate(string $tenantId, string $channel, array $attributes): TenantProvider;

    public function delete(string $tenantId, string $providerId): bool;
}
