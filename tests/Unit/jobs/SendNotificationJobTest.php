<?php

use App\Jobs\SendNotificationJob; // <--- Must be present
use App\Services\NotificationEngineService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $this->channel = 'email';
    $this->recipient = 'user@example.com';
    $this->content = 'Your OTP code is 987654';
    $this->notificationOptions = ['subject' => 'Security Code'];
});

it('can be dispatched onto the default queue', function () {
    Queue::fake();

    SendNotificationJob::dispatch(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content,
        $this->notificationOptions
    );

    Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) {
        return $job->tenantId === $this->tenantId
            && $job->channel === $this->channel
            && $job->recipient === $this->recipient
            && $job->content === $this->content
            && $job->options === $this->notificationOptions;
    });
});

it('calls NotificationEngineService dispatch method when executed', function () {
    $engineMock = Mockery::mock(NotificationEngineService::class);

    $engineMock->shouldReceive('dispatch')
        ->once()
        ->with(
            $this->tenantId,
            $this->channel,
            $this->recipient,
            $this->content,
            $this->notificationOptions
        );

    $job = new SendNotificationJob(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content,
        $this->notificationOptions
    );

    $job->handle($engineMock);
});

it('defines expected retry attempts and exponential backoff configuration', function () {
    $job = new SendNotificationJob(
        $this->tenantId,
        $this->channel,
        $this->recipient,
        $this->content
    );

    expect($job->tries)->toBe(3)
        ->and($job->backoff())->toBe([10, 30, 90]);
});
