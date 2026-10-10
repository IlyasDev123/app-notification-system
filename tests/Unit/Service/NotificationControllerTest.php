<?php

use App\Jobs\SendNotificationJob;
use App\Models\Tenant;
use App\Models\TenantApiKey;
use App\Services\Security\ApiKeyGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create a real tenant + API key so requests can authenticate
    $this->tenant = Tenant::create([
        'name'      => 'Test Tenant',
        'slug'      => 'test-tenant-abc123',
        'is_active' => true,
    ]);

    $generator = new ApiKeyGeneratorService();
    $generated = $generator->generate();

    $this->plainKey = $generated['full_key'];

    TenantApiKey::create([
        'tenant_id'      => $this->tenant->id,
        'name'           => 'Test Key',
        'key_identifier' => $generated['identifier'],
        'key_hash'       => $generated['hash'],
        'is_active'      => true,
    ]);

    $this->payload = [
        'channel'   => 'email',
        'recipient' => 'user@example.com',
        'content'   => 'Your login OTP is 492019',
        'options'   => ['subject' => 'One-Time Password'],
    ];
});

it('dispatches a notification job to the queue and returns HTTP 202', function () {
    Queue::fake();

    $response = $this->withHeader('Authorization', "Bearer {$this->plainKey}")
        ->postJson(route('api.v1.notifications.send'), $this->payload);

    $response->assertStatus(202)
        ->assertJson([
            'message' => 'Notification request accepted and queued for processing.',
            'data'    => [
                'tenant_id' => $this->tenant->id,
                'channel'   => 'email',
                'recipient' => 'user@example.com',
                'status'    => 'queued',
            ],
        ]);

    Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) {
        return $job->tenantId === $this->tenant->id
            && $job->channel === 'email'
            && $job->recipient === 'user@example.com'
            && $job->content === 'Your login OTP is 492019';
    });
});

it('returns 401 when no API key is provided', function () {
    $response = $this->postJson(route('api.v1.notifications.send'), $this->payload);

    $response->assertStatus(401);
});

it('fails validation when required fields are missing', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->plainKey}")
        ->postJson(route('api.v1.notifications.send'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['channel', 'recipient', 'content']);
});

it('fails validation when channel is not supported', function () {
    $invalidPayload = array_merge($this->payload, ['channel' => 'telegram']);

    $response = $this->withHeader('Authorization', "Bearer {$this->plainKey}")
        ->postJson(route('api.v1.notifications.send'), $invalidPayload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['channel']);
});
