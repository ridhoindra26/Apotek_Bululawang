@props([
    'title',
    'value',
    'subtitle' => null,
    'color' => 'blue',
    'icon' => null,
])

@php
    $colorMap = [
        'blue' => 'bg-blue-50 text-blue-600 ring-blue-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'indigo' => 'bg-indigo-50 text-indigo-600 ring-indigo-100',
        'cyan' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',
        'violet' => 'bg-violet-50 text-violet-600 ring-violet-100',
        'green' => 'bg-green-50 text-green-600 ring-green-100',
    ];

    $badgeClass = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
            <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ $value }}</h3>
            @if($subtitle)
                <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="flex h-11 w-11 items-center justify-center rounded-xl ring-1 {{ $badgeClass }}">
            <span class="text-sm font-bold">{{ strtoupper(substr($title, 0, 1)) }}</span>
        </div>
    </div>
</div>