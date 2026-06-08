@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Регистрация</h1>
        <p class="muted">Создай аккаунт — и начнём учиться!</p>

        @if (session('success'))
            <div class="toast toast--good">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="stack" id="registerForm" novalidate>
            @csrf

            <div>
                <label class="label" for="name">Имя</label>
                <input class="input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                <div class="toast toast--bad" id="nameError" style="display:none;"></div>
                @error('name')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                <div class="toast toast--bad" id="emailError" style="display:none;"></div>
                @error('email')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="label" for="password">Пароль</label>
                <input class="input" id="password" type="password" name="password" required autocomplete="new-password">
                <div class="muted small">
                    Минимум 8 символов, должны быть строчные и заглавные буквы, цифры и спецсимволы.
                </div>
                <div class="toast toast--bad" id="passwordError" style="display:none;"></div>
                @error('password')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="label" for="password_confirmation">Повтори пароль</label>
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                <div class="toast toast--bad" id="passwordConfirmationError" style="display:none;"></div>
            </div>

            <button class="btn" type="submit">Создать аккаунт</button>

            <div class="muted small">
                Уже есть аккаунт? <a class="link" href="{{ route('login') }}">Войти</a>
            </div>
        </form>
    </div>

    <div class="card buddy buddy--pink buddy--big">
        <div class="buddy__face">🧸</div>
        <div class="buddy__name">Новый игрок!</div>
        <div class="buddy__mini">Уроки ждут тебя ✨</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');

    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const passwordConfirmationError = document.getElementById('passwordConfirmationError');

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

        hideError(nameError);
        hideError(emailError);
        hideError(passwordError);
        hideError(passwordConfirmationError);

        const nameRegex = /^[A-Za-zА-Яа-яЁёІіЇїЄєҐґ\s-]{2,}$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const hasLower = /[a-zа-яё]/;
        const hasUpper = /[A-ZА-ЯЁ]/;
        const hasDigit = /\d/;
        const hasSymbol = /[^A-Za-zА-Яа-яЁёІіЇїЄєҐґ0-9]/;

        if (!name.value.trim()) {
            showError(nameError, 'Введите имя.');
            valid = false;
        } else if (!nameRegex.test(name.value.trim())) {
            showError(nameError, 'Имя должно содержать минимум 2 символа и состоять из букв.');
            valid = false;
        }

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
        } else if (!hasLower.test(password.value)) {
            showError(passwordError, 'Пароль должен содержать строчные буквы.');
            valid = false;
        } else if (!hasUpper.test(password.value)) {
            showError(passwordError, 'Пароль должен содержать заглавные буквы.');
            valid = false;
        } else if (!hasDigit.test(password.value)) {
            showError(passwordError, 'Пароль должен содержать цифры.');
            valid = false;
        } else if (!hasSymbol.test(password.value)) {
            showError(passwordError, 'Пароль должен содержать спецсимволы.');
            valid = false;
        }

        if (!passwordConfirmation.value) {
            showError(passwordConfirmationError, 'Повторите пароль.');
            valid = false;
        } else if (password.value !== passwordConfirmation.value) {
            showError(passwordConfirmationError, 'Пароли не совпадают.');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>
@endsection