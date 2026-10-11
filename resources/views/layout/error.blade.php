<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
    <title>@yield('title', 'Terjadi Kesalahan - Apotek Bululawang')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet" />

    <style>
        * {
            font-family: Inter, sans-serif;
            letter-spacing: 0em;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-[#d8f3f2]/50 to-[#318f8c]/10">
    <main class="min-h-screen flex items-center justify-center p-4">
        @yield('content')
    </main>

    <footer class="fixed bottom-4 left-0 right-0 text-center text-xs text-slate-500">
        © {{ date('Y') }} PT. Bululawang Jaya Farma.
    </footer>
</body>

</html>