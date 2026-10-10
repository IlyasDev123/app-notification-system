<?php

namespace App\Http\Controllers\Web;

use App\Contracts\NotificationLogRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\TenantApiKeyService;
use App\Services\TenantProviderService;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantWebController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
        private readonly TenantApiKeyService $apiKeyService,
        private readonly TenantProviderService $providerService,
        private readonly NotificationLogRepositoryInterface $logRepository,
    ) {}

    public function index(): View
    {
        $tenants = $this->tenantService->listTenants(15);

        return view('tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $tenant = $this->tenantService->registerTenant($validated['name']);

        return redirect()
            ->route('tenants.show', $tenant->id)
            ->with('success', "Tenant \"{$tenant->name}\" created successfully.");
    }

    public function show(string $tenantId): View
    {
        $tenant    = $this->tenantService->getTenantDetails($tenantId);
        $apiKeys   = $this->apiKeyService->listTenantKeys($tenantId);
        $providers = $this->providerService->listProvidersForTenant($tenantId);
        $stats     = $this->logRepository->getStatsForTenant($tenantId);
        $logs      = $this->logRepository->getPaginatedForTenant($tenantId, 10);

        return view('tenants.show', compact('tenant', 'apiKeys', 'providers', 'stats', 'logs'));
    }

    // ── API Keys ─────────────────────────────────────────────────────────────

    public function storeKey(Request $request, string $tenantId): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'min:3', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $result = $this->apiKeyService->issueKeyForTenant(
            tenantId: $tenantId,
            name: $validated['name'],
            expiresAt: $validated['expires_at'] ?? null
        );

        return redirect()
            ->route('tenants.show', ['tenantId' => $tenantId, 'tab' => 'keys'])
            ->with('new_api_key', $result['plain_text_key'])
            ->with('new_api_key_name', $result['model']->name)
            ->with('success', 'API key generated. Copy it now — it will not be shown again.');
    }

    public function destroyKey(string $tenantId, string $keyId): RedirectResponse
    {
        $this->apiKeyService->revokeTenantKey($tenantId, $keyId);

        return redirect()
            ->route('tenants.show', ['tenantId' => $tenantId, 'tab' => 'keys'])
            ->with('success', 'API key revoked.');
    }

    // ── Providers ────────────────────────────────────────────────────────────

    public function storeProvider(Request $request, string $tenantId): RedirectResponse
    {
        $validated = $request->validate([
            'channel'       => ['required', 'string', 'in:email,sms,push'],
            'provider_name' => ['required', 'string', 'in:mailgun,smtp,twilio'],
            'credentials'   => ['required', 'array'],
            'priority'      => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $this->providerService->configureProvider(
            tenantId: $tenantId,
            channel: $validated['channel'],
            providerName: $validated['provider_name'],
            rawCredentials: $validated['credentials'],
            priority: (int) ($validated['priority'] ?? 1),
        );

        return redirect()
            ->route('tenants.show', ['tenantId' => $tenantId, 'tab' => 'providers'])
            ->with('success', 'Provider configured successfully.');
    }

    public function destroyProvider(string $tenantId, string $providerId): RedirectResponse
    {
        $this->providerService->removeProvider($tenantId, $providerId);

        return redirect()
            ->route('tenants.show', ['tenantId' => $tenantId, 'tab' => 'providers'])
            ->with('success', 'Provider removed.');
    }
}
