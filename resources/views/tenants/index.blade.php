@extends('layouts.app')

@section('title', 'Tenants')

@section('breadcrumb')
    <span class="font-medium text-slate-900">Tenants</span>
@endsection

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Tenants</h1>
        <p class="mt-1 text-sm text-slate-500">Manage all registered tenants on the platform.</p>
    </div>
    <a href="{{ route('tenants.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Tenant
    </a>
</div>

@if($tenants->isNotEmpty())
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50">
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Name</th>
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Slug</th>
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Status</th>
                <th class="text-left text-xs font-medium text-slate-500 px-6 py-3">Created</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($tenants as $tenant)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-3.5 font-medium text-slate-900">{{ $tenant->name }}</td>
                <td class="px-6 py-3.5 text-slate-500 font-mono text-xs">{{ $tenant->slug }}</td>
                <td class="px-6 py-3.5">
                    @if($tenant->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $tenant->created_at->diffForHumans() }}</td>
                <td class="px-6 py-3.5 text-right">
                    <a href="{{ route('tenants.show', $tenant->id) }}"
                       class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Manage →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($tenants->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $tenants->links() }}
    </div>
    @endif
</div>
@else
<div class="bg-white rounded-xl border border-dashed border-slate-300 p-16 text-center">
    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
        </svg>
    </div>
    <p class="text-sm font-medium text-slate-700">No tenants yet</p>
    <p class="text-xs text-slate-500 mt-1 mb-4">Create your first tenant to get started.</p>
    <a href="{{ route('tenants.create') }}"
       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Create Tenant
    </a>
</div>
@endif

@endsection
