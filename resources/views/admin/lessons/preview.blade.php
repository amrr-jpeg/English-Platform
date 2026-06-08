@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('admin.exercises', $lesson) }}">← Назад в конструктор</a>

<div class="lessonHeader">
    <div>
        <div class="badge">Предпросмотр урока</div>
        <h1 class="h1">{{ $lesson->title }}</h1>
        <p class="muted">{{ $lesson->description }}</p>
    </div>

    <div class="card">
        <div class="h2">{{ $lesson->exercises->count() }}</div>
        <div class="muted">упражнений в уроке</div>
    </div>
</div>

<div class="stack">
@foreach($lesson->exercises as $ex)
    <div class="card exerciseCard">
        <div class="exercise__head">
            <div>
                <div class="badge">Задание {{ $ex->order }} • {{ $ex->type }}</div>
                <div class="exercise__question">{{ $ex->question }}</div>
            </div>

            <div class="rewards">
                <span class="pill">✨ +{{ $ex->xp_reward }}</span>
                <span class="pill">🪙 +{{ $ex->coin_reward }}</span>
            </div>
        </div>

        @if($ex->type === 'choice')
            <div class="choices">
                @foreach(($ex->options ?? []) as $opt)
                    <div class="choice">
                        <span>{{ $opt }}</span>
                    </div>
                @endforeach
            </div>

        @elseif($ex->type === 'input')
            <input class="input" value="Поле для ответа пользователя" disabled>

        @elseif($ex->type === 'listening')
            <div class="listeningExercise">
                <div class="listeningExercise__icon">🎧</div>
                <div class="listeningExercise__body">
                    <div class="badge">Аудирование</div>
                    <h3>Прослушать: {{ $ex->data['listening_text'] ?? $ex->answer }}</h3>
                    <p class="muted">На странице урока ученик услышит эту фразу через озвучивание браузера.</p>
                </div>
            </div>

        @elseif($ex->type === 'drag_word')
            <div class="dragZone dragZone--pool">
                <div class="dragZone__title">Буквы</div>
                @foreach(($ex->data['letters'] ?? []) as $letter)
                    <button type="button" class="dragChip">{{ $letter }}</button>
                @endforeach
            </div>

        @elseif($ex->type === 'drag_sentence')
            <div class="dragZone dragZone--pool">
                <div class="dragZone__title">Слова</div>
                @foreach(($ex->data['words'] ?? []) as $word)
                    <button type="button" class="dragChip dragChip--word">{{ $word }}</button>
                @endforeach
            </div>

        @elseif($ex->type === 'flashcards')
            <div class="flashcards">
                @foreach(($ex->data['cards'] ?? []) as $card)
                    <button type="button" class="flashcard">
                        <span class="flashcard__inner">
                            <span class="flashcard__side flashcard__front">{{ $card['front'] ?? '' }}</span>
                            <span class="flashcard__side flashcard__back">{{ $card['back'] ?? '' }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

        @elseif($ex->type === 'pairs')
            <div class="pairsList">
                @foreach(($ex->data['true_pairs'] ?? []) as $left => $right)
                    <div class="pairRow">
                        <div class="pairRow__left">{{ $left }}</div>
                        <div>{{ $right }}</div>
                    </div>
                @endforeach
            </div>

        @elseif($ex->type === 'syllables')
            <div class="dragZone dragZone--pool">
                <div class="dragZone__title">Слоги</div>
                @foreach(($ex->data['syllables'] ?? []) as $s)
                    <button type="button" class="dragChip">{{ $s }}</button>
                @endforeach
            </div>

        @elseif($ex->type === 'scramble')
            <div class="letterCloud">
                @foreach(($ex->data['letters'] ?? []) as $letter)
                    <span>{{ $letter }}</span>
                @endforeach
            </div>
        @endif

        <div class="muted small" style="margin-top: 12px;">
            Правильный ответ: <b>{{ $ex->answer }}</b>
        </div>
    </div>
@endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.flashcard').forEach((card) => {
        card.addEventListener('click', () => {
            card.classList.toggle('flashcard--flipped');
        });
    });
});
</script>
@endsection