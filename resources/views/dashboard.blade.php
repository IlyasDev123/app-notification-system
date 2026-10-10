@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-semibold text-slate-900">Welcome to NotifyHub</h1>
    <p class="mt-1 text-sm text-slate-500">Multi-tenant notification dispatch platform. Register tenants, configure providers, and start sending.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tenants</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $totalTenants }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Active Tenants</p>
        <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $activeTenants }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:col-span-2 lg:col-span-1">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">API Version</p>
        <p class="mt-2 text-3xl font-semibold text-indigo-600">v1</p>
    </div>
</div>

{{-- How it works --}}
<div class="bg-white rounded-xl border border-slate-200 p-6 mb-8">
    <h2 class="text-sm font-semibold text-slate-900 mb-4">Quick Start</h2>
    <ol class="space-y-3">
        <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center">1</span>
            <div>
                <p class="text-sm font-medium text-slate-800">Create a Tenant</p>
                <p class="text-xs text-slate-500">Register a new tenant (your customer / project) in the system.</p>
            </div>
        </li>
        <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center">2</span>
            <div>
                <p class="text-sm font-medium text-slate-800">Configure a Provider</p>
                <p class="text-xs text-slate-500">Add Mailgun, SMTP, or Twilio credentials under the tenant's Providers tab. Credentials are encrypted at rest.</p>
            </div>
        </li>
        <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center">3</span>
            <div>
                <p class="text-sm font-medium text-slate-800">Generate an API Key</p>
                <p class="text-xs text-slate-500">Issue a secret API key for the tenant. They will use it as a Bearer token.</p>
            </div>
        </li>
        <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center">4</span>
            <div>
                <p class="text-sm font-medium text-slate-800">Send Notifications via API</p>
                <p class="text-xs text-slate-500 font-mono">POST /api/v1/notifications/send · Bearer &lt;key&gt;</p>
            </div>
        </li>
    </ol>
</div>

{{-- Recent Tenants --}}
@if($recentTenants->isNotEmpty())
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-900">Recent Tenants</h2>
        <a href="{{ route('tenants.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">View all →</a>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100">
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Name</th>
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Slug</th>
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Status</th>
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Created</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($recentTenants as $tenant)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-3 font-medium text-slate-900">{{ $tenant->name }}</td>
                <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $tenant->slug }}</td>
                <td class="px-6 py-3">
                    @if($tenant->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-slate-500 text-xs">{{ $tenant->created_at->diffForHumans() }}</td>
                <td class="px-6 py-3 text-right">
                    <a href="{{ route('tenants.show', $tenant->id) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Manage →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center">
    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
        </svg>
    </div>
    <p class="text-sm font-medium text-slate-700">No tenants yet</p>
    <p class="text-xs text-slate-500 mt-1 mb-4">Get started by creating your first tenant.</p>
    <a href="{{ route('tenants.create') }}"
       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Create Tenant
    </a>
</div>
@endif

@endsection
