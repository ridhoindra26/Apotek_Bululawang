@extends('layout.layout')

@section('title', 'Ajukan Saran')
@section('page_title', 'Ajukan Saran')

@section('content')
<div class="mx-auto max-w-4xl px-1 pb-28 sm:px-0 sm:pb-6">
    <header class="mb-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
        <a href="{{ route('suggestions.mine') }}" class="inline-flex items-center gap-1 text-sm font-medium text-[#318f8c] hover:text-[#287673]">
            <i class="ri-arrow-left-line"></i> Kembali ke Saran Saya
        </a>
        <div class="mt-3 flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#318f8c]/10 text-[#318f8c]">
                <i class="ri-lightbulb-flash-line text-2xl"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Ajukan Saran Baru</h1>
                <p class="mt-1 text-sm leading-5 text-gray-500">Ceritakan kondisi yang ditemukan dan perbaikan yang Anda usulkan.</p>
            </div>
        </div>
    </header>

    <form
        method="POST"
        action="{{ route('suggestions.store') }}"
        enctype="multipart/form-data"
        x-data="suggestionForm({ maxFiles: {{ config('suggestions.attachments.max_files') }}, existingCount: {{ $suggestion->attachments?->count() ?? 0 }} })"
        @submit="submitting = true"
        class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6"
    >
        @csrf
        @include('suggestions.partials.form')
    </form>
</div>
@endsection
