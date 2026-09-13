<?php

namespace App\Jobs;

use App\Services\NotificationEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $timeout = 60;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $channel,
        public readonly string $recipient,
        public readonly string $content,
        public readonly array $options = []
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 90];
    }

    public function handle(NotificationEngineService $engine): void
    {
        $engine->dispatch(
            $this->tenantId,
            $this->channel,
            $this->recipient,
            $this->content,
            $this->options
        );
    }

    public function failed(?Throwable $exception): void
    {
        logger()->error("SendNotificationJob failed for tenant [{$this->tenantId}]: " . $exception?->getMessage(), [
            'tenant_id' => $this->tenantId,
            'channel'   => $this->channel,
            'recipient' => $this->recipient,
            'exception' => $exception,
        ]);
    }
}
