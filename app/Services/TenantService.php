<?php

namespace App\Services;

use App\Contracts\TenantRepositoryInterface;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository
    ) {}

    public function registerTenant(string $name): Tenant
    {
        $slug = Str::slug($name) . '-' . Str::random(6);

        return $this->tenantRepository->create([
            'name' => $name,
            'slug' => strtolower($slug),
        ]);
    }

    public function getTenantDetails(string $id): Tenant
    {
        return $this->tenantRepository->findById($id);
    }

    public function listTenants(int $perPage = 15): LengthAwarePaginator
    {
        return $this->tenantRepository->paginate($perPage);
    }
}
