<header class="h-16 bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-20 flex items-center">
  <div class="w-full h-full px-4 flex items-center gap-3">
    {{-- Mobile: sidebar button --}}
    <button class="md:hidden" @click="sidebarOpen = true" aria-label="Open sidebar">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="#318f8c" stroke-width="1.5" class="size-6">
        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
      </svg>
    </button>

    {{-- Page title --}}
    <div class="flex-1 min-w-0">
      <h3 class="mb-0 text-base sm:text-lg font-semibold text-slate-800 truncate">
        @yield('page_title','Overview')
      </h3>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
      @auth
      {{-- Desktop badge (kept) --}}
      {{-- <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-200">
        <span class="h-2 w-2 rounded-full" style="background-color: var(--primary-color)"></span>
        <span class="text-sm text-slate-700">{{ auth()->user()->username }}</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-[var(--primary-color)]/10 text-[var(--primary-color)]">
          {{ ucfirst(auth()->user()->role) }}
        </span>
      </div> --}}

      {{-- Profile dropdown (mobile + desktop) --}}
      <div class="relative" x-data="{ open:false }" @keydown.escape.window="open=false">
        <button
          type="button"
          @click="open=!open"
          class="inline-flex items-center gap-2 px-3 py-2 !rounded border border-slate-200 bg-white hover:bg-slate-50"
          aria-label="Open user menu"
        >
          <span class="inline-flex items-center justify-center text-slate-700 text-sm font-semibold">
            {{-- {{ strtoupper(substr(auth()->user()->username ?? auth()->user()->name ?? 'U', 0, 1)) }} --}}
            Profil
          </span>
          {{-- <span class="hidden md:inline text-sm font-medium text-slate-700 max-w-[160px] truncate">
            {{ auth()->user()->username }}
          </span> --}}
          <svg class="size-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
          </svg>
        </button>

        {{-- Backdrop on mobile to close --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-10 md:hidden" @click="open=false"></div>

        {{-- Menu --}}
        <div
          x-show="open"
          x-cloak
          x-transition.origin.top.right
          @click.outside="open=false"
          class="absolute right-0 mt-2 w-56 z-20 rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden"
        >
          <div class="px-4 py-3 border-b border-slate-100">
            <div class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->username }}</div>
          </div>

          <div class="py-1">
            <a href="{{ route('profile.index') }}"
               class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
              <span>Lihat Profil</span>
            </a>

            <a href="{{ route('profile.password.edit') }}"
               class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
              <span>Ubah Password</span>
            </a>
          </div>

          <div class="border-t border-slate-100 p-2">
            <form id="logout-form" method="POST" action="{{ route('auth.logout') }}">
              @csrf
              <button
                type="button"
                id="logout-button"
                class="w-full px-3 py-2 !rounded bg-[#318f8c] text-white text-sm font-semibold hover:opacity-90"
              >
                Logout
              </button>
            </form>
          </div>
        </div>
      </div>
      @endauth
    </div>
  </div>
</header>