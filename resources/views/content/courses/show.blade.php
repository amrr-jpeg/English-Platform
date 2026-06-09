@extends('layouts.kids')

@section('content')
<div class="hero card">
    <div>
        <span class="badge">Курс от {{ $course->creator?->name }}</span>
        <h1>{{ $course->title }}</h1>
        <p class="muted">{{ $course->description }}</p>
    </div>
    <a class="btn btn--ghost" href="{{ route('content.managers.show', $course->creator) }}">Автор курса</a>
</div>

<div class="dashboardLessonGrid">
    @forelse($course->lessons as $lesson)
        <article class="card dashboardLessonCard {{ !$lesson->is_active ? 'dashboardLessonCard--locked' : '' }}">
            <div class="dashboardLessonCard__top">
                <span class="badge">#{{ $lesson->order }}</span>
                <span class="dashboardLessonCard__percent">{{ $lesson->level }}</span>
            </div>
            <h3 class="dashboardLessonCard__title">{{ $lesson->title }}</h3>
            <p class="dashboardLessonCard__desc">{{ $lesson->description }}</p>
            <div class="dashboardLessonCard__bottom">
                <span class="dashboardLessonCard__meta">{{ $lesson->exercises->count() }} заданий</span>
                @if($lesson->is_active)
                    <a class="btn dashboardLessonCard__button" href="{{ route('content.lessons.show', $lesson) }}">Начать</a>
                @else
                    <button class="btn btn--disabled" disabled>Закрыто</button>
                @endif
            </div>
        </article>
    @empty
        <div class="emptyState card"><h2>Уроки ещё не добавлены</h2></div>
    @endforelse
</div>
@endsection
