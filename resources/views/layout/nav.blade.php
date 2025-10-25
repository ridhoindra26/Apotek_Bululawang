<header class="h-16 bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-20 flex items-center">
  <div class="w-full h-full px-4 flex items-center gap-3">
    <button class=" md:hidden" @click="sidebarOpen = true">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="#318f8c" stroke-width="1.5" class="size-6">
        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
      </svg>

    </button>
    <div class="flex-1">
      <h1 class="text-lg font-semibold text-slate-800">@yield('page_title','Overview')</h1>
    </div>

    <div class="flex items-center gap-3">
      <form id="logout-form" method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button
          type="button"
          id="logout-button"
          class="px-3 py-2 !rounded-md bg-[#318f8c] text-white text-sm font-semibold hover:opacity-90"
        >
          Logout
        </button>
      </form>

      @auth
      <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-200">
        <span class="h-2 w-2 rounded-full" style="background-color: var(--primary-color)"></span>
        <span class="text-sm text-slate-700">{{ auth()->user()->username }}</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-[var(--primary-color)]/10 text-[var(--primary-color)]">
          {{ ucfirst(auth()->user()->role) }}
        </span>
      </div>
      @endauth
    </div>
  </div>
</header>
