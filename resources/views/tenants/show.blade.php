@extends('layouts.app')

@section('title', $tenant->name)

@section('breadcrumb')
    <a href="{{ route('tenants.index') }}" class="text-slate-500 hover:text-slate-700">Tenants</a>
    <svg class="w-4 h-4 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="font-medium text-slate-900">{{ $tenant->name }}</span>
@endsection

@section('content')

{{-- New API Key Modal (shown once after generation) --}}
@if(session('new_api_key'))
<div x-data="{ show: true, copied: false }" x-show="show" x-cloak
     class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl">
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-900">API Key Generated</h3>
                <p class="text-sm text-slate-500 mt-0.5">
                    <strong>{{ session('new_api_key_name') }}</strong> — Copy and save this key now. It will not be shown again.
                </p>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex items-center gap-3 mb-5">
            <code class="flex-1 text-xs font-mono text-slate-700 break-all select-all">{{ session('new_api_key') }}</code>
            <button @click="navigator.clipboard.writeText('{{ session('new_api_key') }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    class="flex-shrink-0 flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border transition-colors"
                    :class="copied ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-100'">
                <span x-show="!copied">Copy</span>
                <span x-show="copied" x-cloak>Copied ✓</span>
            </button>
        </div>

        <button @click="show = false"
                class="w-full bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
            I've saved the key — Close
        </button>
    </div>
</div>
@endif

