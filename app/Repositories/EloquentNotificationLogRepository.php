<?php

namespace App\Repositories;

use App\Contracts\NotificationLogRepositoryInterface;
use App\Models\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentNotificationLogRepository implements NotificationLogRepositoryInterface
{
    public function log(array $data): NotificationLog
    {
        return NotificationLog::create($data);
    }

    public function getPaginatedForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return NotificationLog::where('tenant_id', $tenantId)
            ->latest()
            ->paginate($perPage);
    }

    public function getStatsForTenant(string $tenantId): array
    {
        $counts = NotificationLog::where('tenant_id', $tenantId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $sent   = $counts['sent'] ?? 0;
        $failed = $counts['failed'] ?? 0;
        $queued = $counts['queued'] ?? 0;

        return [
            'total'  => $sent + $failed + $queued,
            'sent'   => $sent,
            'failed' => $failed,
            'queued' => $queued,
        ];
    }
}
