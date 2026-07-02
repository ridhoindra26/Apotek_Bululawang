<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Clicks</div>
        <div class="mt-3 text-2xl font-bold text-slate-800">
            {{ number_format($stats['total_clicks'] ?? 0) }}
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Clicks Today</div>
        <div class="mt-3 text-2xl font-bold text-slate-800">
            {{ number_format($stats['clicks_today'] ?? 0) }}
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">7 Days</div>
        <div class="mt-3 text-2xl font-bold text-slate-800">
            {{ number_format($stats['clicks_7_days'] ?? 0) }}
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Unique Visitors</div>
        <div class="mt-3 text-2xl font-bold text-slate-800">
            {{ number_format($stats['unique_visitors'] ?? 0) }}
        </div>
    </div>
</div>