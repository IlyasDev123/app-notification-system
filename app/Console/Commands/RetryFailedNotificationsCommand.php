<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:retry-failed-notifications-command')]
#[Description('Command description')]
class RetryFailedNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:retry-failed 
                            {--tenant= : Optional tenant UUID filter} 
                            {--channel= : Optional channel filter (email, sms, push)} 
                            {--limit=50 : Maximum number of failed logs to retry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-dispatch failed notifications from notification_logs back onto the queue.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $channel  = $this->option('channel');
        $limit    = (int) $this->option('limit');

        $query = NotificationLog::query()
            ->where('status', 'failed');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($channel) {
            $query->where('channel', $channel);
        }

        $failedLogs = $query->oldest()->limit($limit)->get();

        if ($failedLogs->isEmpty()) {
            $this->info('No failed notification logs found matching criteria.');
            return self::SUCCESS;
        }

        $this->info("Found {$failedLogs->count()} failed notification log(s). Re-dispatching jobs...");

        $dispatchedCount = 0;

        foreach ($failedLogs as $log) {
            SendNotificationJob::dispatch(
                $log->tenant_id,
                $log->channel,
                $log->recipient,
                $log->response['content'] ?? 'Retried notification content',
                ['provider' => $log->provider]
            );

            // Update status back to queued to prevent duplicate command retries
            $log->update([
                'status'   => 'queued',
                'response' => array_merge($log->response ?? [], [
                    'retried_at' => now()->toIso8601String(),
                ]),
            ]);

            $dispatchedCount++;
        }

        $this->info("Successfully re-dispatched {$dispatchedCount} notification job(s).");

        return self::SUCCESS;
    }
}
