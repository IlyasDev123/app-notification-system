<?php

namespace App\Http\Controllers;

use App\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function __construct(
        private readonly NotificationLogRepositoryInterface $logRepository
    ) {}

    public function index(Request $request, string $tenantId): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $logs    = $this->logRepository->getPaginatedForTenant($tenantId, $perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }

    public function stats(string $tenantId): JsonResponse
    {
        $stats = $this->logRepository->getStatsForTenant($tenantId);

        return response()->json([
            'status' => 'success',
            'data'   => $stats,
        ]);
    }
}