{{-- Tenant Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $tenant->name }}</h1>
            @if($tenant->is_active)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactive</span>
            @endif
        </div>
        <p class="mt-1 text-sm text-slate-500 font-mono">{{ $tenant->slug }}</p>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'overview' }">

    {{-- Tab Nav --}}
    <div class="flex items-center gap-1 border-b border-slate-200 mb-6">
        @foreach([['overview','Overview'],['keys','API Keys'],['providers','Providers'],['logs','Logs']] as [$key, $label])
        <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap">
            {{ $label }}
            @if($key === 'keys') <span class="ml-1.5 text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full">{{ $apiKeys->count() }}</span>@endif
            @if($key === 'providers') <span class="ml-1.5 text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full">{{ $providers->count() }}</span>@endif
        </button>
        @endforeach
    </div>

    {{-- ── OVERVIEW TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'overview'">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach([
                ['Total', $stats['total'], 'slate'],
                ['Sent', $stats['sent'], 'emerald'],
                ['Failed', $stats['failed'], 'red'],
                ['Queued', $stats['queued'], 'amber'],
            ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold text-{{ $color }}-{{ $color === 'slate' ? '900' : '600' }}">{{ number_format($value) }}</p>
            </div>
            @endforeach
        </div>

        {{-- Tenant Info --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Tenant Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-slate-500">ID</dt>
                    <dd class="mt-0.5 font-mono text-xs text-slate-700 break-all">{{ $tenant->id }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Slug</dt>
                    <dd class="mt-0.5 font-mono text-xs text-slate-700">{{ $tenant->slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Created</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $tenant->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">API Send Endpoint</dt>
                    <dd class="mt-0.5 font-mono text-xs text-slate-700">POST /api/v1/notifications/send</dd>
                </div>
            </dl>
        </div>

        {{-- Recent Logs --}}
        @if($logs->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Recent Logs</h3>
                <button @click="tab = 'logs'" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">View all →</button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Channel</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Provider</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Recipient</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($logs->take(5) as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 capitalize">{{ $log->channel }}</span>
                        </td>
                        <td class="px-6 py-3 text-slate-600 capitalize">{{ $log->provider }}</td>
                        <td class="px-6 py-3 text-slate-600 truncate max-w-[180px]">{{ $log->recipient }}</td>
                        <td class="px-6 py-3">@include('tenants._status_badge', ['status' => $log->status])</td>
                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl border border-dashed border-slate-200 p-10 text-center">
            <p class="text-sm text-slate-500">No notification logs yet. Send your first notification via the API.</p>
        </div>
        @endif
    </div>

    {{-- ── API KEYS TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'keys'" x-cloak
         x-data="{ showAddKey: false }">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-900">API Keys</h2>
            <button @click="showAddKey = true"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Generate New Key
            </button>
        </div>

        {{-- Add Key Modal --}}
        <div x-show="showAddKey" x-cloak
             class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.outside="showAddKey = false">
                <h3 class="text-base font-semibold text-slate-900 mb-1">Generate API Key</h3>
                <p class="text-sm text-slate-500 mb-5">The plain-text key is shown only once after generation.</p>

                <form method="POST" action="{{ route('tenants.keys.store', $tenant->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Key Name</label>
                        <input type="text" name="name" placeholder="e.g. Production Key"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Expires At <span class="font-normal text-slate-400">(optional)</span></label>
                        <input type="datetime-local" name="expires_at"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
                            Generate
                        </button>
                        <button type="button" @click="showAddKey = false"
                                class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2.5 rounded-xl transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($apiKeys->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Name</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Identifier</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Last Used</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Expires</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($apiKeys as $key)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3.5 font-medium text-slate-900">{{ $key->name }}</td>
                        <td class="px-6 py-3.5 font-mono text-xs text-slate-600">{{ $key->key_identifier }}…</td>
                        <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                        <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $key->expires_at ? $key->expires_at->format('M j, Y') : 'Never' }}</td>
                        <td class="px-6 py-3.5">
                            @if($key->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Revoked</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            @if($key->is_active)
                            <form method="POST" action="{{ route('tenants.keys.destroy', [$tenant->id, $key->id]) }}"
                                  onsubmit="return confirm('Revoke this API key? It cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Revoke</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl border border-dashed border-slate-200 p-12 text-center">
            <p class="text-sm text-slate-500">No API keys yet. Generate one to let this tenant authenticate.</p>
        </div>
        @endif
    </div>

    {{-- ── PROVIDERS TAB ────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'providers'" x-cloak
         x-data="{ showAddProvider: false, selectedProvider: '' }">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-900">Notification Providers</h2>
            <button @click="showAddProvider = true"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Provider
            </button>
        </div>

        {{-- Add Provider Modal --}}
        <div x-show="showAddProvider" x-cloak
             class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl my-4" @click.outside="showAddProvider = false">
                <h3 class="text-base font-semibold text-slate-900 mb-1">Add Notification Provider</h3>
                <p class="text-sm text-slate-500 mb-5">Credentials are encrypted at rest using AES-256.</p>

                <form method="POST" action="{{ route('tenants.providers.store', $tenant->id) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Channel</label>
                        <select name="channel"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="push">Push</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Provider</label>
                        <select name="provider_name" x-model="selectedProvider"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                            <option value="">Select a provider…</option>
                            <option value="mailgun">Mailgun (Email)</option>
                            <option value="smtp">SMTP (Email)</option>
                            <option value="twilio">Twilio (SMS)</option>
                        </select>
                    </div>

                    {{-- Mailgun credentials --}}
                    <template x-if="selectedProvider === 'mailgun'">
                        <div class="space-y-3 mb-4">
                            <div class="p-3 bg-slate-50 rounded-lg text-xs text-slate-500">Mailgun credentials</div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">API Key</label>
                                <input type="password" name="credentials[api_key]" placeholder="key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Domain</label>
                                <input type="text" name="credentials[domain]" placeholder="mg.yourdomain.com"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                    </template>

                    {{-- SMTP credentials --}}
                    <template x-if="selectedProvider === 'smtp'">
                        <div class="space-y-3 mb-4">
                            <div class="p-3 bg-slate-50 rounded-lg text-xs text-slate-500">SMTP credentials</div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Host</label>
                                    <input type="text" name="credentials[host]" placeholder="smtp.gmail.com"
                                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Port</label>
                                    <input type="number" name="credentials[port]" placeholder="587"
                                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                                <input type="text" name="credentials[username]" placeholder="you@gmail.com"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                                <input type="password" name="credentials[password]" placeholder="App password"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">From Address</label>
                                <input type="email" name="credentials[from_address]" placeholder="notifications@yourdomain.com"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                    </template>

                    {{-- Twilio credentials --}}
                    <template x-if="selectedProvider === 'twilio'">
                        <div class="space-y-3 mb-4">
                            <div class="p-3 bg-slate-50 rounded-lg text-xs text-slate-500">Twilio credentials</div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Account SID</label>
                                <input type="text" name="credentials[account_sid]" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Auth Token</label>
                                <input type="password" name="credentials[auth_token]" placeholder="Auth token"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">From Number</label>
                                <input type="text" name="credentials[from_number]" placeholder="+15551234567"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                    </template>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Priority <span class="font-normal text-slate-400">(1 = highest)</span></label>
                        <input type="number" name="priority" value="1" min="1" max="10"
                               class="w-24 px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
                            Save Provider
                        </button>
                        <button type="button" @click="showAddProvider = false"
                                class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2.5 rounded-xl transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($providers->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Channel</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Provider</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Priority</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($providers as $provider)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @if($provider->channel === 'email') bg-blue-100 text-blue-700
                                @elseif($provider->channel === 'sms') bg-purple-100 text-purple-700
                                @else bg-orange-100 text-orange-700 @endif capitalize">
                                {{ $provider->channel }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 font-medium text-slate-900 capitalize">{{ $provider->provider_name }}</td>
                        <td class="px-6 py-3.5 text-slate-600">{{ $provider->priority }}</td>
                        <td class="px-6 py-3.5">
                            @if($provider->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <form method="POST" action="{{ route('tenants.providers.destroy', [$tenant->id, $provider->id]) }}"
                                  onsubmit="return confirm('Remove this provider configuration?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl border border-dashed border-slate-200 p-12 text-center">
            <p class="text-sm text-slate-500">No providers configured. Add a provider to enable notification delivery.</p>
        </div>
        @endif
    </div>

    {{-- ── LOGS TAB ──────────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'logs'" x-cloak>

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-900">Notification Logs</h2>
            <span class="text-xs text-slate-500">{{ number_format($stats['total']) }} total</span>
        </div>

        @if($logs->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Channel</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Provider</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Recipient</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @if($log->channel === 'email') bg-blue-100 text-blue-700
                                @elseif($log->channel === 'sms') bg-purple-100 text-purple-700
                                @else bg-orange-100 text-orange-700 @endif capitalize">
                                {{ $log->channel }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-600 capitalize">{{ $log->provider }}</td>
                        <td class="px-6 py-3.5 text-slate-600 truncate max-w-[200px]">{{ $log->recipient }}</td>
                        <td class="px-6 py-3.5">@include('tenants._status_badge', ['status' => $log->status])</td>
                        <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="bg-white rounded-xl border border-dashed border-slate-200 p-12 text-center">
            <p class="text-sm text-slate-500">No notification logs for this tenant yet.</p>
        </div>
        @endif
    </div>

</div>

@endsection
