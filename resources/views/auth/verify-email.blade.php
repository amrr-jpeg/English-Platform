@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Подтверждение email</h1>
        <p class="muted">
            Спасибо за регистрацию! Перед началом работы подтвердите адрес электронной почты,
            перейдя по ссылке из письма.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="toast toast--good">
                Новая ссылка для подтверждения уже отправлена на вашу почту.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="stack">
            @csrf
            <button class="btn" type="submit">Отправить письмо повторно</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="stack" style="margin-top:12px;">
            @csrf
            <button class="btn" type="submit">Выйти</button>
        </form>
    </div>

    <div class="card buddy buddy--blue buddy--big">
        <div class="buddy__face">✉️</div>
        <div class="buddy__name">Подтверди почту</div>
        <div class="buddy__mini">Это нужно для безопасности 🔐</div>
    </div>
</div>
@endsection