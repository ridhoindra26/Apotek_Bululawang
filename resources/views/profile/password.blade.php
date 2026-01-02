@extends('layout.layout')

@section('title', 'Ubah Password')
@section('page_title', 'Ubah Password')

@section('content')
<style>
    [x-cloak]{ display:none !important; }
</style>

<div class="max-w-xl space-y-4">
    {{-- Flash success --}}
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Flash errors --}}
    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
            <div class="font-semibold mb-1">Gagal menyimpan:</div>
            <ul class="list-disc ml-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {{-- Top bar: Back icon + title --}}
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center justify-center !h-9 !w-12 !rounded-full bg-[#318f8c] hover:bg-slate-50"
               aria-label="Back">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="white" class="size-5 text-slate-700">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>

            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-800">Ubah Password</div>
                <div class="text-xs text-slate-500">Gunakan password yang kuat dan jangan bagikan ke orang lain.</div>
            </div>
        </div>

        {{-- ONLY ADDED: page-level alpine state + helper functions (no layout changes) --}}
        <div class="p-4 sm:p-6" x-data="pwUx()">
            {{-- Tips --}}
            <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <div class="font-semibold text-slate-800">Saran password</div>
                <ul class="mt-1 list-disc ml-5 space-y-1 text-xs text-slate-600">
                    <li>Minimal 8 karakter</li>
                    <li>Kombinasi huruf dan angka</li>
                    <li>Hindari tanggal lahir / nama apotek / pola mudah ditebak</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Password Lama --}}
                <div x-data="{ show:false, caps:false }">
                    <label class="block text-sm font-semibold text-slate-800 mb-1">Password Lama</label>

                    <div class="relative">
                        <input :type="show ? 'text' : 'password'"
                               name="current_password"
                               autocomplete="current-password"
                               class="w-full rounded-xl border @error('current_password') border-rose-300 @else border-slate-200 @enderror bg-white px-4 py-3 pr-12 text-sm text-slate-800
                                      focus:border-slate-300 focus:ring-4 focus:ring-slate-100"
                               placeholder="Masukkan password lama"
                               @keydown="caps = (typeof $event.getModifierState === 'function') ? $event.getModifierState('CapsLock') : false"
                               required>

                        <button type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-10 w-10 rounded-lg bg-white"
                                @click="show = !show"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                                :title="show ? 'Hide' : 'Show'">
                            <svg x-show="!show" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!w-6 !h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.364 5 12 5c4.636 0 8.577 2.51 9.964 6.678.07.21.07.434 0 .644C20.577 16.49 16.636 19 12 19c-4.636 0-8.577-2.51-9.964-6.678z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>

                            <svg x-show="show" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!w-6 !h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0113.5 13.5M6.343 6.343A9.954 9.954 0 0112 5c4.636 0 8.577 2.51 9.964 6.678a1.012 1.012 0 010 .644 9.96 9.96 0 01-4.232 5.099" />
                            </svg>
                        </button>
                    </div>

                    @error('current_password')
                        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                    @enderror

                    <p x-show="caps" style="display:none" class="mt-2 text-xs text-amber-700">Caps Lock aktif.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Password Baru --}}
                    <div x-data="{ show:false }">
                        <label class="block text-sm font-semibold text-slate-800 mb-1">Password Baru</label>

                        <div class="relative">
                            {{-- ONLY ADDED: bind value + call strength --}}
                            <input :type="show ? 'text' : 'password'"
                                   name="password"
                                   autocomplete="new-password"
                                   x-model="newPassword"
                                   @input="updateStrength(newPassword); updateMatch()"
                                   class="w-full rounded-xl border @error('password') border-rose-300 @else border-slate-200 @enderror bg-white px-4 py-3 pr-12 text-sm text-slate-800
                                          focus:border-slate-300 focus:ring-4 focus:ring-slate-100"
                                   placeholder="Contoh: Apotek2026"
                                   required>

                            <button type="button"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-10 w-10 rounded-lg bg-white"
                                    @click="show = !show"
                                    :aria-label="show ? 'Hide password' : 'Show password'"
                                    :title="show ? 'Hide' : 'Show'">
                                <svg x-show="!show" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!w-6 !h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.364 5 12 5c4.636 0 8.577 2.51 9.964 6.678.07.21.07.434 0 .644C20.577 16.49 16.636 19 12 19c-4.636 0-8.577-2.51-9.964-6.678z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>

                                <svg x-show="show" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!w-6 !h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0113.5 13.5M6.343 6.343A9.954 9.954 0 0112 5c4.636 0 8.577 2.51 9.964 6.678a1.012 1.012 0 010 .644 9.96 9.96 0 01-4.232 5.099" />
                                </svg>
                            </button>
                        </div>

                        {{-- <p class="mt-2 text-xs text-slate-500">Minimal 8 karakter, kombinasi huruf & angka.</p> --}}

                        {{-- ONLY ADDED: strength indicator --}}
                        <div class="mt-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-slate-500">Kekuatan</p>
                                <p class="text-xs font-semibold" :class="strengthTextClass" x-text="strengthLabel"></p>
                            </div>
                            <div class="mt-1 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-2 rounded-full transition-all"
                                     :class="strengthBarClass"
                                     :style="`width:${strengthPct}%`"></div>
                            </div>
                        </div>

                        @error('password')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password Baru --}}
                    <div x-data="{ show:false }">
                        <label class="block text-sm font-semibold text-slate-800 mb-1">Konfirmasi Password Baru</label>

                        <div class="relative">
                            {{-- ONLY ADDED: bind value + match update --}}
                            <input :type="show ? 'text' : 'password'"
                                   name="password_confirmation"
                                   autocomplete="new-password"
                                   x-model="confirmPassword"
                                   @input="updateMatch()"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-800
                                          focus:border-slate-300 focus:ring-4 focus:ring-slate-100"
                                   placeholder="Ulangi password baru"
                                   required>

                            <button type="button"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-10 w-10 bg-white"
                                    @click="show = !show"
                                    :aria-label="show ? 'Hide password' : 'Show password'"
                                    :title="show ? 'Hide' : 'Show'">
                                <svg x-show="!show" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!w-6 !h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.364 5 12 5c4.636 0 8.577 2.51 9.964 6.678.07.21.07.434 0 .644C20.577 16.49 16.636 19 12 19c-4.636 0-8.577-2.51-9.964-6.678z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>

                                <svg x-show="show" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!w-6 !h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0113.5 13.5M6.343 6.343A9.954 9.954 0 0112 5c4.636 0 8.577 2.51 9.964 6.678a1.012 1.012 0 010 .644 9.96 9.96 0 01-4.232 5.099" />
                                </svg>
                            </button>
                        </div>

                        <p class="mt-2 text-xs text-slate-500">Pastikan sama persis dengan password baru.</p>

                        {{-- ONLY ADDED: match indicator --}}
                        <p class="mt-1 text-xs"
                           :class="matchOk ? 'text-emerald-700' : 'text-rose-600'"
                           x-show="confirmPassword.length > 0"
                           style="display:none"
                           x-text="matchOk ? 'Password cocok.' : 'Password belum cocok.'"></p>
                    </div>
                </div>

                <div class="pt-1 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-[#318f8c] px-5 py-3 text-sm font-semibold text-white hover:opacity-90">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ONLY ADDED: tiny Alpine helper (strength + match), no markup/layout changes beyond bindings above --}}
