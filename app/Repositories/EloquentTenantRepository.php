<?php

namespace App\Repositories;

use App\Contracts\TenantRepositoryInterface;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentTenantRepository implements TenantRepositoryInterface
{

    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function findById(string $id): Tenant
    {
        return Tenant::findOrFail($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::paginate($perPage);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        return $tenant;
    }

    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }
}
