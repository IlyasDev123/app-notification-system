<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\TenantRepositoryInterface;
use App\Repositories\EloquentTenantRepository;
use App\Contracts\EncryptionServiceInterface;
use App\Services\Security\LaravelEncryptionService;
use Illuminate\Encryption\Encrypter;
use App\Contracts\ApiKeyGeneratorServiceInterface;
use App\Services\Security\ApiKeyGeneratorService; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TenantRepositoryInterface::class, 
            EloquentTenantRepository::class
        );

        // 2. Dynamic Encryption Service Binding
        $this->app->bind(EncryptionServiceInterface::class, function ($app) {
            return new LaravelEncryptionService(
                encrypter: $app->make(Encrypter::class)
            );
        });

        // 2. Key Generation Service Binding
        $this->app->bind(
            ApiKeyGeneratorServiceInterface::class,
            ApiKeyGeneratorService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
