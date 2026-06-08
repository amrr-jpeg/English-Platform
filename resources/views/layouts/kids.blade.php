<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'English Platform') }}</title>

    <link rel="stylesheet" href="{{ asset('css/kids.css') }}">
</head>

<body class="theme-skin-{{ auth()->check() ? (auth()->user()->skin ?? 'blue') : 'blue' }}">
    <div class="bg-layer" aria-hidden="true"></div>

    <div class="app">
        <div class="container">
            @include('layouts.navigation')

            <main class="page-content">
                @yield('content')
            </main>

            <footer class="footer">
                Сделано на Laravel • Дипломный проект • English Platform
            </footer>
        </div>
    </div>

    @if(session('achievement_unlocked'))
        <div class="achievementToast" role="status" aria-live="polite">
            <div class="achievementToast__icon">
                {{ session('achievement_unlocked.icon') }}
            </div>

            <div>
                <div class="achievementToast__title">
                    Достижение открыто: {{ session('achievement_unlocked.title') }}
                </div>

                <div class="achievementToast__text">
                    {{ session('achievement_unlocked.description') }}
                </div>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/kids-ui.js') }}"></script>
    <script src="{{ asset('js/sounds.js') }}"></script>
</body>
</html>
