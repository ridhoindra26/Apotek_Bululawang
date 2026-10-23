<nav class="mb-5 grid grid-cols-2 gap-2 sm:grid-cols-4" aria-label="Navigasi saran dan improvement">
    @foreach ([
        ['route' => 'suggestions.index', 'label' => 'Feed Publik', 'active' => 'suggestions.index'],
        ['route' => 'suggestions.mine', 'label' => 'Saran Saya', 'active' => 'suggestions.mine'],
        ['route' => 'suggestions.archive', 'label' => 'Arsip Implementasi', 'active' => 'suggestions.archive'],
    ] as $item)
        <a href="{{ route($item['route']) }}" class="flex min-h-11 items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold sm:text-sm {{ request()->routeIs($item['active']) ? 'bg-[#318f8c] text-white shadow-md' : 'border border-slate-200 bg-white text-slate-600' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
    @can('create', App\Models\Suggestion::class)
        <a href="{{ route('suggestions.create') }}" class="flex min-h-11 items-center justify-center gap-1 rounded-xl bg-slate-900 px-3 py-2 text-center text-xs font-semibold text-white sm:text-sm">
            <i class="ri-add-line"></i> Ajukan Saran
        </a>
    @endcan
</nav>
