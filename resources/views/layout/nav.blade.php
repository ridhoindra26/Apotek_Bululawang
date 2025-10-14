<nav class="opacity-100">
    <div class="flex flex-wrap justify-between items-center mx-auto p-4">
        <!-- Breadcrumb -->
        <nav class="flex px-4 py-3 text-gray-700 border border-gray-200 rounded-lg bg-gray-50"
            aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="#"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <i class="ri-home-4-fill text-dark text-sm opacity-10"></i>
                        <span class="ml-2">Dashboard</span>
                    </a>
                </li>
            </ol>
        </nav>
        <!-- Profil and sign out -->
        <div class="flex items-center space-x-3 rtl:space-x-reverse">
            <i class="ri-user-fill text-dark text-sm opacity-10"></i>
            <a href="tel:5541251234"
                class="text-sm  text-gray-700 dark:text-white hover:underline">{{ auth()->user()->name }}</a>
            <a href="tel:5541251234" class="text-sm  text-black-500">|</a>

            <form action="{{ route('auth.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="text-sm self-start text-black dark:text-black-500 hover:underline pb-1">Keluar</button>
            </form>
        </div>
    </div>
</nav>
