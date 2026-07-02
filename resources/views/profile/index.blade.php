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
