<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
  <title>@yield('title','Login')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    :root {
      --primary-color: #318f8c;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-white via-[#d8f3f2] to-[#318f8c]/20 flex items-center justify-center">
  @yield('content')
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
