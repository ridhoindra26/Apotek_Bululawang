@extends('layout.layout')

@section('title', 'List Dokumen Kasir')
@section('page_title', 'Dokumen Kasir - List')

@section('content')
<div class="space-y-4">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-800">Dokumen Kasir Terupload</h2>
            <p class="text-xs text-slate-500">
                Riwayat upload kertas tutup kasir, bukti setoran, cek darah, dan kas kecil.
            </p>
        </div>

        <a href="{{ route('cashier.index') }}"
           class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            ← Kembali ke Form Upload
        </a>
    </div>

    {{-- FILTERS --}}
    <div class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm">
        <form method="GET" action="{{ route('cashier.list') }}" class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
            {{-- Tanggal dari --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal dari</label>
                <input type="date"
                       name="date_from"
                       value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
            </div>

            {{-- Tanggal sampai --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal sampai</label>
                <input type="date"
                       name="date_to"
                       value="{{ $filters['date_to'] ?? '' }}"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
            </div>

            {{-- Tipe dokumen --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tipe</label>
                <select name="type"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
                    <option value="">Semua</option>
                    <option value="closing_cash" @selected(($filters['type'] ?? '') === 'closing_cash')>Kertas Tutup Kasir</option>
                    <option value="deposit_slip" @selected(($filters['type'] ?? '') === 'deposit_slip')>Bukti Setoran</option>
                    <option value="blood_check"  @selected(($filters['type'] ?? '') === 'blood_check')>Bukti Cek Darah</option>
                    <option value="petty_cash"   @selected(($filters['type'] ?? '') === 'petty_cash')>Kas Kecil</option>
                </select>
            </div>

            {{-- Shift --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Shift</label>
                <select name="shift"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
                    <option value="">Semua</option>
                    <option value="Pagi"  @selected(($filters['shift'] ?? '') === 'Pagi')>Pagi</option>
                    <option value="Siang" @selected(($filters['shift'] ?? '') === 'Siang')>Siang</option>
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Status</label>
                <select name="status"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
                    <option value="">Semua</option>
                    <option value="pending"   @selected(($filters['status'] ?? '') === 'pending')>Menunggu</option>
                    <option value="confirmed" @selected(($filters['status'] ?? '') === 'confirmed')>Terkonfirmasi</option>
                    <option value="rejected"  @selected(($filters['status'] ?? '') === 'rejected')>Ditolak</option>
                </select>
            </div>

            {{-- Cabang (untuk admin) --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Cabang</label>
                <select name="branch_id"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
                    <option value="">Semua</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            @selected(($filters['branch_id'] ?? '') == $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Search (deskripsi / nama kasir) --}}
            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Cari</label>
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Cari deskripsi atau nama kasir..."
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100">
            </div>

            {{-- Buttons --}}
            <div class="md:col-span-2 lg:col-span-2 flex items-end gap-2 mt-1">
                <button type="submit"
                        class="flex-1 rounded-full bg-[#318f8c] px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-[#318f8c]">
                    Terapkan Filter
                </button>
                <a href="{{ route('cashier.list') }}"
                   class="rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- INFO JUMLAH --}}
    <div class="text-[11px] text-slate-500">
        Menampilkan <span class="font-semibold text-slate-700">{{ $documents->total() }}</span> dokumen.
    </div>

    {{-- LIST MOBILE (CARD) --}}
    <div class="space-y-3 md:hidden">
        @forelse($documents as $doc)
            @php
                $photoUrls   = $doc->photo_urls ?? collect();
                $firstPhoto  = $photoUrls->first();
                $photoCount  = $photoUrls->count();

                $badgeColor = match($doc->status) {
                    'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                    'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                    default     => 'bg-slate-50 text-slate-600 border-slate-200',
                };
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-sm flex gap-3">
                @if($firstPhoto)
                    <button
                        type="button"
                        class="relative flex-shrink-0 btn-view-photos"
                        data-photos='@json($photoUrls)'
                        data-label="{{ $doc->date->format('d M Y') }} · {{ $doc->shift }}"
                    >
                        <img src="{{ $firstPhoto }}"
                            alt="Foto dokumen"
                            class="h-16 w-16 rounded-xl border border-slate-200 object-cover">

                        @if($photoCount > 1)
                            <span class="absolute -bottom-1 -right-1 rounded-full bg-black/70 px-1.5 py-0.5 text-[9px] font-medium text-white">
                                +{{ $photoCount - 1 }}
                            </span>
                        @endif
                    </button>
                @else
                    <div class="h-16 w-16 rounded-xl border border-dashed border-slate-200 flex items-center justify-center text-[10px] text-slate-400 flex-shrink-0">
                        No Foto
                    </div>
                @endif

                <div class="flex-1 space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-[11px] font-semibold text-slate-800">
                            {{ $doc->date->format('d M Y') }} · {{ $doc->shift }}
                        </div>
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] {{ $badgeColor }}">
                            {{ ucfirst($doc->status_label ?? $doc->status) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-1 text-[10px] text-slate-500">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5">
                            @switch($doc->type)
                                @case('closing_cash') Kertas Tutup Kasir @break
                                @case('deposit_slip') Bukti Setoran @break
                                @case('blood_check')  Bukti Cek Darah @break
                                @case('petty_cash')   Kas Kecil @break
                            @endswitch
                        </span>
                        @if($doc->cashier)
                            <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5">
                                Kasir: {{ $doc->cashier->name }}
                            </span>
                        @endif
                        @if($doc->branch)
                            <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5">
                                Cabang: {{ $doc->branch->name }}
                            </span>
                        @endif
                    </div>

                    @if($doc->description)
                        <p class="text-[11px] text-slate-600 line-clamp-2">
                            {{ $doc->description }}
                        </p>
                    @endif

                    {{-- tombol konfirmasi untuk admin/superadmin --}}
                    @auth
                    @php
                        $user       = auth()->user();
                        $isAdmin    = in_array($user->role, ['admin', 'superadmin']);
                        $employeeId = $user->employee->id ?? null;
                        $isOwner    = $employeeId && $employeeId === $doc->cashier_id;

                        $typeLabel = match($doc->type) {
                            'closing_cash' => 'Kertas Tutup Kasir',
                            'deposit_slip' => 'Bukti Setoran',
                            'blood_check'  => 'Bukti Cek Darah',
                            'petty_cash'   => 'Kas Kecil',
                            default        => $doc->type,
                        };
                        $summaryLabel = $doc->date->format('d M Y') . ' · ' . $doc->shift . ' · ' . $typeLabel;
                        $photoItems = $doc->photos->map(function ($photo) {
                            return [
                                'id'  => $photo->id,
                                'url' => $photo->url,
                            ];
                        })->values();
                    @endphp

                    <div class="flex flex-wrap gap-2 pt-1">
                        {{-- konfirmasi status: hanya admin/superadmin --}}
                        @if($isAdmin)
                            <button
                                type="button"
                                class="btn-confirm-doc inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-100"
                                data-id="{{ $doc->id }}"
                                data-status="{{ $doc->status }}"
                                data-note="{{ e($doc->admin_note) }}"
                                data-label="{{ $summaryLabel }}"
                            >
                                Ubah Status
                            </button>
                        @endif

                        {{-- edit foto & catatan: admin semua record, user hanya miliknya --}}
                        @if($isAdmin || $isOwner)
                            <button
                                type="button"
                                class="btn-edit-doc inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[10px] font-semibold text-slate-700 hover:bg-slate-50"
                                data-id="{{ $doc->id }}"
                                data-description="{{ e($doc->description) }}"
                                data-label="{{ $summaryLabel }}"
                                data-update-url="{{ route('cashier.update', $doc) }}"
                                data-photo-items='@json($photoItems)'
                            >
                                Edit Foto & Catatan
                            </button>
                        @endif
                    </div>
                @endauth
                    @if($doc->confirmed_by || $doc->confirmed_at)
                        <p class="text-[10px] text-slate-400">
                            @if($doc->confirmed_by)
                                Oleh: {{ $doc->confirmed_by }}
                            @endif
                            @if($doc->confirmed_at)
                                · {{ $doc->confirmed_at->format('d M Y H:i') }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-4 text-center text-xs text-slate-400">
                Belum ada dokumen yang sesuai filter.
            </div>
        @endforelse
    </div>

    {{-- LIST DESKTOP (TABLE) --}}
    <div class="hidden md:block rounded-2xl border border-slate-200 bg-white overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">Tanggal</th>
                    <th class="px-3 py-2 text-left">Shift</th>
                    <th class="px-3 py-2 text-left">Tipe</th>
                    <th class="px-3 py-2 text-left">Foto</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Kasir</th>
                    <th class="px-3 py-2 text-left">Cabang</th>
                    <th class="px-3 py-2 text-left">Keterangan</th>
                    <th class="px-3 py-2 text-right">Konfirmasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documents as $doc)
                    @php
                        $user       = auth()->user();
                        $isAdmin    = $user && in_array($user->role, ['admin', 'superadmin']);
                        $employeeId = $user?->employee?->id;
                        $isOwner    = $employeeId && $employeeId === $doc->cashier_id;
                        $photoUrls  = $doc->photo_urls ?? collect();
                        $firstPhoto = $photoUrls->first();
                        $photoCount = $photoUrls->count();
                        $badgeColor = match($doc->status) {
                            'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                            'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                            default     => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <tr class="text-xs text-slate-700">
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $doc->date->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $doc->shift }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] text-slate-700">
                                @switch($doc->type)
                                    @case('closing_cash') Kertas Tutup Kasir @break
                                    @case('deposit_slip') Bukti Setoran @break
                                    @case('blood_check')  Bukti Cek Darah @break
                                    @case('petty_cash')   Kas Kecil @break
                                @endswitch
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            @if($firstPhoto)
                                <button
                                    type="button"
                                    class="relative inline-block btn-view-photos"
                                    data-photos='@json($photoUrls)'
                                    data-label="{{ $doc->date->format('d M Y') }} · {{ $doc->shift }}"
                                >
                                    <img src="{{ $firstPhoto }}"
                                        class="h-12 w-12 rounded-lg border border-slate-200 object-cover"
                                        alt="Foto dokumen">

                                    @if($photoCount > 1)
                                        <span class="absolute -bottom-1 -right-1 rounded-full bg-black/75 px-1.5 py-0.5 text-[9px] font-medium text-white">
                                            +{{ $photoCount - 1 }}
                                        </span>
                                    @endif
                                </button>
                            @else
                                <span class="text-[11px] text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] {{ $badgeColor }}">
                                {{ ucfirst($doc->status_label ?? $doc->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $doc->cashier->name ?? '-' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $doc->branch->name ?? '-' }}
                        </td>
                        <td class="px-3 py-2 max-w-xs">
                            {{-- <p class="line-clamp-2 text-[11px] text-slate-600"> --}}
                                {{ $doc->description ?: '-' }}
                            {{-- </p> --}}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right text-[11px] text-slate-500">
                            <div class="space-y-1">
                                @if($doc->confirmed_by || $doc->confirmed_at)
                                    <div>{{ $doc->confirmed_by ?? '-' }}</div>
                                    @if($doc->confirmed_at)
                                        <div>{{ $doc->confirmed_at->format('d M Y H:i') }}</div>
                                    @endif
                                @else
                                    <div class="text-slate-400">Belum dikonfirmasi</div>
                                @endif

                                @auth
                                    @php
                                        $typeLabel = match($doc->type) {
                                            'closing_cash' => 'Kertas Tutup Kasir',
                                            'deposit_slip' => 'Bukti Setoran',
                                            'blood_check'  => 'Bukti Cek Darah',
                                            'petty_cash'   => 'Kas Kecil',
                                            default        => $doc->type,
                                        };
                                        $summaryLabel = $doc->date->format('d M Y') . ' · ' . $doc->shift . ' · ' . $typeLabel;
                                        $photoItems = $doc->photos->map(function ($photo) {
                                            return [
                                                'id'  => $photo->id,
                                                'url' => $photo->url,
                                            ];
                                        })->values();
                                    @endphp

                                    {{-- tombol konfirmasi status: hanya admin/superadmin --}}
                                    @if($isAdmin)
                                        <button
                                            type="button"
                                            class="btn-confirm-doc inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                                            data-id="{{ $doc->id }}"
                                            data-status="{{ $doc->status }}"
                                            data-note="{{ e($doc->admin_note) }}"
                                            data-label="{{ $summaryLabel }}"
                                        >
                                            Ubah Status
                                        </button>
                                    @endif

                                    {{-- tombol edit foto & catatan: admin semua record, user hanya record miliknya --}}
                                    @if($isAdmin || $isOwner)
                                        <button
                                            type="button"
                                            class="btn-edit-doc mt-1 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[10px] font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-100"
                                            data-id="{{ $doc->id }}"
                                            data-description="{{ e($doc->description) }}"
                                            data-label="{{ $summaryLabel }}"
                                            data-update-url="{{ route('cashier.update', $doc) }}"
                                            data-photo-items='@json($photoItems)'
                                        >
                                            Edit Foto & Catatan
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-4 text-center text-xs text-slate-400">
                            Belum ada dokumen yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div>
        {{ $documents->links() }}
    </div>
</div>

@auth
    @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
        @foreach($documents as $doc)
            <form id="confirm-form-{{ $doc->id }}"
                  action="{{ route('cashier.confirm', ['cashierDocuments' => $doc]) }}"
                  method="POST"
                  class="hidden">
                @csrf
                @method('POST')
                <input type="hidden" name="status" value="{{ $doc->status }}">
                <input type="hidden" name="admin_note" value="{{ $doc->admin_note }}">
            </form>
        @endforeach
    @endif
@endauth

@auth
    @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
        @foreach($documents as $doc)
            {{-- FORM KONFIRMASI STATUS --}}
            <form id="confirm-form-{{ $doc->id }}"
                  action="{{ route('cashier.confirm', $doc) }}"
                  method="POST"
                  class="hidden">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $doc->status }}">
                <input type="hidden" name="admin_note" value="{{ $doc->admin_note }}">
            </form>

            {{-- FORM EDIT DATA (tanpa field konfirmasi) --}}
            <form id="edit-form-{{ $doc->id }}"
                  action="{{ route('cashier.update', $doc) }}"
                  method="POST"
                  class="hidden">
                @csrf
                @method('PATCH')
                <input type="hidden" name="date" value="{{ $doc->date->format('Y-m-d') }}">
                <input type="hidden" name="shift" value="{{ $doc->shift }}">
                <input type="hidden" name="type" value="{{ $doc->type }}">
                <input type="hidden" name="description" value="{{ $doc->description }}">
            </form>
        @endforeach
    @endif
@endauth


@endsection
