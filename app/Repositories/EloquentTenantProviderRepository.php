<?php

namespace App\Repositories;

use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\TenantProvider;
use Illuminate\Database\Eloquent\Collection;

class EloquentTenantProviderRepository implements TenantProviderRepositoryInterface
{
    public function findByTenantAndChannel(string $tenantId, string $channel): ?TenantProvider
    {
        return TenantProvider::where('tenant_id', $tenantId)
            ->where('channel', $channel)
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();
    }

    public function getByTenantId(string $tenantId): Collection
    {
        return TenantProvider::where('tenant_id', $tenantId)->get();
    }

    public function updateOrCreate(string $tenantId, string $channel, array $attributes): TenantProvider
    {
        return TenantProvider::updateOrCreate(
            ['tenant_id' => $tenantId, 'channel' => $channel],
            $attributes
        );
    }

    public function delete(string $tenantId, string $providerId): bool
    {
        return (bool) TenantProvider::where('tenant_id', $tenantId)
            ->where('id', $providerId)
            ->delete();
    }
}