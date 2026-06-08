@extends('layouts.kids')

@section('content')
<div class="hero">
    <div>
        <h1 class="h1">Статистика</h1>
        <p class="muted">Здесь отображается настоящий прогресс: попытки, награды, ошибки, активность и рекомендации.</p>
    </div>

    <div class="card">
        <div class="h2">LVL {{ $user->level }}</div>
        <div class="muted">текущий уровень</div>
    </div>
</div>

<div class="statsGrid">
    <div class="statCard">
        <div class="statCard__icon">✨</div>
        <div class="statCard__value">{{ $user->xp }}</div>
        <div class="statCard__label">XP всего</div>
    </div>

    <div class="statCard">
        <div class="statCard__icon">📚</div>
        <div class="statCard__value">{{ $completedLessons }}</div>
        <div class="statCard__label">уроков пройдено</div>
    </div>

    <div class="statCard">
        <div class="statCard__icon">🧠</div>
        <div class="statCard__value">{{ $learnedWords }}</div>
        <div class="statCard__label">ответов изучено</div>
    </div>

    <div class="statCard">
        <div class="statCard__icon">🎯</div>
        <div class="statCard__value">{{ $accuracy }}%</div>
        <div class="statCard__label">точность ответов</div>
    </div>
</div>

<section class="card recommendationBox">
    <div class="builderSection__head">
        <div>
            <h2 class="h2">Рекомендации</h2>
            <p class="muted">Система подсказывает, что лучше сделать дальше.</p>
        </div>
    </div>

    <div class="recommendationGrid">
        @foreach($recommendations as $item)
            <article class="recommendationCard">
                <div class="recommendationCard__icon">{{ $item['icon'] }}</div>
                <h3>{{ $item['title'] }}</h3>
                <p>{{ $item['text'] }}</p>
                <a class="btn btn--ghost" href="{{ $item['route'] }}">{{ $item['button'] }}</a>
            </article>
        @endforeach
    </div>
</section>

<div class="card statsChartCard">
    <div class="builderSection__head">
        <div>
            <h2 class="h2">XP за 7 дней</h2>
            <p class="muted">График строится по журналу наград: уроки, игры, экзамены, сундуки и Travel English.</p>
        </div>
    </div>

    <div class="xpChart">
        @foreach($chart as $day)
            @php $height = max(8, intval(($day['xp'] / $maxXp) * 180)); @endphp
            <div class="xpChart__item">
                <div class="xpChart__barWrap">
                    <div class="xpChart__bar" style="height: {{ $height }}px;"><span>{{ $day['xp'] }}</span></div>
                </div>
                <div class="xpChart__label">{{ $day['label'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="adminStatsGrid">
    <section class="card">
        <h2 class="h2">Откуда получены награды</h2>
        <div class="stack">
            @forelse($rewardSources as $source)
                <div class="leaderRow">
                    <b>{{ match($source->source) {
                        'exercise' => 'Уроки',
                        'lesson_complete' => 'Завершение уроков',
                        'travel_exercise' => 'Travel-уроки',
                        'game' => 'Мини-игры',
                        'travel_game' => 'Travel-игры',
                        'exam' => 'Экзамены',
                        'chest' => 'Сундуки',
                        'travel_scenario' => 'Сценарий путешествия',
                        default => $source->source,
                    } }}</b>
                    <span>✨ {{ (int) $source->xp }} • 🪙 {{ (int) $source->coins }}</span>
                </div>
            @empty
                <p class="muted">Наград пока нет.</p>
            @endforelse
        </div>
    </section>

    <section class="card">
        <h2 class="h2">Последняя активность</h2>
        <div class="stack">
            @forelse($recentActivity as $activity)
                <div class="leaderRow">
                    <b>{{ $activity->description ?: $activity->source }}</b>
                    <span>{{ $activity->created_at->format('d.m H:i') }} • ✨ {{ $activity->xp }} • 🪙 {{ $activity->coins }}</span>
                </div>
            @empty
                <p class="muted">Активности пока нет.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="card">
    <h2 class="h2">Ответы</h2>
    <div class="progress">
        <div class="progress__bar" style="width: {{ $accuracy }}%"></div>
    </div>
    <p class="muted">Правильных ответов: {{ $correctAnswers }} из {{ $totalAnswers }} попыток.</p>
</div>

<section class="card">
    <h2 class="h2">Что стоит повторить</h2>
    <div class="stack">
        @forelse($hardExercises as $row)
            @if($row->exercise)
                <div class="leaderRow">
                    <b>{{ $row->exercise->question }}</b>
                    <span>{{ $row->exercise->lesson->title ?? 'Урок' }} • ошибок: {{ (int) $row->wrong_count }}</span>
                </div>
            @endif
        @empty
            @forelse($weakExercises as $attempt)
                @if($attempt->exercise)
                    <div class="leaderRow">
                        <b>{{ $attempt->exercise->question }}</b>
                        <span>{{ $attempt->exercise->lesson->title ?? 'Урок' }}</span>
                    </div>
                @endif
            @empty
                <p class="muted">Пока нет ошибок для повторения.</p>
            @endforelse
        @endforelse
    </div>
</section>
@endsection
