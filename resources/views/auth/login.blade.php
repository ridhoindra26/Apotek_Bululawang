@extends('layout.guest')

@section('title','Sign In')

@section('content')

@php
  $greeting_title = match(true) {
    now()->hour < 12 => 'Selamat Pagiii',
    now()->hour < 18 => 'Selamat Sianggg',
    default => 'Selamat Malammm',
  };
@endphp

<div class="w-full max-w-md mx-auto p-6">
  <div class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-slate-200 p-8 sm:p-10">
    <div class="mb-8 text-center">
      <div class="flex items-center justify-center gap-3 mb-2">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="h-15 w-15 rounded-xl">
      </div>
      <div class="text-center space-y-1">
        <p class="text-2xl font-bold bg-gradient-to-r from-[#318f8c] to-[#42c2be] bg-clip-text text-transparent">{{ $greeting_title }}</p>
        <p class="text-sm text-slate-500">{{ $greeting }}</p>
      </div>
    </div>

    @if ($errors->any())
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm p-3">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('auth.login') }}" class="space-y-6">
      @csrf

      <div>
        <label for="username" class="block text-sm font-medium text-slate-600">Username</label>
        <input
          id="username" name="username" type="text"
          value="{{ old('username') }}"
          class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]"
          placeholder="Siapa yang mau masuk nih??" required
        />
      </div>

      <div x-data="{ show: false }" class="relative">
      <label for="password" class="block text-sm font-medium text-slate-600">Password</label>

      <input
        id="password"
        name="password"
        :type="show ? 'text' : 'password'"
        class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]"
        placeholder="••••••••"
        required
      />

      <button
        type="button"
        @click="show = !show"
        class="absolute right-3 top-[38px] text-slate-500 hover:text-[var(--primary-color)] focus:outline-none"
        tabindex="-1"
      >
        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.364 5 12 5c4.636 0 8.577 2.51 9.964 6.678.07.21.07.434 0 .644C20.577 16.49 16.636 19 12 19c-4.636 0-8.577-2.51-9.964-6.678z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0113.5 13.5M6.343 6.343A9.954 9.954 0 0112 5c4.636 0 8.577 2.51 9.964 6.678a1.012 1.012 0 010 .644 9.96 9.96 0 01-4.232 5.099" />
        </svg>
      </button>
    </div>

      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="remember" class="rounded border-slate-300 text-[var(--primary-color)] focus:ring-[var(--primary-color)]">
          <span>Remember me</span>
        </label>
        {{-- <a href="#" class="text-sm text-[var(--primary-color)] hover:underline">Forgot password?</a> --}}
      </div>

      <button
        type="submit"
        class="w-full py-2.5 rounded-lg font-semibold text-white transition-colors"
        style="background-color: var(--primary-color);"
      >
        Sign In
      </button>
    </form>
  </div>

  <p class="mt-6 text-center text-xs text-slate-500">
    Made with <span class="text-[var(--primary-color)]">❤</span> by Indra <br>
    © {{ date('Y') }} PT. Bululawang Jaya Farma. All rights reserved.
  </p>
</div>
@endsection
