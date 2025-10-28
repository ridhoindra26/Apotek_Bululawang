@extends('layout.layout')
@section('title','Time Ledger')
@section('page_title','Time Ledger')

@section('content')
<div class="container-fluid mx-auto sm:px-6">
  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">

    {{-- BREADCRUMB --}}
    <nav class="flex items-center justify-between mb-4 text-sm text-slate-600">
      <ol class="flex items-center space-x-2"></ol>
      <a href="{{ route('attendances.balance') }}"
         class="px-3 py-1.5 rounded-lg border text-sm font-medium border-slate-200 hover:bg-slate-50">
        ← Back
      </a>
    </nav>

    {{-- EMPLOYEE HEADER --}}
    <div class="flex items-center justify-between mb-5 border-b pb-3">
      <div>
        <p class="text-xs text-slate-500">Name</p>
        <p class="font-bold text-slate-800">
          ID: {{ $employee->name ?? '-' }}
        </p>
      </div>
      <div class="text-right">
        <p class="text-xs text-slate-500">Credit</p>
        <p class="font-semibold text-emerald-700">
          {{ $balance->credit_minutes ?? 0 }} minutes
        </p>
      </div>
      <div class="text-right">
        <p class="text-xs text-slate-500">Debt</p>
        <p class="font-semibold text-rose-700">
          {{ $balance->debt_minutes ?? 0 }} minutes
        </p>
      </div>
      <div class="text-right">
        @php
          $net = ($balance->credit_minutes ?? 0) - ($balance->debt_minutes ?? 0);
          $sign = $net >= 0 ? '+' : '-';
          $hours = floor(abs($net) / 60);
          $mins  = abs($net) % 60;
          $text  = sprintf('%s%02dh %02dm', $sign, $hours, $mins);
        @endphp
        <p class="text-xs text-slate-500">Current Balance</p>
        <p class="font-semibold {{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
          {{ $text }}
        </p>
      </div>

      {{-- ADJUSTMENT BUTTON --}}
      <button type="button"
              class="js-open-adjust whitespace-nowrap bg-[#318f8c] text-white px-4 py-2 !rounded-md shadow-sm hover:bg-[#2b7b79]">
        + Adjust Balance
      </button>
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="grid gap-3 sm:grid-cols-4 mb-4">
      <input type="date" name="from" value="{{ $from ?? '' }}"
             class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
      <input type="date" name="to" value="{{ $to ?? '' }}"
             class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">

      <select name="type" class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
        <option value="">All Types</option>
        <option value="overtime_add"   @selected(($type ?? '') === 'overtime_add')>Overtime Add</option>
        <option value="overtime_spend" @selected(($type ?? '') === 'overtime_spend')>Overtime Spend</option>
        <option value="penalty_add"    @selected(($type ?? '') === 'penalty_add')>Penalty Add</option>
        <option value="penalty_reduce" @selected(($type ?? '') === 'penalty_reduce')>Penalty Reduce</option>
      </select>

      <div class="flex gap-2 sm:col-span-1 sm:justify-end">
        <a href="{{ route('attendances.balance.show', ['id' => $employee->id]) }}"
           class="rounded-md border px-4 py-2 text-slate-700 hover:bg-slate-50">Reset</a>
        <button class="!rounded-md bg-[#318f8c] px-4 py-2 text-white font-semibold">Filter</button>
      </div>
    </form>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="text-left text-slate-500 border-b">
            <th class="py-2 pr-4">Date</th>
            <th class="py-2 pr-4">Type</th>
            <th class="py-2 pr-4">Minutes</th>
            <th class="py-2 pr-4">Source</th>
            <th class="py-2 pr-4">Note</th>
          </tr>
        </thead>
        <tbody>
          @forelse($ledgers as $lg)
            @php
              $isPositive = in_array($lg->type, ['overtime_add','penalty_reduce']);
              $minutes = $lg->minutes ?? 0;
              $note = $lg->note ?? '—';
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="py-2 pr-4">{{ $lg->work_date?->format('d M Y') ?? '—' }}</td>
              <td class="py-2 pr-4">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs
                  {{ $isPositive ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-rose-700 bg-rose-50 border-rose-200' }}">
                  {{ str_replace('_',' ',ucfirst($lg->type)) }}
                </span>
              </td>
              <td class="py-2 pr-4 font-semibold {{ $isPositive ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $isPositive ? '+' : '-' }}{{ $minutes }} min
              </td>
              <td class="py-2 pr-4 text-slate-600">{{ $lg->source ?? '—' }}</td>

              <td class="py-2 pr-4 max-w-[260px]">
                <div class="truncate text-slate-500 js-note" title="{{ $note }}">{{ $note }}</div>
                @if (strlen($note) > 200)
                  <div class="mt-1 text-xs text-slate-500">
                    <button type="button" class="px-2 py-1 rounded-lg border hover:bg-slate-50 js-show-more">
                      Show more
                    </button>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-3 text-center text-slate-400">No ledger records found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $ledgers->links() }}</div>

    <div class="mt-3 text-xs text-slate-500">
      <p>
        <span class="font-semibold text-slate-700">Notes:</span>
        Positive = overtime or correction credit; Negative = penalty or time debt.
      </p>
    </div>

    {{-- Flash hook for JS (SweetAlert) --}}
    <div id="flash-data"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"></div>

    {{-- Modal moved to partial --}}
    @include('attendances.timebalances._adjust-modal', ['employeeId' => $employee->id])

  </div>
</div>
@endsection
