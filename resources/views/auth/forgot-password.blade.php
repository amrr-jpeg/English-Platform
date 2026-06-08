@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Восстановление пароля</h1>
        <p class="muted">Введите email, и мы отправим ссылку для сброса пароля.</p>

        @if (session('status'))
            <div class="toast toast--good">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="stack">
            @csrf

            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn" type="submit">Отправить ссылку</button>

            <div class="muted small">
                <a class="link" href="{{ route('login') }}">Назад ко входу</a>
            </div>
        </form>
    </div>

    <div class="card buddy buddy--pink buddy--big">
        <div class="buddy__face">📩</div>
        <div class="buddy__name">Поможем восстановить доступ</div>
        <div class="buddy__mini">Проверь свою почту ✨</div>
    </div>
</div>
@endsection