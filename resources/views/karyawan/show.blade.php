@extends('layout.layout')

@section('title', 'Detail Karyawan')
@section('page_title', 'Detail Karyawan')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Flash messages (optional) --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-semibold text-white">Detail Karyawan</h2>
                    <p class="text-slate-200 text-sm mt-1">
                        Informasi lengkap karyawan.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex items-center rounded-full bg-slate-800 px-3 py-1 text-xs font-medium text-slate-100 ring-1 ring-inset ring-slate-700">
                        ID: {{ $karyawan->id }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-slate-500">Nama</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $karyawan->name ?? 'Tidak Ada' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">Cabang</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ optional($karyawan->branches)->name ?? 'Tidak Diketahui' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">Role / Jabatan</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ optional($karyawan->roles)->name ?? 'Tidak Ada' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">No. Telepon</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ $karyawan->phone ?: 'Tidak Ada' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">Email Payroll</dt>
                    <dd class="mt-1 text-sm text-slate-900 break-all">
                        {{ $karyawan->payroll_email ?: 'Tidak Ada' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">Tanggal Lahir</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ optional($karyawan->date_of_birth)->format('d-m-Y') ?? 'Tidak Ada' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">Tanggal Masuk</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ optional($karyawan->date_start)->format('d-m-Y') ?? 'Tidak Ada' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500">Updated At</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ optional($karyawan->updated_at)->format('d-m-Y H:i') ?? 'Tidak Ada' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Footer actions --}}
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('karyawan.edit', $karyawan->id) }}"
                       class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                        Edit
                    </a>

                    <form action="{{ route('karyawan.destroy', $karyawan->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                            Hapus
                        </button>
                    </form>
                </div>

                <a href="{{ route('karyawan.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
