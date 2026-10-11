@php
    $classes = match ($status) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-slate-100 text-slate-600',
        'overdue' => 'bg-red-100 text-red-700',
        'due_today' => 'bg-amber-100 text-amber-700',
        'due_soon' => 'bg-blue-100 text-blue-700',
        'normal' => 'bg-gray-100 text-gray-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    {{ $label }}
</span>