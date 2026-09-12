<?php

namespace App\Http\Controllers;

use App\Http\Requests\TanentProvider\StoreTenantProviderRequest;
use App\Services\TenantProviderService;
use Illuminate\Http\JsonResponse;

class TenantProviderController extends Controller
{
    public function __construct(
        private readonly TenantProviderService $providerService
    ) {}

    public function index(string $tenantId): JsonResponse
    {
        $providers = $this->providerService->listProvidersForTenant($tenantId);

        return response()->json([
            'status' => 'success',
            'data'   => $providers,
        ]);
    }

    public function store(StoreTenantProviderRequest $request, string $tenantId): JsonResponse
    {
        $provider = $this->providerService->configureProvider(
            tenantId: $tenantId,
            channel: $request->validated('channel'),
            providerName: $request->validated('provider_name'),
            rawCredentials: $request->validated('credentials'),
            priority: $request->validated('priority', 1),
            isActive: $request->validated('is_active', true)
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Provider credentials securely encrypted and configured.',
            'data'    => [
                'id'            => $provider->id,
                'channel'       => $provider->channel,
                'provider_name' => $provider->provider_name,
                'priority'      => $provider->priority,
                'is_active'     => $provider->is_active,
            ],
        ], 201);
    }

    public function destroy(string $tenantId, string $providerId): JsonResponse
    {
        $this->providerService->removeProvider($tenantId, $providerId);

        return response()->json([
            'status'  => 'success',
            'message' => 'Provider configuration removed successfully.',
        ]);
    }
}