@php
    $statusLabels = ['submitted'=>'Diajukan','under_review'=>'Ditinjau','accepted'=>'Diterima','rejected'=>'Ditolak','planned'=>'Direncanakan','in_progress'=>'Dikerjakan','implemented'=>'Diimplementasikan','closed'=>'Ditutup'];
    $statusClasses = ['submitted'=>'bg-blue-50 text-blue-700','under_review'=>'bg-amber-50 text-amber-700','accepted'=>'bg-emerald-50 text-emerald-700','rejected'=>'bg-red-50 text-red-700','planned'=>'bg-violet-50 text-violet-700','in_progress'=>'bg-cyan-50 text-cyan-700','implemented'=>'bg-teal-50 text-teal-700','closed'=>'bg-slate-100 text-slate-600'];
    $typeLabels = ['suggestion'=>'Saran','complaint'=>'Keluhan','improvement'=>'Improvement'];
@endphp
<article class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:rounded-2xl sm:p-5">
    <div class="mb-2 flex flex-wrap items-center gap-1.5 sm:mb-3 sm:gap-2">
        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold sm:px-2.5 sm:py-1 sm:text-xs {{ $statusClasses[$suggestion->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$suggestion->status] ?? $suggestion->status }}</span>
        <span class="rounded-full bg-[#318f8c]/10 px-2 py-0.5 text-[11px] font-bold text-[#287673] sm:px-2.5 sm:py-1 sm:text-xs">{{ $typeLabels[$suggestion->type] ?? $suggestion->type }}</span>
        @if (($showVisibility ?? false))
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 sm:px-2.5 sm:py-1 sm:text-xs">{{ $suggestion->visibility === 'private' ? 'Privat' : 'Publik' }}</span>
        @endif
        @if (($showVisibility ?? false) && $suggestion->activeReward)
            <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-bold text-violet-700 sm:px-2.5 sm:py-1 sm:text-xs"><i class="ri-gift-line"></i> {{ $suggestion->activeReward->status === 'delivered' ? 'Reward Diserahkan' : 'Reward Menunggu' }}</span>
        @endif
    </div>
    <h2 class="line-clamp-2 text-base font-bold leading-snug text-slate-900 sm:text-lg">{{ $suggestion->title }}</h2>
    <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-600 sm:mt-2 sm:line-clamp-3 sm:text-sm sm:leading-6">{{ Str::limit($suggestion->previous_condition, 180) }}</p>
    <div class="mt-2.5 space-y-0.5 text-[11px] text-slate-500 sm:mt-4 sm:space-y-1 sm:text-xs">
        <p><i class="ri-user-line mr-1"></i>{{ $suggestion->submitter_name_snapshot ?: ($suggestion->submitter?->name ?? '-') }}</p>
        <p><i class="ri-map-pin-line mr-1"></i>{{ $suggestion->targetBranch?->name ?? 'Seluruh Perusahaan' }}</p>
        <p><i class="ri-calendar-line mr-1"></i>{{ $suggestion->submitted_at?->translatedFormat('d M Y, H:i') ?? '-' }}</p>
    </div>
    <div class="mt-auto flex gap-2 pt-3 sm:pt-5">
        <a href="{{ route('suggestions.show', $suggestion) }}" class="flex min-h-9 flex-1 items-center justify-center rounded-lg border border-[#318f8c] px-3 py-1.5 text-center text-xs font-bold text-[#318f8c] sm:min-h-10 sm:rounded-xl sm:py-2 sm:text-sm">Lihat Detail</a>
        @can('update', $suggestion)
            <a href="{{ route('suggestions.edit', $suggestion) }}" class="flex min-h-9 items-center justify-center rounded-lg bg-[#318f8c] px-3 py-1.5 text-xs font-bold text-white sm:min-h-10 sm:rounded-xl sm:px-4 sm:py-2 sm:text-sm">Edit</a>
        @endcan
    </div>
</article>
