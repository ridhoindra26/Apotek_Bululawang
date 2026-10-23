@extends('layout.layout')
@section('title','Feed Saran')
@section('page_title', 'Feed Saran')
@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-5"><h1 class="text-xl font-black text-slate-900 sm:text-2xl">Saran & Continuous Improvement</h1><p class="mt-1 text-sm text-slate-500">Ide publik dari seluruh cabang.</p></div>
    @include('suggestions.partials.navigation')
    <form method="GET" class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm [&_input]:min-h-11 [&_input]:w-full [&_input]:rounded-xl [&_input]:border-gray-200 [&_input]:bg-gray-50/60 [&_input:focus]:border-[#318f8c] [&_input:focus]:ring-4 [&_input:focus]:ring-[#318f8c]/10 [&_select]:min-h-11 [&_select]:w-full [&_select]:rounded-xl [&_select]:border-gray-200 [&_select]:bg-gray-50/60 [&_select:focus]:border-[#318f8c] [&_select:focus]:ring-4 [&_select:focus]:ring-[#318f8c]/10">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <input name="search" value="{{ request('search') }}" placeholder="Cari judul atau isi..." class="rounded-xl border-slate-300 lg:col-span-2">
            <select name="type" class="rounded-xl border-slate-300"><option value="">Semua jenis</option>@foreach(['suggestion'=>'Saran','complaint'=>'Keluhan','improvement'=>'Improvement'] as $value=>$label)<option value="{{ $value }}" @selected(request('type')===$value)>{{ $label }}</option>@endforeach</select>
            <select name="status" class="rounded-xl border-slate-300"><option value="">Semua status</option>@foreach(['submitted'=>'Diajukan','under_review'=>'Ditinjau','accepted'=>'Diterima','rejected'=>'Ditolak','planned'=>'Direncanakan','in_progress'=>'Dikerjakan','implemented'=>'Diimplementasikan','closed'=>'Ditutup'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select>
            <select name="branch_id" class="rounded-xl border-slate-300"><option value="">Semua target</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)request('branch_id')===(string)$branch->id)>{{ $branch->name }}</option>@endforeach</select>
            <select name="sort" class="rounded-xl border-slate-300"><option value="newest" @selected(request('sort')!=='oldest')>Terbaru</option><option value="oldest" @selected(request('sort')==='oldest')>Terlama</option></select>
            <button class="min-h-11 rounded-xl bg-[#318f8c] px-4 py-2 font-bold text-white">Terapkan Filter</button>
            @if(request()->hasAny(['search','type','status','branch_id','sort']))<a href="{{ route('suggestions.index') }}" class="flex min-h-11 items-center justify-center rounded-xl border px-4 py-2 text-center font-semibold text-slate-600">Reset</a>@endif
        </div>
    </form>
    @if($suggestions->isEmpty())<div class="rounded-2xl border border-dashed bg-white text-center text-slate-500 p-3"><i class="ri-lightbulb-line text-5xl text-slate-300"></i><p class="mt-3 font-semibold">Belum ada saran publik yang sesuai.</p></div>
    @else<div class="grid gap-3 sm:gap-4 md:grid-cols-2 xl:grid-cols-3">@foreach($suggestions as $suggestion) @include('suggestions.partials.card') @endforeach</div><div class="mt-6">{{ $suggestions->links() }}</div>@endif
</div>
@endsection
