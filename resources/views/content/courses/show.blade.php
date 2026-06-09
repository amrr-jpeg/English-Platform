@extends('layouts.kids')

@section('content')
<div class="hero card">
    <div>
        <span class="badge">Курс от {{ $course->creator?->name ?? 'Контент-менеджер' }}</span>
        <h1>{{ $course->title }}</h1>
        <p class="muted">{{ $course->description }}</p>
    </div>

    @if($course->creator)
        <a class="btn btn--ghost" href="{{ route('content.managers.show', $course->creator) }}">
            Автор курса
        </a>
    @endif
</div>

<div class="dashboardLessonGrid">
    @forelse($course->lessons as $lesson)
        <div class="card">
            <span class="badge">Урок #{{ $lesson->order }}</span>

            <h2>{{ $lesson->title }}</h2>

            <p class="muted">{{ $lesson->description }}</p>

            <a href="{{ route('content.lessons.show', $lesson) }}" class="btn">
                Открыть урок
            </a>
        </div>
    @empty
        <div class="card">
            <h2>Уроков пока нет</h2>
            <p class="muted">Контент-менеджер ещё не добавил уроки.</p>
        </div>
    @endforelse
</div>
@endsection