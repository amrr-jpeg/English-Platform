@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('dashboard') }}">← Назад</a>

<h1 class="h1">Твой персонаж</h1>
<p class="muted">Покупай стили один раз, а потом свободно переключай их.</p>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

@if(session('info'))
    <div class="toast">{{ session('info') }}</div>
@endif

<div class="characterGrid">
    @include('partials.player-avatar', [
        'user' => $user,
        'avatarClass' => 'buddy buddy--big characterAvatar',
        'faceClass' => 'buddy__face',
        'showCharacterInfo' => true,
    ])

    <div class="card">
        <h2 class="h2">Стили персонажа</h2>

        <div class="skins">
            @foreach($skins as $s)
                @php
                    $isOwned = in_array($s['key'], $ownedSkins);
                    $isSelected = ($user->skin ?? 'blue') === $s['key'];
                @endphp

                <form method="POST" action="{{ route('character.buySkin') }}"
                      class="skinCard {{ $isSelected ? 'skinCard--selected' : '' }}">
                    @csrf

                    <input type="hidden" name="skin" value="{{ $s['key'] }}">

                    <div class="skinPreview skinPreview--{{ $s['key'] }}">
                        <div class="skinPreviewFigure skinPreviewFigure--{{ $s['key'] }}">
                            <span class="skinPreviewFigure__head"></span>
                            <span class="skinPreviewFigure__body"></span>
                        </div>
                    </div>

                    <div class="skinInfo">
                        <div class="skinName">{{ $s['name'] }}</div>

                        <div class="muted small">
                            @if($isSelected)
                                ✅ Сейчас выбран
                            @elseif($isOwned)
                                🔓 Уже куплен
                            @else
                                Цена: {{ $s['price'] }} 🪙
                            @endif
                        </div>
                    </div>

                    @if($isSelected)
                        <button class="btn btn--disabled" type="button" disabled>
                            Выбран
                        </button>
                    @elseif($isOwned)
                        <button class="btn" type="submit">
                            Выбрать
                        </button>
                    @else
                        <button class="btn" type="submit">
                            Купить за {{ $s['price'] }} 🪙
                        </button>
                    @endif
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection
