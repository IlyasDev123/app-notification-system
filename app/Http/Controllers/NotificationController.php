<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notification\SendNotificationRequest;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function send(SendNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $request->user()->id;

        SendNotificationJob::dispatch(
            $tenantId,
            $validated['channel'],
            $validated['recipient'],
            $validated['content'],
            $validated['options'] ?? []
        );

        return response()->json([
            'message' => 'Notification request accepted and queued for processing.',
            'data'    => [
                'tenant_id' => $tenantId,
                'channel'   => $validated['channel'],
                'recipient' => $validated['recipient'],
                'status'    => 'queued',
            ],
        ], 202);
    }
}
