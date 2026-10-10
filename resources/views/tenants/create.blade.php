@extends('layouts.app')

@section('title', 'New Tenant')

@section('breadcrumb')
    <a href="{{ route('tenants.index') }}" class="text-slate-500 hover:text-slate-700">Tenants</a>
    <svg class="w-4 h-4 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="font-medium text-slate-900">New Tenant</span>
@endsection

@section('content')

<div class="max-w-lg">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">New Tenant</h1>
        <p class="mt-1 text-sm text-slate-500">Register a new tenant to start managing their notification providers and API keys.</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('tenants.store') }}">
            @csrf
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Tenant Name</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="e.g. Acme Corporation"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                              @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-500">A unique slug will be generated automatically.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                    Create Tenant
                </button>
                <a href="{{ route('tenants.index') }}" class="text-sm text-slate-600 hover:text-slate-800">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
