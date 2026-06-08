@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Админская статистика</h1>
        <p class="muted">Общая аналитика по платформе: пользователи, уроки, попытки, награды и активность.</p>
    </div>

    <a class="btn btn--ghost" href="{{ route('admin.index') }}">Админ-панель</a>
</div>

<div class="statsGrid">
    <div class="statCard"><div class="statCard__icon">👥</div><div class="statCard__value">{{ $usersCount }}</div><div class="statCard__label">пользователей</div></div>
    <div class="statCard"><div class="statCard__icon">📚</div><div class="statCard__value">{{ $lessonsCount }}</div><div class="statCard__label">уроков</div></div>
    <div class="statCard"><div class="statCard__icon">✅</div><div class="statCard__value">{{ $activeLessonsCount }}</div><div class="statCard__label">активных уроков</div></div>
    <div class="statCard"><div class="statCard__icon">🧠</div><div class="statCard__value">{{ $exercisesCount }}</div><div class="statCard__label">упражнений</div></div>
    <div class="statCard"><div class="statCard__icon">🎯</div><div class="statCard__value">{{ $accuracy }}%</div><div class="statCard__label">средняя точность</div></div>
    <div class="statCard"><div class="statCard__icon">🏁</div><div class="statCard__value">{{ $completedLessonsCount }}</div><div class="statCard__label">прохождений уроков</div></div>
    <div class="statCard"><div class="statCard__icon">✨</div><div class="statCard__value">{{ $totalXpIssued }}</div><div class="statCard__label">XP выдано</div></div>
    <div class="statCard"><div class="statCard__icon">🪙</div><div class="statCard__value">{{ $totalCoinsIssued }}</div><div class="statCard__label">монет выдано</div></div>
</div>

<div class="adminStatsGrid">
    <div class="card">
        <h2 class="h2">Топ пользователей по XP</h2>
        <div class="stack">
            @foreach($topUsers as $index => $user)
                <div class="leaderRow">
                    <span>#{{ $index + 1 }}</span>
                    <b>{{ $user->name }}</b>
                    <span>✨ {{ $user->xp }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2 class="h2">Популярные уроки</h2>
        <div class="stack">
            @foreach($popularLessons as $lesson)
                <div class="leaderRow">
                    <b>{{ $lesson->title }}</b>
                    <span>{{ $lesson->user_lessons_count }} прохождений</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
