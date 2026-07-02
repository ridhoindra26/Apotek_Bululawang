<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <title>@yield('title', 'Apotek Bululawang')</title>

    {{-- Critical CSS: must load before external assets --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            opacity: 1;
            transition: opacity .25s ease;
        }

        #page-loader.is-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .page-loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            font-family: Arial, sans-serif;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }

        .page-loader-spinner {
            width: 42px;
            height: 42px;
            border: 4px solid #d1d5db;
            border-top-color: #318f8c;
            border-radius: 9999px;
            animation: page-loader-spin .75s linear infinite;
        }

        @keyframes page-loader-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        media="print"
        onload="this.media='all'"
    >

    <noscript>
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        >
    </noscript>

    <style>
        * {
            font-family: Inter, sans-serif;
            letter-spacing: 0em;
        }
    </style>

    {{-- Remix Icons --}}
    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
        rel="stylesheet"
    />

    {{-- Argon CSS --}}
    <link
        id="pagestyle"
        href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}"
        rel="stylesheet"
    />

    {{-- Charts --}}
    <script defer src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Flash / Validation --}}
    <script>
        window.goodsReceiptFlash = {
            success: @json(session('success')),
            error: @json(session('error')),
            warning: @json(session('warning')),
            validationErrors: @json($errors->all()),
        };
    </script>
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-gradient-to-br from-white via-[#d8f3f2]/50 to-[#318f8c]/10"
>
    {{-- Must be first visible element inside body --}}
    <div id="page-loader" aria-live="polite" aria-label="Memuat halaman">
        <div class="page-loader-content">
            <div class="page-loader-spinner"></div>
            <p>Memuat halaman...</p>
        </div>
    </div>

    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-30 bg-black/30 md:hidden"
        @click="sidebarOpen = false"
    ></div>

    <div class="flex min-h-screen">
        @include('layout.side')

        <div class="flex w-full flex-1 flex-col">
            @include('layout.nav')

            <main class="w-full flex-1 p-4 sm:p-6">
                @yield('content')
            </main>

            <footer class="py-4 text-center text-xs text-slate-500">
                Made with <span class="text-[var(--primary-color)]">❤</span> by Indra
                <br>
                © {{ date('Y') }} PT. Bululawang Jaya Farma. All rights reserved.
            </footer>
        </div>
    </div>

    {{-- Alpine --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Loader fallback / page-ready listener --}}
    <script>
        function hidePageLoader() {
            const loader = document.getElementById('page-loader')

            if (!loader || loader.dataset.hidden === 'true') {
                return
            }

            loader.dataset.hidden = 'true'
            loader.classList.add('is-hidden')

            setTimeout(() => {
                loader.remove()
            }, 300)
        }

        window.addEventListener('load', () => {
            const isGoodsReceiptPage = document.querySelector('[data-goods-receipt-form]')

            if (!isGoodsReceiptPage) {
                setTimeout(hidePageLoader, 150)
            }
        })

        window.addEventListener('goods-receipt:page-ready', () => {
            setTimeout(hidePageLoader, 150)
        })

        {{-- Emergency fallback if a JS error prevents normal loader completion --}}
        setTimeout(() => {
            hidePageLoader()
        }, 10000)
    </script>
</body>

</html>