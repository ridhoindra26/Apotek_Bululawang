<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link rel="apple-touch-icon" sizes="76x76" href="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_128dp.png"> --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
    <title>@yield('title','Apotek Bululawang')</title>
    <!--     Fonts    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet" />
    <style>
        * {
            font-family: Inter, sans-serif;
            letter-spacing: 0em;
        }
    </style>
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

{{-- <body class="g-sidenav-show   bg-gray-100">
    <div class="max-height-100 bg-i position-absolute w-100"
        style="background-image: url('https://s.tmimgcdn.com/scr/1200x750/250600/background-wave-water-blue-vector-design-v10_250624-original.jpg'); width: 100%; min-height: 50vh; background-repeat: no-repeat; background-size: cover;">
    </div>
    @include('layout.side')
    <main class="main-content position-relative border-radius-lg">
        @include('layout.nav')
        @yield('content')

    </main>
    </div>

</body> --}}

    <body x-data="{ sidebarOpen:false }" class="min-h-screen bg-gradient-to-br from-white via-[#d8f3f2]/50 to-[#318f8c]/10">
        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-black/30 md:hidden" @click="sidebarOpen=false"></div>

        <!-- Put sidebar and content in a horizontal flex row -->
        <div class="flex min-h-screen">
            @include('layout.side')  {{-- sidebar is part of the flex row --}}

            <div class="flex-1 flex flex-col w-full">  {{-- ❌ no md:pl-64 here --}}
            @include('layout.nav')   {{-- navbar will span full width of this column --}}
            <main class="flex-1 p-4 sm:p-6 w-full">
                @yield('content')
            </main>
            <footer class="py-4 text-center text-xs text-slate-500">
                Made with <span class="text-[var(--primary-color)]">❤</span> by Indra <br>
                © {{ date('Y') }} PT. Bululawang Jaya Farma. All rights reserved.
            </footer>
            </div>
        </div>

        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </body>

</html>
