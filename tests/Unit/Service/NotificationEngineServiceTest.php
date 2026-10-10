<?php

use App\Contracts\NotificationLogRepositoryInterface;
use App\Contracts\NotificationProviderInterface;
use App\Contracts\TenantProviderRepositoryInterface;
use App\Models\NotificationLog;
use App\Models\TenantProvider;
use App\Services\NotificationEngineService;
use App\Services\NotificationProviderFactory;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->logRepositoryMock      = Mockery::mock(NotificationLogRepositoryInterface::class);
    $this->providerRepositoryMock = Mockery::mock(TenantProviderRepositoryInterface::class);
    $this->providerFactoryMock    = Mockery::mock(NotificationProviderFactory::class);
    $this->providerDriverMock     = Mockery::mock(NotificationProviderInterface::class);

    $this->service = new NotificationEngineService(
        $this->logRepositoryMock,
        $this->providerRepositoryMock,
        $this->providerFactoryMock
    );

    $this->tenantId  = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $this->channel   = 'email';
    $this->recipient = 'user@example.com';
    $this->content   = 'Your verification code is 123456';
});

afterEach(function () {
    Mockery::close();
});

it('dispatches a notification successfully through the resolved provider driver', function () {
    $providerName        = 'mailgun';
    $decryptedCredentials = ['api_key' => 'key-12345', 'domain' => 'mg.example.com'];
    $options             = ['subject' => 'Welcome'];

    // Build a TenantProvider whose credentials will be returned as an array
    $tenantProviderModel = new TenantProvider();
    $tenantProviderModel->forceFill([
        'id'            => 'provider-uuid-1',
        'tenant_id'     => $this->tenantId,
        'channel'       => $this->channel,
        'provider_name' => $providerName,
        'is_active'     => true,
    ]);
    // Set credentials through the cast so decrypt works when engine reads it
    $tenantProviderModel->credentials = $decryptedCredentials;

    $providerResponse = ['success' => true, 'message_id' => 'mailgun-msg-1234', 'status' => 200];

    $expectedLog = new NotificationLog();
    $expectedLog->forceFill([
        'tenant_id' => $this->tenantId,
        'channel'   => $this->channel,
        'provider'  => $providerName,
        'recipient' => $this->recipient,
        'status'    => 'sent',
        'response'  => $providerResponse,
    ]);

    $this->providerRepositoryMock
        ->shouldReceive('findByTenantAndChannel')
        ->once()
        ->with($this->tenantId, $this->channel)
        ->andReturn($tenantProviderModel);

    $this->providerFactoryMock
        ->shouldReceive('make')
        ->once()
        ->with($providerName)
        ->andReturn($this->providerDriverMock);

    $this->providerDriverMock
        ->shouldReceive('send')
        ->once()
        ->with($this->recipient, $this->content, $decryptedCredentials, $options)
        ->andReturn($providerResponse);

    $this->logRepositoryMock
        ->shouldReceive('log')
        ->once()
        ->andReturn($expectedLog);

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

    expect(fn () => $this->service->dispatch(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content
    ))->toThrow(
        RuntimeException::class,
        "No active provider configured for tenant [{$this->tenantId}] on channel [{$this->channel}]."
    );
});

it('throws exception when configured provider is inactive', function () {
    $inactiveTenantProvider = new TenantProvider();
    $inactiveTenantProvider->forceFill([
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

    expect(fn () => $this->service->dispatch(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content
    ))->toThrow(
        RuntimeException::class,
        "No active provider configured for tenant [{$this->tenantId}] on channel [{$this->channel}]."
    );
});
