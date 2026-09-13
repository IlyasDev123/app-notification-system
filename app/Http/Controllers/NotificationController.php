<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Dispatch a notification to the processing queue.
     */
    public function send(SendNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        SendNotificationJob::dispatch(
            $validated['tenant_id'],
            $validated['channel'],
            $validated['recipient'],
            $validated['content'],
            $validated['options'] ?? []
        );

        return response()->json([
            'message' => 'Notification request accepted and queued for processing.',
            'data'    => [
                'tenant_id' => $validated['tenant_id'],
                'channel'   => $validated['channel'],
                'recipient' => $validated['recipient'],
                'status'    => 'queued',
            ],
        ], 202);
    }
}
