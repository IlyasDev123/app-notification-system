<?php

use App\Contracts\TenantApiKeyRepositoryInterface;
use App\Contracts\ApiKeyGeneratorServiceInterface;
use App\Models\TenantApiKey;
use App\Services\TenantApiKeyService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

// Boot Laravel container to allow Eloquent Model & Collection instantiation
uses(TestCase::class);

beforeEach(function () {
    $this->repositoryMock = Mockery::mock(TenantApiKeyRepositoryInterface::class);
    $this->generatorMock = Mockery::mock(ApiKeyGeneratorServiceInterface::class);

    $this->service = new TenantApiKeyService(
        $this->repositoryMock,
        $this->generatorMock
    );
});

afterEach(function () {
    Mockery::close();
});

it('issues a new API key for a tenant successfully', function () {
    $tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $keyName = 'Production Key';
    $expiresAt = '2027-01-01 00:00:00';

    $generatedPayload = [
        'identifier' => 'tn_live_12345678',
        'full_key'   => 'tn_live_12345678.secret_payload_string',
        'hash'       => hash('sha256', 'tn_live_12345678.secret_payload_string'),
    ];

    $dummyModel = new TenantApiKey([
        'name'           => $keyName,
        'key_identifier' => $generatedPayload['identifier'],
        'expires_at'     => $expiresAt,
    ]);

    $this->generatorMock
        ->shouldReceive('generate')
        ->once()
        ->with('tn_live_')
        ->andReturn($generatedPayload);

    $this->repositoryMock
        ->shouldReceive('createForTenant')
        ->once()
        ->with($tenantId, [
            'name'           => $keyName,
            'key_identifier' => $generatedPayload['identifier'],
            'key_hash'       => $generatedPayload['hash'],
            'expires_at'     => $expiresAt,
            'is_active'       => true,
        ])
        ->andReturn($dummyModel);

    $result = $this->service->issueKeyForTenant($tenantId, $keyName, $expiresAt);

    expect($result)
        ->toHaveKeys(['model', 'plain_text_key'])
        ->and($result['plain_text_key'])->toBe('tn_live_12345678.secret_payload_string')
        ->and($result['model']->name)->toBe($keyName);
});

it('returns active API keys for a tenant', function () {
    $tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';

    // FIX: Using EloquentCollection satisfies the return type hint on the service contract
    $dummyCollection = new EloquentCollection([
        new TenantApiKey(['name' => 'Key 1']),
        new TenantApiKey(['name' => 'Key 2']),
    ]);

    $this->repositoryMock
        ->shouldReceive('getActiveKeysForTenant')
        ->once()
        ->with($tenantId)
        ->andReturn($dummyCollection);

    $keys = $this->service->listTenantKeys($tenantId);

    expect($keys)->toHaveCount(2)
        ->and($keys->first()->name)->toBe('Key 1');
});

it('revokes an API key successfully when key exists', function () {
    $tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $keyId = 'key-uuid-1234';

    $this->repositoryMock
        ->shouldReceive('revokeKey')
        ->once()
        ->with($keyId, $tenantId)
        ->andReturn(true);

    $revoked = $this->service->revokeTenantKey($tenantId, $keyId);

    expect($revoked)->toBeTrue();
});

it('returns false when revoking a non-existent API key', function () {
    $tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $keyId = 'invalid-key-id';

    $this->repositoryMock
        ->shouldReceive('revokeKey')
        ->once()
        ->with($keyId, $tenantId)
        ->andReturn(false);

    $revoked = $this->service->revokeTenantKey($tenantId, $keyId);

    expect($revoked)->toBeFalse();
});
