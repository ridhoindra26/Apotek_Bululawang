<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <title>@yield('title', 'Login')</title>

    <style>
        :root {
            --primary-color: #318f8c;
        }

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
            color: #374151;
            font-family: Arial, sans-serif;
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        window.goodsReceiptFlash = {
            success: @json(session('success')),
            error: @json(session('error')),
            warning: @json(session('warning')),
            validationErrors: @json($errors->all()),
        };
    </script>
</head>

<body class="min-h-full bg-gradient-to-br from-white via-[#d8f3f2] to-[#318f8c]/20 bg-fixed">

    {{-- Must be the first visible element inside body --}}
    <div id="page-loader" aria-live="polite" aria-label="Memuat halaman">
        <div class="page-loader-content">
            <div class="page-loader-spinner"></div>
            <p>Memuat halaman...</p>
        </div>
    </div>

    <main class="min-h-screen px-4 py-8">
        @yield('content')
    </main>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        setTimeout(() => {
            hidePageLoader()
        }, 10000)
    </script>
</body>
</html>