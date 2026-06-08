@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Двухфакторная аутентификация</h1>
        <p class="muted">Введите 6-значный код, который мы отправили на вашу почту.</p>

        @if (session('status'))
            <div class="toast toast--good">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.verify') }}" class="stack">
            @csrf

            <div>
                <label class="label" for="code">Код подтверждения</label>
                <input class="input" id="code" type="text" name="code" maxlength="6" required autofocus autocomplete="one-time-code">
                @error('code')
                    <div class="toast toast--bad">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn" type="submit">Подтвердить вход</button>
        </form>

        <form method="POST" action="{{ route('2fa.resend') }}" class="stack" style="margin-top: 12px;">
            @csrf
            <button class="btn" type="submit">Отправить код повторно</button>
        </form>

        <div class="muted small">
            <a class="link" href="{{ route('login') }}">Вернуться ко входу</a>
        </div>
    </div>

    <div class="card buddy buddy--pink buddy--big">
        <div class="buddy__face">📨</div>
        <div class="buddy__name">Почти готово!</div>
        <div class="buddy__mini">Подтверди вход кодом ✨</div>
    </div>
</div>
@endsection