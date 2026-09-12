<?php

namespace App\Contracts;

use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

interface TenantRepositoryInterface
{

    public function create(array $data): Tenant;
    
    public function findById(string $id): Tenant;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function update(Tenant $tenant, array $data): Tenant;

    public function delete(Tenant $tenant): bool;
}

