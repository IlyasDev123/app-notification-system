<?php

use App\Contracts\NotificationLogRepositoryInterface;
use App\Models\NotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(NotificationLogRepositoryInterface::class);
    $this->tenantId = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
});

it('logs notification attempts to the database', function () {
    $logData = [
        'tenant_id' => $this->tenantId,
        'channel'   => 'email',
        'provider'  => 'mailgun',
        'recipient' => 'user@example.com',
        'status'    => 'sent',
        'response'  => ['id' => 'mailgun-123'],
    ];

    $log = $this->repository->log($logData);

    expect($log)->toBeInstanceOf(NotificationLog::class)
        ->and($log->id)->not->toBeNull()
        ->and($log->tenant_id)->toBe($this->tenantId)
        ->and($log->response)->toBe(['id' => 'mailgun-123']);

    $this->assertDatabaseHas('notification_logs', [
        'id'        => $log->id,
        'tenant_id' => $this->tenantId,
        'status'    => 'sent',
    ]);
});

it('calculates aggregate statistics for a tenant', function () {
    // Create logs for target tenant
    NotificationLog::factory()->create(['tenant_id' => $this->tenantId, 'status' => 'sent']);
    NotificationLog::factory()->create(['tenant_id' => $this->tenantId, 'status' => 'sent']);
    NotificationLog::factory()->create(['tenant_id' => $this->tenantId, 'status' => 'failed']);
    NotificationLog::factory()->create(['tenant_id' => $this->tenantId, 'status' => 'queued']);

    // Create log for a different tenant to ensure scope isolation
    NotificationLog::factory()->create(['tenant_id' => 'other-tenant-uuid', 'status' => 'sent']);

    $stats = $this->repository->getStatsForTenant($this->tenantId);

    expect($stats)->toBe([
        'total'  => 4,
        'sent'   => 2,
        'failed' => 1,
        'queued' => 1,
    ]);
});

it('returns paginated logs for a tenant ordered by latest', function () {
    NotificationLog::factory()->count(20)->create(['tenant_id' => $this->tenantId]);

    $paginated = $this->repository->getPaginatedForTenant($this->tenantId, 15);

    expect($paginated->total())->toBe(20)
        ->and($paginated->perPage())->toBe(15)
        ->and($paginated->items())->toHaveCount(15);
});
