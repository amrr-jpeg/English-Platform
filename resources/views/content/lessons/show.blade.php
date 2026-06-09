@extends('layouts.kids')

@section('content')
<div class="lessonHeader card">
    <div>
        <span class="badge">{{ $lesson->contentCourse?->title }}</span>
        <h1>{{ $lesson->title }}</h1>
        <p class="muted">{{ $lesson->description }}</p>
    </div>
    <a class="btn btn--ghost" href="{{ route('content.courses.show', $lesson->contentCourse) }}">К курсу</a>
</div>

@if(session('success')) <div class="toast toast--ok">{{ session('success') }}</div> @endif
@if(session('error')) <div class="toast toast--bad">{{ session('error') }}</div> @endif
@if(session('hint')) <div class="toast">Подсказка: {{ session('hint') }}</div> @endif

<div class="card stack">
    <div class="row" style="justify-content:space-between;">
        <b>Прогресс урока: {{ $completed }}/{{ $total }}</b>
        <b>{{ $percent }}%</b>
    </div>
    <div class="progress"><div class="progress__bar" style="width: {{ $percent }}%"></div></div>
</div>

@if($lesson->theory)
    <div class="card lessonTheoryText">
        <h2>Теория</h2>
        @foreach(preg_split('/\R{2,}/u', $lesson->theory) as $block)
            @if(trim($block) !== '')
                <p>{{ trim($block) }}</p>
            @endif
        @endforeach
    </div>
@endif

<div class="stack">
    @forelse($lesson->exercises as $exercise)
        @php $isDone = (bool) ($done->get($exercise->id)?->is_correct ?? false); @endphp
        <div class="exerciseCard card" id="exercise-{{ $exercise->id }}">
            <div class="exercise__head">
                <div>
                    <span class="badge {{ $isDone ? 'badge--ok' : '' }}">{{ $isDone ? 'готово' : 'задание' }} #{{ $exercise->order }}</span>
                    <h2>{{ $exercise->question }}</h2>
                    <p class="muted small">Награда: {{ $exercise->xp_reward }} XP • {{ $exercise->coin_reward }} монет</p>
                </div>
            </div>

            @if($exercise->type === 'pairs')
                <div class="pairsList">
                    @foreach(($exercise->data['pairs'] ?? []) as $pair)
                        <div class="pairRow"><b>{{ $pair['left'] ?? '' }}</b><span>{{ $pair['right'] ?? '' }}</span></div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('exercises.submit', $exercise) }}" class="answerForm">
                    @csrf
                    <input type="hidden" name="answer" value="pairs">
                    <button class="btn" type="submit">Отметить как изучено</button>
                </form>
            @elseif($exercise->type === 'choice')
                <form method="POST" action="{{ route('exercises.submit', $exercise) }}" class="answerForm">
                    @csrf
                    <div class="choices">
                        @foreach(($exercise->options ?? []) as $option)
                            <label class="choice"><input type="radio" name="answer" value="{{ $option }}" required> {{ $option }}</label>
                        @endforeach
                    </div>
                    <button class="btn" type="submit">Ответить</button>
                </form>
            @else
                @if($exercise->type === 'listening')
                    <div class="listeningExercise">
                        <div class="listeningExercise__icon">🎧</div>
                        <div class="listeningExercise__body">
                            <p class="muted">Текст для прослушивания: <b>{{ $exercise->data['listening_text'] ?? $exercise->answer }}</b></p>
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ route('exercises.submit', $exercise) }}" class="answerForm">
                    @csrf
                    <input class="input" name="answer" placeholder="Введи ответ" required>
                    <button class="btn" type="submit">Проверить</button>
                </form>
            @endif
        </div>
    @empty
        <div class="emptyState card"><h2>Заданий пока нет</h2></div>
    @endforelse
</div>
@endsection
