@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Вход</h1>
        <p class="muted">Войди, чтобы продолжить уроки 🙂</p>

        @if (session('status'))
            <div class="toast toast--good">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="stack" id="loginForm" novalidate>
            @csrf

            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                <div class="toast toast--bad" id="emailError" style="display:none;"></div>
                @error('email')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="label" for="password">Пароль</label>
                <input class="input" id="password" type="password" name="password" required autocomplete="current-password">
                <div class="toast toast--bad" id="passwordError" style="display:none;"></div>
                @error('password')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <label class="check">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Запомнить меня</span>
            </label>

            <button class="btn" type="submit">Войти</button>

            <div class="muted small">
                <a class="link" href="{{ route('password.request') }}">Забыли пароль?</a>
            </div>

            <div class="muted small">
                Нет аккаунта? <a class="link" href="{{ route('register') }}">Зарегистрироваться</a>
            </div>
        </form>
    </div>

    <div class="card buddy buddy--blue buddy--big">
        <div class="buddy__face">🔑</div>
        <div class="buddy__name">Добро пожаловать!</div>
        <div class="buddy__mini">Сейчас будем учиться 🎮</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    function showError(element, message) {
        element.textContent = message;
        element.style.display = 'block';
    }

    function hideError(element) {
        element.textContent = '';
        element.style.display = 'none';
    }

    form.addEventListener('submit', function (e) {
        let valid = true;

        hideError(emailError);
        hideError(passwordError);

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email.value.trim()) {
            showError(emailError, 'Введите email.');
            valid = false;
        } else if (!emailRegex.test(email.value.trim())) {
            showError(emailError, 'Введите корректный email.');
            valid = false;
        }

        if (!password.value) {
            showError(passwordError, 'Введите пароль.');
            valid = false;
        } else if (password.value.length < 8) {
            showError(passwordError, 'Пароль должен содержать минимум 8 символов.');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection