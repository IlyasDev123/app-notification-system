<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tenant\StoreTenantApiKeyRequest;
use App\Services\TenantApiKeyService;
use Illuminate\Http\JsonResponse;

class TenantApiKeyController extends Controller
{
    public function __construct(
        private readonly TenantApiKeyService $apiKeyService
    ) {}

    public function index(string $tenantId): JsonResponse
    {
        $keys = $this->apiKeyService->listTenantKeys($tenantId);

        return response()->json([
            'status' => 'success',
            'data'   => $keys,
        ]);
    }

    public function store(StoreTenantApiKeyRequest $request, string $tenantId): JsonResponse
    {
        $result = $this->apiKeyService->issueKeyForTenant(
            tenantId: $tenantId,
            name: $request->validated('name'),
            expiresAt: $request->validated('expires_at')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'API Key generated successfully. Save this key now; it will not be displayed again.',
            'data'    => [
                'id'             => $result['model']->id,
                'name'           => $result['model']->name,
                'plain_text_key' => $result['plain_text_key'],
                'expires_at'     => $result['model']->expires_at,
            ],
        ], 201);
    }

    public function destroy(string $tenantId, string $keyId): JsonResponse
    {
        $revoked = $this->apiKeyService->revokeTenantKey($tenantId, $keyId);

        if (!$revoked) {
            return response()->json([
                'status'  => 'error',
                'message' => 'API Key not found or already revoked.',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'API Key revoked successfully.',
        ]);
    }
}