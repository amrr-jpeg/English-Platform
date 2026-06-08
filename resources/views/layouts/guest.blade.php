<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'English Platform') }}</title>

    <link rel="stylesheet" href="{{ asset('css/kids.css') }}">
</head>
<body class="theme-skin-blue">
    <div class="bg-layer" aria-hidden="true"></div>

    <div class="container">
        <main class="authWrap" style="min-height: calc(100vh - 80px);">
            <div class="card authCard">
                <div class="row" style="margin-bottom: 18px;">
                    <a class="brand" href="/">
                        <span class="brand__logo">🎓</span>
                        <span class="brand__text">
                            <span class="brand__title">English Platform</span>
                            <span class="brand__subtitle">учим английский играя</span>
                        </span>
                    </a>
                </div>

                {{ $slot }}
            </div>

            <div class="buddy buddy--blue buddy--big">
                <div class="buddy__face">🌟</div>
                <div class="buddy__name">Добро пожаловать!</div>
                <div class="buddy__mini">Учись, играй и собирай награды</div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/kids-ui.js') }}"></script>
</body>
</html>
