@extends('layouts.kids')

@section('content')
<div class="travelPage">
<div class="travelHero card">
    <div>
        <div class="badge">🌍 Отдельный курс</div>
        <h1 class="h1">Английский для путешествий</h1>
        <p class="muted">Travel English связан с общим прогрессом: за уроки, игры и сценарий начисляются XP и монеты.</p>
    </div>

    <div class="row">
        <a class="btn" href="{{ route('travel.games') }}">🎮 Игры по путешествиям</a>
        <a class="btn btn--ghost" href="{{ route('travel.scenario') }}">✈️ Симулятор путешествия</a>
    </div>
</div>

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<div class="travelGrid">
    @foreach($lessons as $lesson)
        @php $isUnlocked = in_array($lesson->id, $unlockedLessonIds ?? [], true); @endphp

        <div class="card travelLessonCard {{ !$isUnlocked ? 'lessonCard--locked' : '' }}">
            <div class="badge">{{ $lesson->level }}</div>
            <h2>{{ $lesson->title }}</h2>
            <p class="muted">{{ $lesson->description }}</p>

            <div class="lessonMeta">
                <span class="pill">🧠 {{ $lesson->exercises_count }} заданий</span>
                @if(in_array($lesson->id, $completedIds))
                    <span class="pill">✅ пройден</span>
                @elseif($isUnlocked)
                    <span class="pill">▶ доступен</span>
                @else
                    <span class="pill">🔒 закрыт</span>
                @endif
            </div>

            @if($isUnlocked)
                <a class="btn" href="{{ route('lessons.show', $lesson) }}">Начать урок</a>
            @else
                <button class="btn btn--disabled" disabled>Сначала пройди предыдущий</button>
            @endif
        </div>
    @endforeach
</div>
</div>
@endsection
