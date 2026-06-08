@extends('layouts.kids')

@section('content')
<div class="hero">
    <div>
        <h1 class="h1">Английский В Игре 🎓</h1>
        <p class="muted">Учись английскому в игровом формате: уроки, задания, монеты и прокачка персонажа.</p>

        @auth
            <a class="btn" href="{{ route('dashboard') }}">Перейти в уроки</a>
        @else
            <div class="row">
                <a class="btn" href="{{ route('login') }}">Войти</a>
                <a class="btn btn--ghost" href="{{ route('register') }}">Регистрация</a>
            </div>
        @endauth
    </div>

    <div class="buddy buddy--blue">
        <div class="buddy__face">👋</div>
        <div class="buddy__name">Привет!</div>
        <div class="buddy__mini">Давай начнём 🙂</div>
    </div>
</div>
@endsection
