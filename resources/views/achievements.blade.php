@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('dashboard') }}">← Назад</a>

<div class="hero">
    <div>
        <h1 class="h1">Достижения</h1>
        <p class="muted">Выполняй задания, проходи уроки и получай награды.</p>
    </div>

    <div class="card">
        <div class="h2">
            {{ count($unlockedIds) }} / {{ $achievements->count() }}
        </div>
        <div class="muted">открыто достижений</div>
    </div>
</div>

<div class="achievementsGrid">
    @foreach($achievements as $achievement)
        @php
            $unlocked = in_array($achievement->id, $unlockedIds);
        @endphp

        <div class="achievementCard {{ $unlocked ? 'achievementCard--unlocked' : 'achievementCard--locked' }}">
            <div class="achievementCard__icon">
                {{ $unlocked ? $achievement->icon : '🔒' }}
            </div>

            <div class="achievementCard__body">
                <h3>{{ $achievement->title }}</h3>
                <p>{{ $achievement->description }}</p>

                <div class="achievementReward">
                    <span>✨ +{{ $achievement->reward_xp }} XP</span>
                    <span>🪙 +{{ $achievement->reward_coins }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection