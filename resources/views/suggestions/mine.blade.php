@extends('layout.layout')
@section('title','Saran Saya')
@section('page_title', 'Saran Saya')
@section('content')
<div class="mx-auto max-w-7xl"><div class="mb-5"><h1 class="text-2xl font-black text-slate-900">Saran Saya</h1><p class="text-sm text-slate-500">Pantau seluruh pengajuan publik maupun privat Anda.</p></div>@include('suggestions.partials.navigation')
@if($suggestions->isEmpty())<div class="rounded-2xl border border-dashed bg-white p-3 text-center"><p class="text-slate-500">Anda belum pernah mengajukan saran.</p><a href="{{ route('suggestions.create') }}" class="mt-4 inline-block rounded-xl bg-[#318f8c] px-4 py-2 font-bold text-white">Ajukan Saran</a></div>@else<div class="grid gap-3 sm:gap-4 md:grid-cols-2 xl:grid-cols-3">@foreach($suggestions as $suggestion) @include('suggestions.partials.card',['showVisibility'=>true]) @endforeach</div><div class="mt-6">{{ $suggestions->links() }}</div>@endif</div>
@endsection
