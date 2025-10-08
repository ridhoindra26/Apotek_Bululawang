<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_128dp.png">
    <link rel="icon" type="image/png" href="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_128dp.png">
    <title>
        TES
    </title>
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
</head>

<body class="g-sidenav-show   bg-gray-100">
    <div class="max-height-100 bg-i position-absolute w-100"
        style="background-image: url('https://s.tmimgcdn.com/scr/1200x750/250600/background-wave-water-blue-vector-design-v10_250624-original.jpg'); width: 100%; min-height: 50vh; background-repeat: no-repeat; background-size: cover;">
    </div>
    @include('layout.side')
    <main class="main-content position-relative border-radius-lg">
        @include('layout.nav')
        @yield('content')

    </main>
    </div>

</body>

</html>
