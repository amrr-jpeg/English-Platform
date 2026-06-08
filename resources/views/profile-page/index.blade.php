@extends('layouts.kids')

@section('content')
<div class="profilePage">
    <section class="profileHero card {{ $user->profile_background ?? '' }} {{ $user->profile_frame ?? '' }}">
        <div class="profileHero__avatar">
            @include('partials.player-avatar', [
                'user' => $user,
                'avatarClass' => 'profileAvatar',
                'faceClass' => 'shopAvatar__face',
            ])
        </div>

        <div class="profileHero__info">
            <span class="badge">👤 Профиль ученика</span>
            <h1 class="h1">{{ $user->name }}</h1>
            <p class="muted">{{ $user->email }}</p>

            <div class="profileStatsLine">
                <span>LVL {{ $user->level }}</span>
                <span>✨ {{ $user->xp }} XP</span>
                <span>🪙 {{ $user->coins }}</span>
                <span>🔥 {{ $user->streak }}</span>
            </div>
        </div>
    </section>

    <div class="statsGrid profileStatsGrid">
        <div class="statCard">
            <div class="statCard__icon">📚</div>
            <div class="statCard__value">{{ $completedLessons }}</div>
            <div class="statCard__label">уроков пройдено</div>
        </div>

        <div class="statCard">
            <div class="statCard__icon">🎯</div>
            <div class="statCard__value">{{ $accuracy }}%</div>
            <div class="statCard__label">точность</div>
        </div>

        <div class="statCard">
            <div class="statCard__icon">✅</div>
            <div class="statCard__value">{{ $correctAnswers }}</div>
            <div class="statCard__label">правильных ответов</div>
        </div>

        <div class="statCard">
            <div class="statCard__icon">🧩</div>
            <div class="statCard__value">{{ $totalAnswers }}</div>
            <div class="statCard__label">ответов всего</div>
        </div>
    </div>

    <div class="profileGrid">
        <section class="card">
            <div class="builderSection__head">
                <div>
                    <span class="badge">🏅 Награды</span>
                    <h2 class="h2">Последние достижения</h2>
                </div>
            </div>

            <div class="profileBadges">
                @forelse($achievements as $achievement)
                    <div class="profileBadge">
                        <div class="profileBadge__icon">{{ $achievement->icon }}</div>
                        <b>{{ $achievement->title }}</b>
                    </div>
                @empty
                    <p class="muted">Достижений пока нет.</p>
                @endforelse
            </div>
        </section>

        <section class="card">
            <div class="builderSection__head">
                <div>
                    <span class="badge">🎁 Сундуки</span>
                    <h2 class="h2">Последние сундуки</h2>
                </div>
            </div>

            <div class="stack">
                @forelse($lastChests as $chest)
                    <div class="rewardHistoryItem">
                        <div>{{ $chest->reward_label }}</div>
                        <span class="muted small">{{ $chest->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                @empty
                    <p class="muted">Сундуки пока не открывались.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
