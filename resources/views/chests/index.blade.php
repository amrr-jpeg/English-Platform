@extends('layouts.kids')

@section('content')
<div class="hero">
    <div>
        <h1 class="h1">Сундуки</h1>
        <p class="muted">Открывай ежедневный сундук и получай случайные награды.</p>
    </div>

    <div class="card">
        <div class="h2">🎁</div>
        <div class="muted">1 сундук в день</div>
    </div>
</div>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<div class="chestLayout">
    <div class="card chestCard {{ $openedToday ? 'chestCard--opened' : '' }}">
        <div class="chestIcon">{{ $openedToday ? '📦' : '🎁' }}</div>

        <h2 class="h2">
            {{ $openedToday ? 'Сундук уже открыт' : 'Ежедневный сундук' }}
        </h2>

        <p class="muted">
            Возможные награды: XP, монеты или смешанная награда.
        </p>

        <form method="POST" action="{{ route('chests.open') }}">
            @csrf

            <button class="btn" type="submit" {{ $openedToday ? 'disabled' : '' }}>
                {{ $openedToday ? 'Приходи завтра' : 'Открыть сундук' }}
            </button>
        </form>
    </div>

    <div class="card">
        <h2 class="h2">История наград</h2>

        <div class="stack">
            @forelse($history as $item)
                <div class="rewardHistoryItem">
                    <div>{{ $item->reward_label }}</div>
                    <span class="muted small">{{ $item->created_at->format('d.m.Y H:i') }}</span>
                </div>
            @empty
                <p class="muted">Ты ещё не открывал сундуки.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection