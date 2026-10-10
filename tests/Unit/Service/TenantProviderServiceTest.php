<?php

use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\TenantProvider;
use App\Services\TenantProviderService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->providerRepositoryMock = Mockery::mock(TenantProviderRepositoryInterface::class);

    $this->service = new TenantProviderService($this->providerRepositoryMock);

    $this->tenantId   = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $this->providerId = 'provider-uuid-1234';
    $this->channel    = 'email';
});

afterEach(function () {
    Mockery::close();
});

it('configures a tenant provider and passes raw credentials to the repository', function () {
    $providerName    = 'mailgun';
    $rawCredentials  = ['api_key' => 'key-12345', 'domain' => 'mg.example.com'];
    $priority        = 1;
    $isActive        = true;

    $expectedModel = (new TenantProvider())->forceFill([
        'id'            => $this->providerId,
        'tenant_id'     => $this->tenantId,
        'channel'       => $this->channel,
        'provider_name' => $providerName,
        'priority'      => $priority,
        'is_active'     => $isActive,
    ]);

    // Credentials are passed as raw array — the model's encrypted:array cast
    // handles encryption, no manual encryption in the service layer.
    $this->providerRepositoryMock
        ->shouldReceive('updateOrCreate')
        ->once()
        ->with($this->tenantId, $this->channel, [
            'provider_name' => $providerName,
            'credentials'   => $rawCredentials,
            'priority'      => $priority,
            'is_active'     => $isActive,
        ])
        ->andReturn($expectedModel);

    $result = $this->service->configureProvider(
        $this->tenantId,
        $this->channel,
        $providerName,
        $rawCredentials,
        $priority,
        $isActive
    );

    expect($result)
        ->toBeInstanceOf(TenantProvider::class)
        ->and($result->id)->toBe($this->providerId)
        ->and($result->provider_name)->toBe($providerName);
});

it('lists all configured providers for a tenant', function () {
    $dummyCollection = new EloquentCollection([
        (new TenantProvider())->forceFill(['id' => '1', 'tenant_id' => $this->tenantId, 'provider_name' => 'mailgun']),
        (new TenantProvider())->forceFill(['id' => '2', 'tenant_id' => $this->tenantId, 'provider_name' => 'twilio']),
    ]);

    $this->providerRepositoryMock
        ->shouldReceive('getByTenantId')
        ->once()
        ->with($this->tenantId)
        ->andReturn($dummyCollection);

    $providers = $this->service->listProvidersForTenant($this->tenantId);

    expect($providers)
        ->toBeInstanceOf(EloquentCollection::class)
        ->toHaveCount(2)
        ->and($providers->first()->provider_name)->toBe('mailgun');
});

it('removes a provider configuration for a tenant', function () {
    $this->providerRepositoryMock
        ->shouldReceive('delete')
        ->once()
        ->with($this->tenantId, $this->providerId)
        ->andReturn(true);

    $result = $this->service->removeProvider($this->tenantId, $this->providerId);

    expect($result)->toBeTrue();
});
