<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TenantWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('tenants')->name('tenants.')->group(function () {
    Route::get('/', [TenantWebController::class, 'index'])->name('index');
    Route::get('/create', [TenantWebController::class, 'create'])->name('create');
    Route::post('/', [TenantWebController::class, 'store'])->name('store');
    Route::get('/{tenantId}', [TenantWebController::class, 'show'])->name('show');

    Route::post('/{tenantId}/keys', [TenantWebController::class, 'storeKey'])->name('keys.store');
    Route::delete('/{tenantId}/keys/{keyId}', [TenantWebController::class, 'destroyKey'])->name('keys.destroy');

    Route::post('/{tenantId}/providers', [TenantWebController::class, 'storeProvider'])->name('providers.store');
    Route::delete('/{tenantId}/providers/{providerId}', [TenantWebController::class, 'destroyProvider'])->name('providers.destroy');
});
