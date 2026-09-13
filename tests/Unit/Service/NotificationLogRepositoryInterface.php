<?php

use App\Contracts\NotificationLogRepositoryInterface;
use App\Models\NotificationLog;
use App\Services\NotificationEngineService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->logRepositoryMock = Mockery::mock(NotificationLogRepositoryInterface::class);
    $this->service = new NotificationEngineService($this->logRepositoryMock);

    $this->tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $this->channel = 'email';
    $this->recipient = 'user@example.com';
    $this->content = 'Your OTP code is 987654';
    $this->options = ['subject' => 'Security Code'];
});

it('dispatches email successfully and logs a sent audit record', function () {
    $expectedLog = new NotificationLog([
        'id'        => 'uuid-log-123',
        'tenant_id' => $this->tenantId,
        'channel'   => $this->channel,
        'provider'  => 'mailgun',
        'recipient' => $this->recipient,
        'status'    => 'sent',
    ]);

    $this->logRepositoryMock->shouldReceive('log')
        ->once()
        ->with(Mockery::on(function (array $data) {
            return $data['tenant_id'] === $this->tenantId
                && $data['channel'] === 'email'
                && $data['provider'] === 'mailgun'
                && $data['recipient'] === $this->recipient
                && $data['status'] === 'sent'
                && isset($data['response']['message_id']);
        }))
        ->andReturn($expectedLog);

    $result = $this->service->dispatch(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content,
        $this->options
    );

    expect($result)->toBe($expectedLog);
});

it('logs a failed audit record and rethrows exception when delivery throws error', function () {
    $serviceMock = Mockery::mock(NotificationEngineService::class, [$this->logRepositoryMock])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $serviceMock->shouldReceive('sendViaProvider')
        ->once()
        ->andThrow(new Exception('Provider API connection timeout'));

    $this->logRepositoryMock->shouldReceive('log')
        ->once()
        ->with(Mockery::on(function (array $data) {
            return $data['tenant_id'] === $this->tenantId
                && $data['status'] === 'failed'
                && $data['response'] === ['error' => 'Provider API connection timeout'];
        }))
        ->andReturn(new NotificationLog());

    expect(fn() => $serviceMock->dispatch(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content,
        $this->options
    ))->toThrow(Exception::class, 'Provider API connection timeout');
});
