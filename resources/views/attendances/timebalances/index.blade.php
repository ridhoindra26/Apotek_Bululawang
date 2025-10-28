@extends('layout.layout')
@section('title','Time Balances')
@section('page_title','Time Balances')

@section('content')
<div class="container-fluid mx-auto sm:px-6">
  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">

    {{-- BREADCRUMB + SUBMENU --}}
    <nav class="flex items-center justify-between mb-4 text-sm text-slate-600">
      {{-- <ol class="flex items-center space-x-2">
        <li>
          <a href="{{ route('dashboard') }}" class="hover:text-[#318f8c] font-medium">Dashboard</a>
        </li>
        <li>/</li>
        <li>
          <a href="{{ route('attendances.index') }}" class="hover:text-[#318f8c] font-medium">Attendances</a>
        </li>
        @if(request()->routeIs('attendances.balance'))
          <li>/</li>
          <li class="text-slate-400">Time Balance</li>
        @endif
      </ol> --}}

      {{-- Submenu (toggle) --}}
      <div class="flex space-x-2">
        <a href="{{ route('attendances.index') }}"
          class="px-3 py-1.5 rounded-lg border text-sm font-medium
                  {{ request()->routeIs('attendances.index') ? 'bg-[#318f8c] text-white border-[#318f8c]' : 'border-slate-200 hover:bg-slate-50' }}">
          Attendance List
        </a>
        <a href="{{ route('attendances.balance') }}"
          class="px-3 py-1.5 rounded-lg border text-sm font-medium
                  {{ request()->routeIs('attendances.balance') ? 'bg-[#318f8c] text-white border-[#318f8c]' : 'border-slate-200 hover:bg-slate-50' }}">
          Time Balance
        </a>
      </div>
    </nav>

    @php
      $fmt = fn(int $m) => sprintf('%02d:%02d', intdiv(abs($m),60), abs($m)%60);
      $pill = function(int $net) {
        $label = $net > 0 ? 'Overtime' : ($net < 0 ? 'Debt' : 'Balanced');
        $cls   = $net > 0
          ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
          : ($net < 0 ? 'text-rose-700 bg-rose-50 border-rose-200'
                      : 'text-slate-600 bg-slate-50 border-slate-200');
        return "<span class=\"inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs $cls\">$label</span>";
      };
    @endphp

    {{-- TABLE --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="text-left text-slate-500 border-b">
            <th class="py-2 pr-4">Employee</th>
            <th class="py-2 pr-4">Credit (min)</th>
            <th class="py-2 pr-4">Debt (min)</th>
            <th class="py-2 pr-4">Net</th>
            <th class="py-2 pr-4">Updated</th>
            <th class="py-2 pr-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($timeBalances as $tb)
            @php
              $credit = (int) ($tb->credit_minutes ?? 0);
              $debt   = (int) ($tb->debt_minutes ?? 0);
              $net    = (int) $tb->net_minutes; // accessor
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="py-2 pr-4">
                <div class="font-medium text-slate-800">{{ $tb->employee->name ?? '—' }}</div>
                {{-- <div class="text-xs text-slate-500">#{{ $tb->id_employee }}</div> --}}
              </td>
              <td class="py-2 pr-4 text-emerald-700">
                +{{ $fmt($credit) }}
              </td>
              <td class="py-2 pr-4 text-rose-700">
                -{{ $fmt($debt) }}
              </td>
              <td class="py-2 pr-4">
                <div class="flex items-center gap-2">
                  <span class="{{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }} font-semibold">
                    {{ $tb->net_readable }}
                  </span>
                  {!! $pill($net) !!}
                </div>
              </td>
              <td class="py-2 pr-4 text-slate-500">
                {{ optional($tb->updated_at)->diffForHumans() ?? '—' }}
              </td>
              <td class="py-2 pr-4">
                <a 
                    href="{{ route('attendances.balance.show', ['id' => $tb->id_employee]) }}"
                   class="rounded-full border px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">
                  View Ledger
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-3 text-center text-slate-400">No balances found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $timeBalances->links() }}</div>

    <div class="mt-3 text-xs text-slate-500">
      <p><span class="font-semibold text-slate-700">Notes:</span> Credit = overtime minutes accumulated; Debt = minutes owed.
         Net = Credit − Debt.</p>
    </div>
  </div>
</div>
@endsection
