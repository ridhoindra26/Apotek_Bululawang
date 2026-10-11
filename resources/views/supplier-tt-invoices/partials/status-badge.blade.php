@php
    $classes = match ($status) {
        'draft' => 'bg-gray-100 text-gray-700',
        'verified' => 'bg-green-100 text-green-700',
        'scheduled_payment' => 'bg-blue-100 text-blue-700',
        'paid' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-slate-100 text-slate-600',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    {{ $label }}
</span>
