@extends('layout.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

@php
  $greeting_title = match(true) {
    now()->hour < 12 => 'Selamat Pagiii',
    now()->hour < 18 => 'Selamat Sianggg',
    default => 'Selamat Malammm',
  };
@endphp

<div class="mx-auto w-full max-w-4xl px-4 sm:px-6">
  {{-- HEADER CARD --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800">
          {{ $greeting_title }}, {{ auth()->user()->name }}
        </h2>
      </div>
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-slate-500 text-xs sm:text-sm">Hari ini</p>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800">
          {{ now()->locale('id_ID')->translatedFormat('l, d F Y') }}
        </h2>
      </div>
      <div class="text-left sm:text-right">
        <p class="text-slate-500 text-xs sm:text-sm">Jam</p>
        <h2 id="live-clock" class="text-xl sm:text-2xl font-mono font-semibold text-[#318f8c]">
          --:--:--
        </h2>
      </div>
    </div>

    {{-- STATUS SUMMARY --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
      <div class="rounded-xl border border-slate-200 p-3 text-center bg-slate-50">
        <p class="text-xs text-slate-500">Check-In</p>
        <p class="font-semibold text-slate-800">
          {{ $attendanceToday?->check_in_at ? $attendanceToday->check_in_at->format('H:i') : '—' }}
        </p>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 text-center bg-slate-50">
        <p class="text-xs text-slate-500">Check-Out</p>
        <p class="font-semibold text-slate-800">
          {{ $attendanceToday?->check_out_at ? $attendanceToday->check_out_at->format('H:i') : '—' }}
        </p>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 text-center bg-slate-50">
        <p class="text-xs text-slate-500">Status</p>
        <p class="font-semibold text-[#318f8c] capitalize">
          {{ $attendanceToday?->status ? str_replace('_',' ', $attendanceToday->status) : 'not checked in' }}
        </p>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 text-center bg-slate-50">
        <p class="text-xs text-slate-500">Durasi Kerja</p>
        <p class="font-semibold text-slate-800">
          {{ $attendanceToday?->work_minutes ? sprintf('%02d:%02d', floor($attendanceToday->work_minutes / 60), $attendanceToday->work_minutes % 60) : '—' }}
        </p>
      </div>
    </div>

    {{-- Hidden camera input --}}
    <input type="file" id="camera-input" accept="image/*" capture="user" class="hidden">

    {{-- ACTION BUTTONS --}}
    {{-- @dd(auth()->user()) --}}
    @if(auth()->user()->id_employee)
      <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button id="checkin-button-desktop"
                class="hidden sm:inline-flex w-full items-center justify-center !rounded-md bg-[#318f8c] px-6 py-3 text-white font-semibold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#318f8c]/40 active:opacity-100 transition-all">
          Check-In
        </button>

        <button id="checkout-button-desktop"
                class="hidden sm:inline-flex w-full items-center justify-center !rounded-md bg-[#318f8c] px-6 py-3 text-white font-semibold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#318f8c]/40 active:opacity-100 transition-all">
          Check-Out
        </button>
      </div>
    @else
      <p class="mt-6 text-center text-sm text-slate-500">
        Anda belum terdaftar sebagai karyawan.<br>
        Tidak bisa melakukan kehadiran.
      </p>
    @endif
  </div>

  {{-- RECENT HISTORY --}}
  @if(auth()->user()->id_employee)
  <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
    <h3 class="font-semibold text-slate-800 mb-4">Riwayat Kehadiran</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="text-left text-slate-500 border-b">
            <th class="py-2 pr-4">Tanggal</th>
            <th class="py-2 pr-4">Check-In</th>
            <th class="py-2 pr-4">Check-Out</th>
            <th class="py-2 pr-4">Durasi</th>
            <th class="py-2 pr-4">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recentAttendances as $a)
            <tr class="border-b hover:bg-slate-50">
              <td class="py-2 pr-4">{{ $a->work_date->format('Y-m-d') }}</td>
              <td class="py-2 pr-4">
                <button type="button"
                        class="text-[#318f8c] hover:underline js-photo-fetch"
                        data-url="{{ route('attendance.photo', ['type' => 'check_in', 'id' => $a->id]) }}"
                        data-caption="Check-In — {{ $a->work_date->format('Y-m-d') }} {{ $a->check_in_at->format('H:i') }}">
                  {{ $a->check_in_at->format('H:i') }}
                </button>
            </td>

            <td class="py-2 pr-4">
              @php
                $outTime = $a->check_out_at?->format('H:i');
              @endphp

              @if($outTime)
                <button type="button"
                        class="text-[#318f8c] hover:underline js-photo-fetch"
                        data-url="{{ route('attendance.photo', ['type' => 'check_out', 'id' => $a->id]) }}"
                        data-caption="Check-Out — {{ $a->work_date->format('Y-m-d') }} {{ $outTime }}">
                  {{ $outTime }}
                </button>
              @else
                {{ $outTime ?? '—' }}
              @endif
            </td>

              <td class="py-2 pr-4">
                {{ $a->work_minutes ? sprintf('%02d:%02d', floor($a->work_minutes / 60), $a->work_minutes % 60) : '—' }}
              </td>
              <td class="py-2 pr-4 capitalize text-[#318f8c]">{{ str_replace('_', ' ', $a->status) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-3 text-center text-slate-400">Tidak Ada Data Kehadiran Ditemukan</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @endif
</div>

{{-- MOBILE FLOATING BUTTONS --}}
@if(auth()->user()->id_employee)
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur px-4 pb-[calc(env(safe-area-inset-bottom)+12px)] pt-3 sm:hidden">
  <div class="mx-auto flex w-full max-w-md gap-3">
    <button id="checkin-button"
            class="flex-1 !rounded-md bg-[#318f8c] py-3 text-white font-semibold hover:opacity-90 focus:ring-2 focus:ring-[#318f8c]/40">
       Check-In
    </button>
    <button id="checkout-button"
            class="flex-1 !rounded-md bg-[#318f8c] py-3 text-white font-semibold hover:opacity-90 focus:ring-2 focus:ring-[#318f8c]/40">
      Check-Out
    </button>
  </div>
</div>
@endif

<div class="h-20 sm:h-0"></div>
@endsection
