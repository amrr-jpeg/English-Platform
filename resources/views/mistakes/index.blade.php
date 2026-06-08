@extends('layouts.kids')

@section('content')
<div class="hero">
    <div>
        <h1 class="h1">Работа над ошибками</h1>
        <p class="muted">Здесь отдельно показаны свежие ошибки и задания, где ошибки повторялись несколько раз.</p>
    </div>

    <div class="card">
        <div class="h2">{{ $mistakes->count() }}</div>
        <div class="muted">текущих ошибок</div>
    </div>
</div>

<div class="mistakeColumns">
    <section class="card mistakePanel">
        <div class="builderSection__head">
            <div>
                <h2 class="h2">Текущие ошибки</h2>
                <p class="muted">Задания, где последняя попытка была неправильной.</p>
            </div>
        </div>

        <div class="stack">
            @forelse($mistakes as $mistake)
                @php $exercise = $mistake->exercise; @endphp

                @if($exercise)
                    <div class="mistakeCard mistakeCard--compact">
                        <div>
                            <div class="badge">{{ $exercise->lesson->title ?? 'Урок' }}</div>
                            <h3>{{ $exercise->question }}</h3>

                            <p class="muted">Твой ответ: <b>{{ $mistake->user_answer ?: '—' }}</b></p>
                            <p class="muted">Правильный ответ: <b>{{ $exercise->answer ?: 'см. задание' }}</b></p>
                            <p class="muted small">Попытка: {{ $mistake->created_at->format('d.m.Y H:i') }}</p>
                        </div>

                        @if($exercise->lesson)
                            <a class="btn" href="{{ route('lessons.show', ['lesson' => $exercise->lesson, 'exercise' => $exercise->id]) }}">Повторить</a>
                        @endif
                    </div>
                @endif
            @empty
                <div class="emptyState emptyState--small">
                    <div class="emptyState__icon">✅</div>
                    <div class="h2">Свежих ошибок нет</div>
                    <div class="muted">Отличная работа!</div>
                </div>
            @endforelse
        </div>
    </section>

    <section class="card mistakePanel">
        <div class="builderSection__head">
            <div>
                <h2 class="h2">Сложные задания</h2>
                <p class="muted">Задания, где было две и больше неправильных попыток.</p>
            </div>
        </div>

        <div class="stack">
            @forelse($hardExercises as $row)
                @php $exercise = $row->exercise; @endphp

                @if($exercise)
                    <div class="mistakeCard mistakeCard--compact">
                        <div>
                            <div class="badge badge--warn">{{ (int) $row->wrong_count }} ошибок</div>
                            <h3>{{ $exercise->question }}</h3>
                            <p class="muted">Урок: <b>{{ $exercise->lesson->title ?? '—' }}</b></p>
                            <p class="muted">Правильный ответ: <b>{{ $exercise->answer ?: 'см. задание' }}</b></p>
                        </div>

                        @if($exercise->lesson)
                            <a class="btn btn--ghost" href="{{ route('lessons.show', ['lesson' => $exercise->lesson, 'exercise' => $exercise->id]) }}">Тренировать</a>
                        @endif
                    </div>
                @endif
            @empty
                <div class="emptyState emptyState--small">
                    <div class="emptyState__icon">🌟</div>
                    <div class="h2">Сложных заданий пока нет</div>
                    <div class="muted">Повторяющихся ошибок не найдено.</div>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
