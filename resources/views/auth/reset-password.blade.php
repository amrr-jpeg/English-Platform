@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Новый пароль</h1>
        <p class="muted">Придумай новый надёжный пароль для аккаунта.</p>

        <form method="POST" action="{{ route('password.store') }}" class="stack" id="resetPasswordForm" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="label" for="password">Новый пароль</label>
                <input class="input" id="password" type="password" name="password" required autocomplete="new-password">
                <div class="muted small">
                    Минимум 8 символов, строчные и заглавные буквы, цифры и спецсимволы.
                </div>
                <div class="toast toast--bad" id="passwordError" style="display:none;"></div>
                @error('password')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="label" for="password_confirmation">Подтвердите пароль</label>
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                <div class="toast toast--bad" id="passwordConfirmationError" style="display:none;"></div>
                @error('password_confirmation')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn" type="submit">Сменить пароль</button>
        </form>
    </div>

    <div class="card buddy buddy--blue buddy--big">
        <div class="buddy__face">🔒</div>
        <div class="buddy__name">Безопасность аккаунта</div>
        <div class="buddy__mini">Сделай пароль сильным 💪</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resetPasswordForm');
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
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

        hideError(passwordError);
        hideError(passwordConfirmationError);

        const hasLower = /[a-zа-яё]/;
        const hasUpper = /[A-ZА-ЯЁ]/;
        const hasDigit = /\d/;
        const hasSymbol = /[^A-Za-zА-Яа-яЁёІіЇїЄєҐґ0-9]/;

        if (!password.value) {
            showError(passwordError, 'Введите новый пароль.');
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
            showError(passwordConfirmationError, 'Подтвердите пароль.');
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