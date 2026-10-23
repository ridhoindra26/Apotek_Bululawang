@extends('layout.layout')
@section('title','Edit Saran')
@section('page_title', 'Edit Saran')
@section('content')
<div class="mx-auto max-w-3xl"><div class="mb-5"><a href="{{ route('suggestions.show',$suggestion) }}" class="text-sm font-semibold text-[#318f8c]"><i class="ri-arrow-left-line"></i> Kembali ke detail</a><h1 class="mt-2 text-xl font-black sm:text-2xl">Edit Saran</h1><p class="text-sm text-slate-500">Perubahan hanya tersedia sebelum keputusan diberikan.</p></div><form method="POST" action="{{ route('suggestions.update',$suggestion) }}" enctype="multipart/form-data" x-data="suggestionForm({ maxFiles: {{ config('suggestions.attachments.max_files') }}, existingCount: {{ $suggestion->attachments?->count() ?? 0 }} })" @submit="submitting = true" class="rounded-2xl border bg-white p-4 shadow-sm sm:p-7">@csrf @method('PUT') @include('suggestions.partials.form')</form></div>
@endsection
