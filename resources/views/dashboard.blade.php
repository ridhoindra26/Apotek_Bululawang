@extends('layout.layout')

@section('title','Dashboard')
@section('page_title','Dashboard')

{{-- @section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
  <div class="rounded-xl border border-slate-200 bg-white p-4">
    <p class="text-sm text-slate-500">Total Users</p>
    <p class="mt-2 text-2xl font-bold text-slate-800">1,284</p>
  </div>
  <div class="rounded-xl border border-slate-200 bg-white p-4">
    <p class="text-sm text-slate-500">Active Sessions</p>
    <p class="mt-2 text-2xl font-bold text-slate-800">87</p>
  </div>
</div>
@endsection --}}

@section('content')
<div class="mx-auto w-full max-w-md px-4 sm:max-w-xl sm:px-6">
  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-slate-500 text-xs sm:text-sm">Today</p>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800">{{ now()->translatedFormat('l, d F Y') }}</h2>
      </div>
      <div class="text-left sm:text-right">
        <p class="text-slate-500 text-xs sm:text-sm">Current Time</p>
        <h2 id="live-clock" class="text-xl sm:text-2xl font-mono font-semibold text-[#318f8c]">--:--:--</h2>
      </div>
    </div>

    {{-- Hidden camera input --}}
    <input type="file" id="camera-input" accept="image/*" capture="environment" class="hidden">

    <div class="mt-4 grid grid-cols-1 gap-3 sm:mt-6 sm:grid-cols-2">
      <button id="checkin-button-desktop"
              class="hidden sm:inline-flex w-full items-center justify-center !rounded-full bg-[#318f8c] px-6 py-3 text-white font-semibold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#318f8c]/40 active:opacity-100 transition-all">
        Check In
      </button>

      <button id="checkout-button-desktop"
              class="hidden sm:inline-flex w-full items-center justify-center !rounded-full bg-[#318f8c] px-6 py-3 text-white font-semibold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#318f8c]/40 active:opacity-100 transition-all">
        Check Out
      </button>
    </div>
  </div>
</div>

{{-- Mobile floating buttons --}}
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur px-4 pb-[calc(env(safe-area-inset-bottom)+12px)] pt-3 sm:hidden">
  <div class="mx-auto flex w-full max-w-md gap-3">
    <button id="checkin-button"
            class="flex-1 !rounded-full bg-[#318f8c] py-3 text-white font-semibold hover:opacity-90 focus:ring-2 focus:ring-[#318f8c]/40">
      Check In
    </button>
    <button id="checkout-button"
            class="flex-1 !rounded-full bg-[#318f8c] py-3 text-white font-semibold hover:opacity-90 focus:ring-2 focus:ring-[#318f8c]/40">
      Check Out
    </button>
  </div>
</div>

<div class="h-20 sm:h-0"></div>
@endsection
