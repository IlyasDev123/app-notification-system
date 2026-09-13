<?php

namespace App\Contracts;

use App\Models\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationLogRepositoryInterface
{
    /**
     * Store a new notification audit log.
     */
    public function log(array $data): NotificationLog;

    /**
     * Get paginated logs for a specific tenant.
     */
    public function getPaginatedForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get aggregated delivery statistics for a tenant.
     *
     * @return array{total: int, sent: int, failed: int, queued: int}
     */
    public function getStatsForTenant(string $tenantId): array;
}
