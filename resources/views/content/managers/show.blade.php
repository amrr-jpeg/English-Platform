@extends('layouts.kids')

@section('content')
<div class="hero card">
    <div>
        <span class="badge">Контент-менеджер</span>
        <h1>{{ $manager->name }}</h1>
        <p class="muted">Курсы автора и обновления. Подписчиков: {{ $manager->content_manager_followers_count }}</p>
    </div>
    <div class="row">
        @if($isSubscribed)
            <form method="POST" action="{{ route('content.managers.unsubscribe', $manager) }}">@csrf @method('DELETE')<button class="btn btn--ghost">Отписаться</button></form>
        @else
            <form method="POST" action="{{ route('content.managers.subscribe', $manager) }}">@csrf <button class="btn">Подписаться</button></form>
        @endif
    </div>
</div>

<div class="grid">
    @forelse($courses as $course)
        <article class="card lessonCard">
            <span class="badge">{{ $course->level }}</span>
            <h2>{{ $course->title }}</h2>
            <p class="muted">{{ $course->description }}</p>
            <p class="muted small">Уроков: {{ $course->lessons_count }}</p>
            <a class="btn" href="{{ route('content.courses.show', $course) }}">Проходить курс</a>
        </article>
    @empty
        <div class="emptyState card"><h2>У этого автора пока нет опубликованных курсов</h2></div>
    @endforelse
</div>
@endsection
