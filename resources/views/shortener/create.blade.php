@extends('layout.layout')

@section('title', 'Buat Short Link')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Buat Short Link</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Buat short URL publik beserta QR code untuk kebutuhan website, promo, dan campaign.
                    </p>
                </div>

                <a
                    href="{{ route('shortener.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Kembali ke Daftar
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <div class="font-semibold">Terjadi kesalahan:</div>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('shortener.store') }}">
            @csrf
            @include('shortener.partials.form')
        </form>
    </div>
</div>
@endsection