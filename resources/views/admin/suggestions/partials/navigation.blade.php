@php
    $items = [
        [
            'route' => 'admin.suggestions.index',
            'active' => request()->routeIs('admin.suggestions.index', 'admin.suggestions.show', 'admin.suggestions.evaluation', 'admin.suggestions.status', 'admin.suggestions.correction'),
            'icon' => 'ri-task-line',
            'label' => 'Review Saran',
        ],
        [
            'route' => 'admin.suggestions.dashboard',
            'active' => request()->routeIs('admin.suggestions.dashboard'),
            'icon' => 'ri-line-chart-line',
            'label' => 'Analytics',
        ],
        [
            'route' => 'admin.suggestion-rewards.index',
            'active' => request()->routeIs('admin.suggestion-rewards.*'),
            'icon' => 'ri-gift-line',
            'label' => 'Reward',
        ],
    ];
@endphp

<nav class="mb-5 grid grid-cols-3 gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm" aria-label="Navigasi manajemen saran">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}"
           class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center text-xs font-bold transition sm:flex-row sm:gap-2 sm:text-sm {{ $item['active'] ? 'bg-[#318f8c] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-[#318f8c]' }}">
            <i class="{{ $item['icon'] }} text-lg"></i>
            <span class="truncate">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
