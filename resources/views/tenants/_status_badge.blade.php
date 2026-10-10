@php
$classes = match($status) {
    'sent'   => 'bg-emerald-100 text-emerald-700',
    'failed' => 'bg-red-100 text-red-700',
    'queued' => 'bg-amber-100 text-amber-700',
    default  => 'bg-slate-100 text-slate-600',
};
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $classes }} capitalize">{{ $status }}</span>
