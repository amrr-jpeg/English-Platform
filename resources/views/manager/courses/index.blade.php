@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Мои курсы</h1>
        <p class="muted">Здесь контент-менеджер создаёт свои курсы, уроки и упражнения.</p>
    </div>

    <a class="btn" href="{{ route('manager.courses.create') }}">Создать курс</a>
</div>

@if(session('success')) <div class="toast toast--ok">{{ session('success') }}</div> @endif
@if(session('error')) <div class="toast toast--bad">{{ session('error') }}</div> @endif

<div class="grid">
    @forelse($courses as $course)
        <article class="card lessonCard">
            <span class="badge">{{ $course->is_published ? 'Опубликован' : 'Черновик' }}</span>
            <h2>{{ $course->title }}</h2>
            <p class="muted">{{ $course->description ?: 'Описание пока не добавлено.' }}</p>
            <p class="muted small">Уроков: {{ $course->lessons_count }} • уровень: {{ $course->level }}</p>
            <div class="row">
                <a class="btn" href="{{ route('manager.courses.edit', $course) }}">Редактировать</a>
                @if($course->is_published)
                    <a class="btn btn--ghost" href="{{ route('content.courses.show', $course) }}">Посмотреть</a>
                @endif
            </div>
        </article>
    @empty
        <div class="emptyState card">
            <div class="emptyState__icon">📚</div>
            <h2>Курсов пока нет</h2>
            <p class="muted">Нажми “Создать курс”, чтобы добавить первый курс.</p>
        </div>
    @endforelse
</div>
@endsection
