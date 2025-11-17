@extends('layout.layout')
@section('title','Attendances')
@section('page_title','Attendances')

@section('content')
<div class="container-fluid mx-auto sm:px-6">
  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">

    {{-- BREADCRUMB --}}
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

    {{-- FILTERS --}}
    <form method="GET" class="grid gap-3 sm:grid-cols-6 mb-4">
      <input type="text" name="q" value="{{ $qName }}" placeholder="Search employee..."
             class=" rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
      <select name="branch" class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
        <option value="">All branches</option>
        @foreach($branches as $b)
          <option value="{{ $b->id }}" @selected($branch==$b->id)>{{ $b->name }}</option>
        @endforeach
      </select>
      <input type="date" name="from" value="{{ $from }}"
             class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
      <input type="date" name="to" value="{{ $to }}"
             class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
      {{-- <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#318f8c] focus:ring-0">
        <option value="">All status</option>
        <option value="present" @selected($status==='present')>present</option>
        <option value="absent"  @selected($status==='absent')>absent</option>
        <option value="late"    @selected($status==='late')>late</option>
      </select> --}}
      <button class="!rounded-md bg-[#318f8c] px-4 py-2 text-white font-semibold">Filter</button>
    </form>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="text-left text-slate-500 border-b">
            <th class="py-2 pr-4">Date</th>
            <th class="py-2 pr-4">Employee</th>
            <th class="py-2 pr-4">Branch</th>
            <th class="py-2 pr-4">In</th>
            <th class="py-2 pr-4">Out</th>
            <th class="py-2 pr-4">Work</th>
            <th class="py-2 pr-4 text-rose-700">Late</th>
            <th class="py-2 pr-4 text-rose-700">Early L.</th>
            <th class="py-2 pr-4 text-emerald-700">Early In</th>
            <th class="py-2 pr-4 text-emerald-700">Overtime</th>
            <th class="py-2 pr-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($att as $a)
            @php
              $d = $a->work_date?->format('d M Y');
              $in = $a->check_in_at?->format('H:i');
              $out= $a->check_out_at?->format('H:i');
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="py-2 pr-4">{{ $d }}</td>
              <td class="py-2 pr-4">{{ $a->employee->name ?? '-' }}</td>
              <td class="py-2 pr-4">{{ $a->branch->name ?? '-' }}</td>

              <td class="py-2 pr-4">
                @if($in)
                  <button type="button" class="text-[#318f8c] hover:underline js-photo-fetch"
                          data-url="{{ route('attendance.photo', ['type' => 'check_in', 'id' => $a->id]) }}"
                          data-caption="Check In — {{ $d }} {{ $in }}">
                    {{ $in }}
                  </button>
                @else
                  —
                @endif
              </td>

              <td class="py-2 pr-4">
                @if($out)
                  <button type="button" class="text-[#318f8c] hover:underline js-photo-fetch"
                          data-url="{{ route('attendance.photo', ['type' => 'check_out', 'id' => $a->id]) }}"
                          data-caption="Check Out — {{ $d }} {{ $out }}">
                    {{ $out }}
                  </button>
                @else
                  —
                @endif
              </td>

              <td class="py-2 pr-4">
                {{ $a->work_minutes ? sprintf('%02d:%02d', intdiv($a->work_minutes,60), $a->work_minutes%60) : '—' }}
              </td>
              <td class="py-2 pr-4">{{ $a->late_minutes ?? 0 }}</td>
              <td class="py-2 pr-4">{{ $a->early_leave_minutes ?? 0 }}</td>
              <td class="py-2 pr-4">{{ $a->early_checkin_minutes ?? 0 }}</td>
              <td class="py-2 pr-4">{{ $a->overtime_minutes ?? 0 }}</td>

              <td class="py-2 pr-4">
                <button type="button"
                        class="rounded-full border px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        onclick="handleMinutesPanel({{ $a->id }}, {{ $a->is_confirmed ? 1 : 0 }})">
                  {{ $a->is_confirmed ? 'Edit' : 'Confirm' }}
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="py-3 text-center text-slate-400">No records.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $att->links() }}</div>
  </div>
</div>

{{-- Slide-over panel --}}
@include('attendances._minutes-panel')

@endsection
