@extends('layout.layout')

@section('title', 'Profil')
@section('page_title', 'Profil')

@section('content')
<div class="max-w-3xl space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-semibold">
                    {{ strtoupper(substr($user->username ?? $user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-base font-semibold text-slate-800 truncate">
                        {{ $user->name ?? $user->name }}
                    </div>
                    <div class="text-sm text-slate-500 truncate">
                        {{ $user->email ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="sm:ml-auto flex items-center gap-2">
                <a href="{{ route('profile.password.edit') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Ubah Password
                </a>
            </div>
        </div>
    </div>

    @if($rewardSummary)
    <div class="rounded-2xl border border-violet-200 bg-white p-4 shadow-sm sm:p-6">
        <h2 class="mb-4 text-sm font-semibold text-slate-800"><i class="ri-gift-line text-violet-600"></i> Ringkasan Reward Saran</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-3"><small class="text-slate-500">Total</small><b class="block text-xl">{{ number_format($rewardSummary->total ?? 0) }}</b></div>
            <div class="rounded-xl bg-amber-50 p-3"><small class="text-amber-700">Menunggu</small><b class="block text-xl">{{ number_format($rewardSummary->pending ?? 0) }}</b></div>
            <div class="rounded-xl bg-teal-50 p-3"><small class="text-teal-700">Diserahkan</small><b class="block text-xl">{{ number_format($rewardSummary->delivered ?? 0) }}</b></div>
            <div class="rounded-xl bg-violet-50 p-3"><small class="text-violet-700">Total Uang</small><b class="block text-sm">Rp {{ number_format($rewardSummary->delivered_money ?? 0,0,',','.') }}</b></div>
        </div>
    </div>
    @endif

    @if($suggestionSummary)
    <div class="rounded-2xl border border-teal-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-4 flex items-center justify-between"><h2 class="text-sm font-semibold text-slate-800"><i class="ri-lightbulb-line text-[#318f8c]"></i> Kontribusi Improvement</h2><a href="{{ route('suggestions.mine') }}" class="text-xs font-bold text-[#318f8c]">Lihat saran saya</a></div>
        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-xl bg-slate-50 p-3"><small class="text-slate-500">Diajukan</small><b class="block text-xl">{{ number_format($suggestionSummary->submitted ?? 0) }}</b></div>
            <div class="rounded-xl bg-teal-50 p-3"><small class="text-teal-700">Diimplementasikan</small><b class="block text-xl">{{ number_format($suggestionSummary->implemented ?? 0) }}</b></div>
        </div>
    </div>
    @endif

    {{-- Account details (read-only) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">Profil Saya</h2>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-100 p-3">
                <div class="text-xs text-slate-500">Username</div>
                <div class="text-sm font-medium text-slate-800">{{ $user->username ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-slate-100 p-3">
                <div class="text-xs text-slate-500">No. Telepon</div>
                <div class="text-sm font-medium text-slate-800">{{ $user->employee->phone ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-slate-100 p-3">
                <div class="text-xs text-slate-500">Email</div>
                <div class="text-sm font-medium text-slate-800">{{ $user->email ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-slate-100 p-3">
                <div class="text-xs text-slate-500">Tanggal Lahir</div>
                <div class="text-sm font-medium text-slate-800">{{ $user->employee->date_of_birth ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-slate-100 p-3">
                <div class="text-xs text-slate-500">Tanggal Masuk</div>
                <div class="text-sm font-medium text-slate-800">
                    {{ $user->employee->date_start ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
