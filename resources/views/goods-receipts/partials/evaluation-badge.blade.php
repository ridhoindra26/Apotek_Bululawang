@php
    $classes = match ($status) {
        'unchecked' => 'bg-gray-100 text-gray-700',
        'matched' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'partially_rejected' => 'bg-amber-100 text-amber-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    {{ $label }}
</span>
