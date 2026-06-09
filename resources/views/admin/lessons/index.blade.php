@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Админка: уроки</h1>
        <p class="muted">Управляй уроками, упражнениями и предпросмотром.</p>
    </div>

    <div class="row">
        <a class="btn" href="{{ route('admin.lessons.create') }}">+ Создать урок</a>
        <a class="btn btn--ghost" href="{{ route('admin.index') }}">Админ-панель</a>
    </div>
</div>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<div class="adminLessonsList">
    @forelse($lessons as $lesson)
        <div class="card adminLessonCard">
            <div class="adminLessonCard__main">
                <div class="badge">
                    #{{ $lesson->order }}
                    @if(!empty($lesson->level))
                        · {{ $lesson->level }}
                    @endif
                </div>

                <h2 class="adminLessonCard__title">
                    {{ $lesson->title }}
                </h2>

                <p class="muted small">
                    {{ $lesson->description ?: 'Описание не заполнено' }}
                </p>

                <div class="lessonMeta">
                    <span class="pill">🧩 {{ $lesson->exercises_count ?? $lesson->exercises()->count() }} заданий</span>

                    @if($lesson->is_active)
                        <span class="pill">✅ Активен</span>
                    @else
                        <span class="pill">👁 Скрыт</span>
                    @endif
                </div>
            </div>

            <div class="adminLessonCard__actions">
                <a class="btn" href="{{ route('admin.lessons.edit', $lesson) }}">
                    Редактировать урок
                </a>

                <a class="btn btn--ghost" href="{{ route('admin.exercises', $lesson) }}">
                    Отдельно задания
                </a>

                <a class="btn btn--ghost" href="{{ route('admin.lessons.preview', $lesson) }}">
                    Предпросмотр
                </a>
            </div>
        </div>
    @empty
        <div class="card emptyState">
            <div class="emptyState__icon">📚</div>
            <div class="h2">Уроков пока нет</div>
            <div class="muted">Создай первый урок и добавь упражнения.</div>
            <a class="btn" href="{{ route('admin.lessons.create') }}">Создать урок</a>
        </div>
    @endforelse
</div>
@endsection
