<?php

use App\Contracts\EncryptionServiceInterface;
use App\Contracts\NotificationProviderInterface;
use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\NotificationLog;
use App\Models\TenantProvider;
use App\Services\NotificationEngineService;
use App\Services\NotificationProviderFactory;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->providerRepositoryMock = Mockery::mock(TenantProviderRepositoryInterface::class);
    $this->encryptionServiceMock = Mockery::mock(EncryptionServiceInterface::class);
    $this->providerFactoryMock = Mockery::mock(NotificationProviderFactory::class);
    $this->providerDriverMock = Mockery::mock(NotificationProviderInterface::class);

    $this->service = new NotificationEngineService(
        $this->providerRepositoryMock,
        $this->encryptionServiceMock,
        $this->providerFactoryMock
    );

    $this->tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $this->channel = 'email';
    $this->recipient = 'user@example.com';
    $this->content = 'Your verification code is 123456';
});

afterEach(function () {
    Mockery::close();
});

it('dispatches a notification successfully through the resolved provider driver', function () {
    $providerName = 'mailgun';
    $encryptedCredentials = 'eyJpdiI6IlhYWCIsInZhbHVlIjoiWVlZIiwibWFjIjoiWlpaIn0=';
    $decryptedCredentials = ['api_key' => 'key-12345', 'domain' => 'mg.example.com'];
    $options = ['subject' => 'Welcome to LifeLedger'];

    $tenantProviderModel = (new TenantProvider())->forceFill([
        'id'            => 'provider-uuid-1',
        'tenant_id'     => $this->tenantId,
        'channel'       => $this->channel,
        'provider_name' => $providerName,
        'credentials'   => $encryptedCredentials,
        'is_active'     => true,
    ]);

    $providerResponse = [
        'success'    => true,
        'message_id' => 'mailgun-msg-1234',
        'status'     => 'sent',
    ];

    // 1. Assert repository lookup for channel & tenant
    $this->providerRepositoryMock
        ->shouldReceive('findByTenantAndChannel')
        ->once()
        ->with($this->tenantId, $this->channel)
        ->andReturn($tenantProviderModel);

    // 2. Assert decryption of provider credentials
    $this->encryptionServiceMock
        ->shouldReceive('decryptArray')
        ->once()
        ->with($encryptedCredentials)
        ->andReturn($decryptedCredentials);

    // 3. Assert factory makes the correct driver
    $this->providerFactoryMock
        ->shouldReceive('make')
        ->once()
        ->with($providerName)
        ->andReturn($this->providerDriverMock);

    // 4. Assert driver sends payload
    $this->providerDriverMock
        ->shouldReceive('send')
        ->once()
        ->with($this->recipient, $this->content, $decryptedCredentials, $options)
        ->andReturn($providerResponse);

    $log = $this->service->dispatch(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content,
        $options
    );

    expect($log)
        ->toBeInstanceOf(NotificationLog::class)
        ->and($log->tenant_id)->toBe($this->tenantId)
        ->and($log->channel)->toBe($this->channel)
        ->and($log->provider)->toBe($providerName)
        ->and($log->recipient)->toBe($this->recipient)
        ->and($log->status)->toBe('sent');
});

it('throws exception when no provider exists for tenant and channel', function () {
    $this->providerRepositoryMock
        ->shouldReceive('findByTenantAndChannel')
        ->once()
        ->with($this->tenantId, $this->channel)
        ->andReturnNull();

    expect(function () {
        $this->service->dispatch(
            $this->tenantId,
            $this->channel,
            $this->recipient,
            $this->content
        );
    })->toThrow(Exception::class, "No active provider configured for tenant [{$this->tenantId}] on channel [{$this->channel}].");
});

it('throws exception when configured provider is inactive', function () {
    $inactiveTenantProvider = (new TenantProvider())->forceFill([
        'id'            => 'provider-uuid-2',
        'tenant_id'     => $this->tenantId,
        'channel'       => $this->channel,
        'provider_name' => 'twilio',
        'is_active'     => false,
    ]);

    $this->providerRepositoryMock
        ->shouldReceive('findByTenantAndChannel')
        ->once()
        ->with($this->tenantId, $this->channel)
        ->andReturn($inactiveTenantProvider);

    expect(function () {
        $this->service->dispatch(
            $this->tenantId,
            $this->channel,
            $this->recipient,
            $this->content
        );
    })->toThrow(Exception::class, "No active provider configured for tenant [{$this->tenantId}] on channel [{$this->channel}].");
});
