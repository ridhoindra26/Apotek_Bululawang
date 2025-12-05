@extends('layout.layout')

@section('title', 'Pengumuman')
@section('page_title', 'Pengumuman')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Daftar Pengumuman</h2>
    <a href="{{ route('announcements.create') }}"
       class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
        Tambah Pengumuman
    </a>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-2 text-left">Judul</th>
                <th class="px-4 py-2 text-left">Periode</th>
                <th class="px-4 py-2 text-left">Target</th>
                <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($announcements as $announcement)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2">
                        <div class="font-medium">
                            {{ $announcement->title }}
                            @if($announcement->is_active)
                                <span class="ml-2 text-[11px] px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                    Aktif
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 line-clamp-1">
                            {{ Str::limit($announcement->body, 80) }}
                        </div>
                    </td>
                    <td class="px-4 py-2 text-xs text-slate-600">
                        {{ $announcement->date_from }}
                        @if($announcement->date_to)
                            – {{ $announcement->date_to }}
                        @else
                            <span class="italic text-slate-400">tanpa batas</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-xs text-slate-600">
                        {{ $announcement->employees->count() }} karyawan
                    </td>
                    <td class="px-4 py-2 text-right text-xs">
                        <a href="{{ route('announcements.edit', $announcement) }}"
                           class="inline-flex items-center px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                            Edit
                        </a>
                        <form action="{{ route('announcements.destroy', $announcement->id) }}"
                              method="POST" class="inline-block"
                              onsubmit="return confirm('Hapus pengumuman ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-2 py-1 rounded border border-rose-200 text-rose-600 hover:bg-rose-50">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                        Belum ada pengumuman.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $announcements->links() }}
</div>
@endsection