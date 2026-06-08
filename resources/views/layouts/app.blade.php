<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'English Platform') }}</title>

    <link rel="stylesheet" href="{{ asset('css/kids.css') }}">
</head>
<body class="theme-skin-{{ auth()->check() ? (auth()->user()->skin ?? 'blue') : 'blue' }}">
    <div class="bg-layer" aria-hidden="true"></div>

    <div class="app">
        <div class="container">
            @include('layouts.navigation')

            @hasSection('header')
                <header class="page-header card">
                    <div class="page-header__inner">
                        @yield('header')
                    </div>
                </header>
            @endif

            <main class="page-content">
                @isset($slot)
                    {{ $slot }}
                @endisset

                @yield('content')
            </main>

            <footer class="footer">
                Сделано на Laravel • Дипломный проект • English Platform
            </footer>
        </div>
    </div>

    <script src="{{ asset('js/kids-ui.js') }}"></script>
    <script src="{{ asset('js/sounds.js') }}"></script>
</body>
</html>
