<?php

use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
    $this->payload = [
        'tenant_id' => $this->tenantId,
        'channel'   => 'email',
        'recipient' => 'user@example.com',
        'content'   => 'Your login OTP is 492019',
        'options'   => ['subject' => 'One-Time Password'],
    ];
});

it('dispatches a notification job to the queue and returns HTTP 202', function () {
    Queue::fake();

    $response = $this->postJson(route('api.v1.notifications.send'), $this->payload);

    $response->assertStatus(202)
        ->assertJson([
            'message' => 'Notification request accepted and queued for processing.',
            'data'    => [
                'tenant_id' => $this->tenantId,
                'channel'   => 'email',
                'recipient' => 'user@example.com',
                'status'    => 'queued',
            ],
        ]);

    Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) {
        return $job->tenantId === $this->tenantId
            && $job->channel === 'email'
            && $job->recipient === 'user@example.com'
            && $job->content === 'Your login OTP is 492019';
    });
});

it('fails validation when required fields are missing', function () {
    Queue::fake();

    $response = $this->postJson(route('api.v1.notifications.send'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tenant_id', 'channel', 'recipient', 'content']);

    Queue::assertNothingPushed();
});

it('fails validation when channel is not supported', function () {
    Queue::fake();

    $invalidPayload = array_merge($this->payload, ['channel' => 'telegram']);

    $response = $this->postJson(route('api.v1.notifications.send'), $invalidPayload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['channel']);

    Queue::assertNothingPushed();
});

it('fails validation when tenant_id is not a valid UUID', function () {
    Queue::fake();

    $invalidPayload = array_merge($this->payload, ['tenant_id' => 'invalid-uuid-format']);

    $response = $this->postJson(route('api.v1.notifications.send'), $invalidPayload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tenant_id']);

    Queue::assertNothingPushed();
});