<script>
function pwUx() {
    return {
        newPassword: '',
        confirmPassword: '',

        strengthPct: 10,
        strengthLabel: 'KOSONG NIH',
        strengthTextClass: 'text-rose-600',
        strengthBarClass: 'bg-rose-500',

        matchOk: false,

        updateStrength(val) {
            let score = 0;
            if (!val) score = 0;
            else {
                if (val.length >= 8) score++;
                if (val.length >= 12) score++;
                if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
                if (/\d/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
            }

            if (score <= 1) {
                this.strengthPct = 25;
                this.strengthLabel = 'HUUU JELEK';
                this.strengthTextClass = 'text-rose-600';
                this.strengthBarClass = 'bg-rose-500';
            } else if (score === 2) {
                this.strengthPct = 45;
                this.strengthLabel = 'OK LAH';
                this.strengthTextClass = 'text-amber-700';
                this.strengthBarClass = 'bg-amber-500';
            } else if (score === 3) {
                this.strengthPct = 65;
                this.strengthLabel = 'BAGUSSS';
                this.strengthTextClass = 'text-slate-700';
                this.strengthBarClass = 'bg-slate-600';
            } else {
                this.strengthPct = 100;
                this.strengthLabel = 'ANJAYYY KEREEN';
                this.strengthTextClass = 'text-emerald-700';
                this.strengthBarClass = 'bg-emerald-600';
            }
        },

        updateMatch() {
            this.matchOk = (this.confirmPassword === this.newPassword);
        }
    }
}
</script>
@endsection
