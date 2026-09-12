<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Tenant\CreateTenantRequest;
use App\Services\TenantService;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    public function index(): JsonResponse
    {
        $tenants = $this->tenantService->listTenants();

        return response()->json([
            'status' => 'success',
            'data'   => $tenants,
        ]);
    }

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->registerTenant(
            name: $request->validated('name')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Tenant successfully created.',
            'data'    => $tenant,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantService->getTenantDetails($id);

        return response()->json([
            'status' => 'success',
            'data'   => $tenant,
        ]);
    }
}
