@extends('layouts.kids')

@section('content')
@php
    $backRoute = $lesson->category === 'Travel English' ? route('travel.index') : route('dashboard');
    $total = max(1, $userLesson->total_exercises);
    $percent = intval(($userLesson->completed_exercises / $total) * 100);
    $completion = session('lesson_completed_summary');
@endphp

<a class="link" href="{{ $backRoute }}">← Назад</a>

@if($completion)
    <section class="card lessonCompleteNotice">
        <div class="lessonCompleteNotice__icon">🏆</div>
        <div>
            <div class="badge badge--ok">Урок завершён</div>
            <h2 class="h2">{{ $completion['title'] }}</h2>
            <p class="muted">
                Бонус: +{{ $completion['xp'] }} XP и +{{ $completion['coins'] }} монет.
                @if(!empty($completion['next_lesson']))
                    Открыт следующий урок: <b>{{ $completion['next_lesson'] }}</b>.
                @endif
            </p>
        </div>
    </section>
@endif

<div class="lessonHeader">
    <div>
        <div class="badge">{{ $lesson->category ?: 'Курс' }} • {{ $lesson->level }}</div>
        <h1 class="h1">{{ $lesson->title }}</h1>
        <p class="muted">{{ $lesson->description }}</p>
    </div>

    <div class="card progressCard">
        <div class="progressCard__title">Твой прогресс</div>
        <div class="progress">
            <div class="progress__bar" style="width: {{ $percent }}%"></div>
        </div>
        <div class="muted small">{{ $userLesson->completed_exercises }} / {{ $userLesson->total_exercises }} ({{ $percent }}%)</div>

        @if($userLesson->is_completed)
            <div class="badge badge--ok">Урок пройден ✅</div>
        @else
            <div class="badge">Задание {{ $currentIndex }} из {{ $lesson->exercises->count() }}</div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
    <script>document.addEventListener('DOMContentLoaded', () => window.AppSounds && AppSounds.play('correct'));</script>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
    <script>document.addEventListener('DOMContentLoaded', () => window.AppSounds && AppSounds.play('wrong'));</script>
@endif

@if(session('hint'))
    <div class="toast">💡 {{ session('hint') }}</div>
@endif

@if($theoryBlocks)
    <section class="card lessonTheoryCard" data-lesson-theory data-storage-key="lesson_theory_collapsed_{{ $lesson->id }}">
        <div class="builderSection__head lessonTheoryCard__head">
            <div>
                <h2 class="h2">Теория урока</h2>
                <p class="muted">Прочитай короткое объяснение перед заданием.</p>
            </div>
            <button type="button" class="btn btn--ghost lessonTheoryToggle" data-lesson-theory-toggle aria-expanded="true">
                Скрыть теорию
            </button>
        </div>

        <div class="lessonTheoryText" data-lesson-theory-body>
            @foreach($theoryBlocks as $block)
                @php $lines = preg_split('/\R/u', $block); @endphp

                @if(count($lines) > 1)
                    <div class="theoryBlock">
                        @foreach($lines as $line)
                            @if(trim($line) !== '')
                                <p>{{ $line }}</p>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p>{{ $block }}</p>
                @endif
            @endforeach
        </div>
    </section>
@endif

@if(!$currentExercise)
    <section class="card emptyState" id="current-exercise">
        <div class="emptyState__icon">🏁</div>
        <div class="h2">Урок завершён</div>
        <p class="muted">Все задания выполнены. Можно перейти дальше или повторить упражнения без повторного начисления награды.</p>
        <div class="row lessonRepeatActions">
            <a class="btn" href="{{ $backRoute }}">К списку уроков</a>
            @if($nextLesson)
                <a class="btn" href="{{ route('lessons.show', $nextLesson) }}">Следующий урок</a>
            @endif
            <a class="btn btn--ghost" href="{{ route('lessons.show', ['lesson' => $lesson, 'repeat' => 1]) }}">Повторить урок</a>
            <a class="btn btn--ghost" href="{{ route('games.index') }}">Игры для повторения</a>
        </div>
    </section>
