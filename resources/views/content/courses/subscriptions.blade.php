@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Курсы по моим подпискам</h1>
        <p class="muted">Здесь появляются курсы контент-менеджеров, на которых ты подписан.</p>
    </div>
    <a class="btn btn--ghost" href="{{ route('content.managers.index') }}">Найти авторов</a>
</div>

<div class="grid">
    @forelse($courses as $course)
        <article class="card lessonCard">
            <span class="badge">{{ $course->creator?->name }}</span>
            <h2>{{ $course->title }}</h2>
            <p class="muted">{{ $course->description }}</p>
            <p class="muted small">Уроков: {{ $course->lessons_count }} • {{ $course->level }}</p>
            <a class="btn" href="{{ route('content.courses.show', $course) }}">Открыть курс</a>
        </article>
    @empty
        <div class="emptyState card">
            <h2>Пока нет курсов</h2>
            <p class="muted">Подпишись на контент-менеджера, чтобы видеть его курсы.</p>
        </div>
    @endforelse
</div>
@endsection
