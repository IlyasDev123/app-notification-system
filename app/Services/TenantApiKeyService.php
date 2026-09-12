<?php

namespace App\Services;

use App\Contracts\TenantApiKeyRepositoryInterface;
use App\Contracts\ApiKeyGeneratorServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class TenantApiKeyService
{
    public function __construct(
        private readonly TenantApiKeyRepositoryInterface $apiKeyRepository,
        private readonly ApiKeyGeneratorServiceInterface $generatorService
    ) {}

    public function issueKeyForTenant(string $tenantId, string $name, ?string $expiresAt = null): array
    {
        // 1. Generate key pair and hash via Security Service
        $generated = $this->generatorService->generate(prefix: 'tn_live_');

        // 2. Persist record via Repository
        $apiKey = $this->apiKeyRepository->createForTenant($tenantId, [
            'name'           => $name,
            'key_identifier' => $generated['identifier'],
            'key_hash'       => $generated['hash'],
            'expires_at'     => $expiresAt,
            'is_active'       => true,
        ]);

        return [
            'model'          => $apiKey,
            'plain_text_key' => $generated['full_key'], // Only exposed during initial creation
        ];
    }

    public function listTenantKeys(string $tenantId): Collection
    {
        return $this->apiKeyRepository->getActiveKeysForTenant($tenantId);
    }

    public function revokeTenantKey(string $tenantId, string $keyId): bool
    {
        return $this->apiKeyRepository->revokeKey($keyId, $tenantId);
    }
}