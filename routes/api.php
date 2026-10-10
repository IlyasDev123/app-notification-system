<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationLogController;
use App\Http\Controllers\TenantApiKeyController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantProviderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Tenant management ────────────────────────────────────────────────────
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::post('/tenants', [TenantController::class, 'store']);
    Route::get('/tenants/{tenantId}', [TenantController::class, 'show']);

    // ── Per-tenant resources ─────────────────────────────────────────────────
    Route::prefix('/tenants/{tenantId}')->group(function () {

        // API keys
        Route::get('/keys', [TenantApiKeyController::class, 'index']);
        Route::post('/keys', [TenantApiKeyController::class, 'store']);
        Route::delete('/keys/{keyId}', [TenantApiKeyController::class, 'destroy']);

        // Notification providers
        Route::get('/providers', [TenantProviderController::class, 'index']);
        Route::post('/providers', [TenantProviderController::class, 'store']);
        Route::delete('/providers/{providerId}', [TenantProviderController::class, 'destroy']);

        // Notification logs & stats
        Route::get('/logs', [NotificationLogController::class, 'index']);
        Route::get('/stats', [NotificationLogController::class, 'stats']);
    });

    // ── Notification send (requires tenant API key) ──────────────────────────
    Route::middleware('tenant.api')->group(function () {
        Route::post('/notifications/send', [NotificationController::class, 'send'])
            ->name('api.v1.notifications.send');
    });
});