@else
    @php
        $ex = $currentExercise;
        $data = $ex->data ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        if (!is_array($data)) {
            $data = [];
        }
        $alreadyDone = $done->get($ex->id)?->is_correct ?? false;
    @endphp

    <section class="card exerciseCard exerciseCard--single {{ $repeatMode ? 'exerciseCard--repeat' : '' }}" id="current-exercise">
        <div class="exercise__head">
            <div class="exercise__q">
                <div class="badge">
                    {{ $repeatMode ? 'Повторение' : 'Задание ' . $currentIndex . ' из ' . $lesson->exercises->count() }}
                </div>
                <div class="exercise__question">{{ $ex->question }}</div>
                @if($alreadyDone)
                    <p class="muted small">Это задание уже выполнено. Повторить можно, но награда второй раз не начисляется.</p>
                @endif
            </div>

            <div class="rewards">
                @if($alreadyDone || $ex->type === 'flashcards')
                    <span class="pill">✨ +0</span>
                    <span class="pill">🪙 +0</span>
                @else
                    <span class="pill">✨ +{{ $ex->xp_reward }}</span>
                    <span class="pill">🪙 +{{ $ex->coin_reward }}</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('exercises.submit', $ex) }}" class="answerForm">
            @csrf

            @if($ex->type === 'choice')
                @php
                    $options = $ex->options ?? [];
                    if (is_string($options)) {
                        $options = json_decode($options, true) ?: [];
                    }
                    if (!is_array($options)) {
                        $options = [];
                    }
                @endphp

                <div class="choices">
                    @foreach($options as $opt)
                        <label class="choice">
                            <input type="radio" name="answer" value="{{ $opt }}" required>
                            <span>{{ $opt }}</span>
                        </label>
                    @endforeach
                </div>

            @elseif($ex->type === 'listening')
                @php
                    $listeningText = (string) ($data['listening_text'] ?? $ex->answer);
                    $voiceLang = (string) ($data['voice_lang'] ?? 'en-US');
                    $options = $ex->options ?? [];
                    if (is_string($options)) {
                        $options = json_decode($options, true) ?: [];
                    }
                    if (!is_array($options)) {
                        $options = [];
                    }
                @endphp

                <div
                    class="listeningExercise"
                    data-listening-exercise
                    data-text="{{ e($listeningText) }}"
                    data-lang="{{ e($voiceLang) }}"
                >
                    <div class="listeningExercise__icon">🎧</div>
                    <div class="listeningExercise__body">
                        <div class="badge">Аудирование</div>
                        <h3>Прослушай и ответь</h3>
                        <p class="muted">Нажми кнопку, внимательно послушай английское слово или фразу и введи ответ.</p>

                        <div class="row listeningExercise__actions">
                            <button type="button" class="btn" data-listening-play>▶ Прослушать</button>
                            <button type="button" class="btn btn--ghost" data-listening-slow>🐢 Медленно</button>
                        </div>

                        <div class="muted small" data-listening-status>Голос озвучивания работает через браузер.</div>
                    </div>
                </div>

                @if(count($options) >= 2)
                    <div class="choices">
                        @foreach($options as $opt)
                            <label class="choice">
                                <input type="radio" name="answer" value="{{ $opt }}" required>
                                <span>{{ $opt }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <input class="input" type="text" name="answer" placeholder="Напиши, что услышал..." autocomplete="off" required autofocus>
                @endif

            @elseif($ex->type === 'input')
                <input class="input" type="text" name="answer" placeholder="Напиши ответ..." autocomplete="off" required autofocus>

            @elseif($ex->type === 'scramble')
                <div class="muted small">Собери слово или фразу:</div>
                <div class="letterCloud">
                    @foreach(($data['letters'] ?? preg_split('//u', str_replace(' ', '', $ex->answer), -1, PREG_SPLIT_NO_EMPTY)) as $letter)
                        <span>{{ $letter }}</span>
                    @endforeach
                </div>
                <input class="input" type="text" name="answer" placeholder="Впиши ответ..." autocomplete="off" required>

            @elseif(in_array($ex->type, ['drag_word', 'drag_sentence', 'syllables'], true))
                @php
                    $chips = $data['letters'] ?? $data['words'] ?? $data['syllables'] ?? [];
                    $joiner = $ex->type === 'drag_sentence' ? ' ' : '';
                    $title = $ex->type === 'drag_sentence' ? 'Предложение' : 'Ответ';
                @endphp

                <div class="muted small">Нажимай на части в правильном порядке или перетаскивай их.</div>
                <div class="dragGame" data-drag-game data-join="{{ $joiner }}">
                    <div class="dragZone dragZone--answer" data-drop>
                        <div class="dragZone__title">{{ $title }}</div>
                    </div>
                    <div class="dragZone dragZone--pool" data-pool>
                        <div class="dragZone__title">Варианты</div>
                        @foreach($chips as $chip)
                            <button type="button" class="dragChip" draggable="true" data-value="{{ $chip }}">{{ $chip }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="answer" class="dragAnswer" required>
                    <button type="button" class="btn btn--ghost dragReset">Сбросить</button>
                </div>

            @elseif($ex->type === 'pairs')
                <div class="muted small">Соедини пары: выбери перевод.</div>
                <div class="pairsList">
                    @foreach(($data['pairs'] ?? []) as $pair)
                        @php $left = $pair['left'] ?? ''; @endphp
                        @if($left !== '')
                            <div class="pairRow">
                                <div class="pairRow__left">{{ $left }}</div>
                                <select class="input" name="pairs[{{ $left }}]" required>
                                    <option value="">— выбрать —</option>
                                    @foreach(($data['right_options'] ?? []) as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach
                </div>
                <input type="hidden" name="answer" value="pairs" required>

            @elseif($ex->type === 'flashcards')
                <div class="flashcards">
                    @foreach(($data['cards'] ?? []) as $card)
                        <button type="button" class="flashcard">
                            <span class="flashcard__inner">
                                <span class="flashcard__side flashcard__front">{{ $card['front'] ?? '' }}</span>
                                <span class="flashcard__side flashcard__back">{{ $card['back'] ?? '' }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="answer" value="done">
            @endif

            <button class="btn" type="submit">{{ $ex->type === 'flashcards' ? 'Я запомнил' : 'Проверить' }}</button>

            @error('answer')
                <div class="toast toast--bad">{{ $message }}</div>
            @enderror
        </form>
    </section>
@endif

<section class="card lessonMapCard">
    <div class="builderSection__head">
        <div>
            <h2 class="h2">Карта урока</h2>
            <p class="muted">Зелёные пункты уже выполнены. После завершения урок можно повторять.</p>
        </div>
    </div>

    <div class="lessonStepMap">
        @foreach($lesson->exercises as $index => $exercise)
            @php $isDone = $done->get($exercise->id)?->is_correct ?? false; @endphp
            @if($userLesson->is_completed)
                <a href="{{ route('lessons.show', ['lesson' => $lesson, 'exercise' => $exercise->id]) }}"
                   class="lessonStepMap__item {{ $isDone ? 'lessonStepMap__item--done' : '' }} {{ $currentExercise && $currentExercise->id === $exercise->id ? 'lessonStepMap__item--current' : '' }}">
                    {{ $index + 1 }}
                </a>
            @else
                <span class="lessonStepMap__item {{ $isDone ? 'lessonStepMap__item--done' : '' }} {{ $currentExercise && $currentExercise->id === $exercise->id ? 'lessonStepMap__item--current' : '' }}">
                    {{ $index + 1 }}
                </span>
            @endif
        @endforeach
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const theoryCard = document.querySelector('[data-lesson-theory]');
    if (theoryCard) {
        const theoryBody = theoryCard.querySelector('[data-lesson-theory-body]');
        const theoryToggle = theoryCard.querySelector('[data-lesson-theory-toggle]');
        const storageKey = theoryCard.dataset.storageKey || 'lesson_theory_collapsed';

        const applyTheoryState = (collapsed) => {
            theoryCard.classList.toggle('lessonTheoryCard--collapsed', collapsed);
            if (theoryBody) theoryBody.hidden = collapsed;
            if (theoryToggle) {
                theoryToggle.textContent = collapsed ? 'Показать теорию' : 'Скрыть теорию';
                theoryToggle.setAttribute('aria-expanded', String(!collapsed));
            }
        };

        let savedCollapsed = false;
        try {
            savedCollapsed = localStorage.getItem(storageKey) === '1';
        } catch (error) {
            savedCollapsed = false;
        }
        applyTheoryState(savedCollapsed);

        theoryToggle?.addEventListener('click', () => {
            const collapsed = !theoryCard.classList.contains('lessonTheoryCard--collapsed');
            applyTheoryState(collapsed);
            try {
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            } catch (error) {}
        });
    }

    if (window.location.hash === '#current-exercise') {
        const currentExercise = document.getElementById('current-exercise');
        if (currentExercise) {
            setTimeout(() => currentExercise.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
        }
    }

    document.querySelectorAll('[data-listening-exercise]').forEach((box) => {
        const playBtn = box.querySelector('[data-listening-play]');
        const slowBtn = box.querySelector('[data-listening-slow]');
        const status = box.querySelector('[data-listening-status]');
        const text = box.dataset.text || '';
        const lang = box.dataset.lang || 'en-US';

        const speak = (rate = 0.9) => {
            if (!('speechSynthesis' in window) || !window.SpeechSynthesisUtterance) {
                if (status) status.textContent = 'В этом браузере нет встроенного озвучивания. Попробуй открыть сайт в Chrome, Edge или Яндекс.Браузере.';
                return;
            }

            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = lang;
            utterance.rate = rate;
            utterance.pitch = 1;
            utterance.volume = 1;
            window.speechSynthesis.speak(utterance);

            if (status) status.textContent = rate < 0.8 ? 'Слушаем медленно...' : 'Слушаем...';
            utterance.onend = () => {
                if (status) status.textContent = 'Можно прослушать ещё раз или ответить.';
            };
        };

        playBtn?.addEventListener('click', () => speak(0.9));
        slowBtn?.addEventListener('click', () => speak(0.65));
    });

    document.querySelectorAll('[data-drag-game]').forEach((game) => {
        const drop = game.querySelector('[data-drop]');
        const pool = game.querySelector('[data-pool]');
        const hidden = game.querySelector('.dragAnswer');
        const reset = game.querySelector('.dragReset');
        const joiner = game.dataset.join ?? '';
        let dragged = null;

        if (!drop || !pool || !hidden) return;

        const updateAnswer = () => {
            hidden.value = Array.from(drop.querySelectorAll('.dragChip'))
                .map((chip) => chip.dataset.value || chip.textContent.trim())
                .join(joiner)
                .trim();
        };

        const makeDropZone = (zone) => {
            zone.addEventListener('dragover', (event) => {
                event.preventDefault();
                zone.classList.add('dragZone--active');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('dragZone--active'));
            zone.addEventListener('drop', (event) => {
                event.preventDefault();
                zone.classList.remove('dragZone--active');
                if (dragged) zone.appendChild(dragged);
                updateAnswer();
            });
        };

        game.querySelectorAll('.dragChip').forEach((chip) => {
            chip.addEventListener('dragstart', (event) => {
                dragged = chip;
                chip.classList.add('dragChip--dragging');
                event.dataTransfer.effectAllowed = 'move';
            });
            chip.addEventListener('dragend', () => {
                chip.classList.remove('dragChip--dragging');
                dragged = null;
                updateAnswer();
            });
            chip.addEventListener('click', () => {
                if (chip.closest('[data-pool]')) drop.appendChild(chip);
                else pool.appendChild(chip);
                updateAnswer();
            });
        });

        makeDropZone(drop);
        makeDropZone(pool);

        if (reset) {
            reset.addEventListener('click', () => {
                Array.from(drop.querySelectorAll('.dragChip')).forEach((chip) => pool.appendChild(chip));
                updateAnswer();
            });
        }

        updateAnswer();
    });

    document.querySelectorAll('.flashcard').forEach((card) => {
        card.addEventListener('click', () => card.classList.toggle('flashcard--flipped'));
    });
});
</script>
@endsection
