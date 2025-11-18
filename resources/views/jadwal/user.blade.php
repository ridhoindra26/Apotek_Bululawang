@extends('layout.layout')

@section('title','Jadwal Saya')
@section('page_title','Jadwal Saya')

@section('content')
<div class="container mx-auto px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
    {{-- HEADER / HERO --}}
    <div class="mb-4">
        <h2 class="text-xl sm:text-2xl font-bold">
            {{-- Halo, {{ auth()->user()->name ?? 'Karyawan' }} --}}
            Hari ini, {{ \Carbon\Carbon::now()->locale('id_ID')->translatedFormat('l, d F Y') }}
        </h2>

        {{-- Card status hari ini --}}
        <div class="mt-3 p-3 rounded-xl border bg-[rgba(49,152,152,0.15)] flex items-start gap-3">
            <div class="mt-0.5 text-lg">📅</div>
            <div>
                @if($todaySchedule)
                    @php
                        $isLeave = $todaySchedule->is_vacation;
                        $branchName = $todaySchedule->branches->name ?? '-';
                        $startTime = $todaySchedule->shiftTime->start_time ?? null;
                        $endTime   = $todaySchedule->shiftTime->end_time ?? null;
                    @endphp

                    <p class="text-center text-xl sm:text-base font-bold m-0">
                        @if($isLeave)
                            Hari ini kamu <span class="text-red-600">LIBUR</span>
                        @else
                            Hari ini kamu masuk
                            <span class="font-bold">
                                shift {{ $todaySchedule->shift ?? '-' }}
                            </span>
                        @endif
                    </p>

                    @if(!$isLeave)
                        <p class="text-xs sm:text-sm text-gray-700 mt-1">
                            Cabang: {{ $branchName }} <br>
                            {{-- @if($startTime && $endTime) --}}
                                Jam: {{ \Illuminate\Support\Str::substr($startTime,0,5) }}
                                – {{ \Illuminate\Support\Str::substr($endTime,0,5) }}
                            {{-- @endif --}}
                        </p>
                    @endif
                @else
                    <p class="text-sm sm:text-base font-semibold">
                        Jadwal hari ini belum tersedia.
                    </p>
                    <p class="text-xs sm:text-sm text-gray-700">
                        Silakan hubungi admin jika menurutmu ini tidak sesuai.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB (sementara cuma 1) --}}
    <div class="mb-4 border-b flex">
        <button
            class="px-3 py-2 text-xs sm:text-sm border-b-2 border-[#318f8c] font-bold">
            Jadwal Saya
        </button>
    </div>

    {{-- LIST JADWAL N HARI KE DEPAN --}}
    <div class="space-y-2">
        @forelse($upcomingSchedules as $sch)
            @php
                $date = $sch->date; // sudah cast ke Carbon
                $isLeave = $sch->is_vacation;
                $branchName = $sch->branches->name ?? '-';
                $startTime = $sch->shiftTime->start_time ?? null;
                $endTime   = $sch->shiftTime->end_time ?? null;

                $badgeColor = $isLeave
                    ? 'bg-red-100 text-red-700 border-red-200'
                    : ($sch->shift === 'Pagi'
                        ? 'bg-yellow-100 text-yellow-800 border-yellow-300'
                        : 'bg-blue-100 text-blue-800 border-blue-300');
            @endphp

            <div class="p-3 rounded-xl border bg-white flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500">
                        {{-- {{$date}} --}}
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->locale('id_ID')->translatedFormat('l, d F Y') }}
                    </p>
                    <p class="text-sm font-semibold">
                        @if($isLeave)
                            Selamat Istirahat :)
                        @else
                            Shift {{ $sch->shift ?? '-' }}
                        @endif
                    </p>

                    @if(!$isLeave)
                        <p class="text-xs text-gray-600 mt-1">
                            Cabang: {{ $branchName }} <br>
                            {{-- @if($startTime && $endTime) --}}
                                Jam: {{ \Illuminate\Support\Str::substr($startTime,0,5) }}
                                – {{ \Illuminate\Support\Str::substr($endTime,0,5) }}
                            {{-- @endif --}}
                        </p>
                    @endif
                </div>

                <span class="px-2 py-1 rounded-full border text-[11px] {{ $badgeColor }} whitespace-nowrap">
                    @if($isLeave)
                        Libur
                    @else
                        Shift {{ $sch->shift ?? '-' }}
                    @endif
                </span>
            </div>
        @empty
            <p class="text-xs sm:text-sm text-gray-500">
                Belum ada jadwal yang tercatat untuk beberapa hari ke depan.
            </p>
        @endforelse
    </div>

    {{-- LEGEND --}}
    <div class="mt-6 border-t pt-3 text-xs sm:text-sm text-gray-600">
        <p class="font-semibold mb-1">Note:</p>

        <p class="mt-2 text-[11px] sm:text-xs text-gray-500">
            Jadwal dapat berubah sewaktu-waktu. Silakan cek kembali setiap hari sebelum jam kerja.
        </p>
    </div>
</div>
@endsection
