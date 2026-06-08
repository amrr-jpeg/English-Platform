@extends('layouts.kids')

@section('content')
<link rel="stylesheet" href="{{ asset('css/games-modern.css') }}">

<div class="modernGamePage" data-game-page>
    <a class="link" href="{{ route('games.index') }}">← Назад в игровой центр</a>

    <section class="modernGameHeader modernGameHeader--{{ $game['accent'] }}">
        <div class="modernGameHeader__main">
            <div class="modernGameHeader__icon">{{ $game['icon'] }}</div>
            <div>
                <div class="gameEyebrow">{{ $sourceName }} · уровень: {{ $levelName }}</div>
                <h1>{{ $game['title'] }}</h1>
                <p>{{ $game['description'] }}</p>
            </div>
        </div>

        <div class="modernGameStats">
            @if($time > 0)
                <div><span>⏱️</span><b id="timer">{{ $time }}</b><small>сек</small></div>
            @endif
            <div><span>⭐</span><b id="score">0</b><small>баллы</small></div>
            <div><span>📍</span><b id="step">1</b><small>/ {{ count($items) }}</small></div>
        </div>
    </section>

    <section class="modernPlayCard" id="playCard">
        <div class="questionProgress">
            <div class="questionProgress__bar" id="progressBar"></div>
        </div>

        <div id="quizArea"></div>
    </section>

    <section class="modernResultCard" id="resultCard" hidden>
        <div class="modernResultCard__icon" id="resultIcon">🏁</div>
        <h2 id="resultTitle">Игра завершена</h2>
        <p id="resultText">Сейчас посчитаем результат.</p>

        <div class="resultStatsGrid">
            <div><b id="finalScore">0</b><span>правильных</span></div>
            <div><b id="finalAccuracy">0%</b><span>точность</span></div>
            <div><b id="finalReward">—</b><span>награда</span></div>
        </div>

        <div class="wrongReview" id="wrongReview" hidden>
            <h3>Что стоит повторить</h3>
            <div id="wrongList"></div>
        </div>

        <div class="resultActions">
            <a class="gamePrimaryAction" href="{{ url()->current() }}?source={{ $source }}">Сыграть ещё раз</a>
            <a class="gameSecondaryAction" href="{{ route('dashboard') }}">К урокам</a>
            <a class="gameSecondaryAction" href="{{ route('shop') }}">В магазин</a>
        </div>
    </section>
</div>

<script>
const GAME_SESSION_ID = @json($sessionId);
const GAME_KEY = @json($game['key']);
const ITEMS = @json($items);
const CSRF_TOKEN = @json(csrf_token());
const REWARD_URL = @json(route('games.reward'));
const GAME_TIME = @json($time);

let currentIndex = 0;
let score = 0;
let finished = false;
let answers = [];
let timer = GAME_TIME;
let interval = null;
let questionLocked = false;
let sentenceSelected = [];
let memoryState = {
    first: null,
    second: null,
    locked: false,
    matched: 0,
    errors: 0,
};

const quizArea = document.getElementById('quizArea');
const playCard = document.getElementById('playCard');
const resultCard = document.getElementById('resultCard');
const scoreEl = document.getElementById('score');
const stepEl = document.getElementById('step');
const timerEl = document.getElementById('timer');
const progressBar = document.getElementById('progressBar');

function normalize(text) {
    return String(text || '')
        .toLowerCase()
        .replace(/[.!?,;:]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

function shuffle(array) {
    return [...array].sort(() => Math.random() - 0.5);
}

function escapeHtml(text) {
    return String(text ?? '').replace(/[&<>"']/g, (m) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[m]));
}

function makeButton(className, text, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = className;
    button.textContent = text;
    button.addEventListener('click', onClick);
    return button;
}

function lockQuestionControls() {
    quizArea.querySelectorAll('button, input').forEach((element) => {
        element.disabled = true;
    });
}

function updateProgress(forceComplete = false) {
    const total = Math.max(ITEMS.length, 1);
    const completed = forceComplete ? total : Math.min(currentIndex, total);
    progressBar.style.width = Math.min((completed / total) * 100, 100) + '%';
    stepEl.textContent = forceComplete ? total : Math.min(currentIndex + 1, total);
    scoreEl.textContent = score;
}

function startTimer() {
    if (!GAME_TIME || !timerEl || interval) return;

    interval = setInterval(() => {
        if (finished) {
            clearInterval(interval);
            return;
        }

        timer--;
        timerEl.textContent = timer;

        if (timer <= 0) {
            finishGame();
        }
    }, 1000);
}

function render() {
    updateProgress();

    if (ITEMS.length === 0) {
        quizArea.innerHTML = `<div class="emptyGameState">Пока нет заданий для этой игры.</div>`;
        return;
    }

    if (ITEMS[0].mode === 'memory') {
        renderMemory();
        return;
    }

    renderQuestion();
    startTimer();
}

function renderQuestion() {
    if (finished) return;

    if (currentIndex >= ITEMS.length) {
        finishGame();
        return;
    }

    questionLocked = false;
    sentenceSelected = [];
    updateProgress();

    const item = ITEMS[currentIndex];

    if (item.mode === 'sentence') {
        renderSentence(item);
        return;
    }

    if (item.mode === 'input') {
        renderInput(item);
        return;
    }

    if (item.mode === 'listening') {
        renderListening(item);
        return;
    }

    renderSelect(item);
}

function baseQuestionHtml(item, content) {
    return `
        <div class="questionTopLine">
            <div class="gameEyebrow">Задание ${currentIndex + 1} из ${ITEMS.length}</div>
            <button type="button" class="hintBtn" id="hintButton">💡 Подсказка</button>
        </div>
        <div class="modernQuestion">
            <div class="modernQuestion__label">${escapeHtml(item.subprompt || 'Выбери ответ')}</div>
            <div class="modernQuestion__prompt ${item.is_picture ? 'modernQuestion__prompt--picture' : ''}">${escapeHtml(item.prompt || '')}</div>
        </div>
        ${content}
        <div class="gameFeedback" id="feedback"></div>
    `;
}

function bindHintButton() {
    document.getElementById('hintButton')?.addEventListener('click', showHint);
}

function renderSelect(item) {
    quizArea.innerHTML = baseQuestionHtml(item, `<div class="answerGrid" id="answerGrid"></div>`);
    bindHintButton();

    const grid = document.getElementById('answerGrid');
    shuffle(item.options || []).forEach((option) => {
        grid.appendChild(makeButton('answerChoice', option, () => answerSelect(option)));
    });
}

function renderInput(item) {
    quizArea.innerHTML = baseQuestionHtml(item, `
        <div class="inputAnswerBox">
            <input class="input gameTextInput" id="textAnswer" placeholder="Введите ответ..." autocomplete="off">
            <button class="gamePrimaryAction" type="button" id="inputAnswerButton">Ответить</button>
        </div>
    `);
    bindHintButton();

    const input = document.getElementById('textAnswer');
    const button = document.getElementById('inputAnswerButton');

    input.focus();
    button.addEventListener('click', answerInput);
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter') answerInput();
    });
}

function renderListening(item) {
    const answerBlock = item.options && item.options.length > 0
        ? `<div class="answerGrid" id="answerGrid"></div>`
        : `<div class="inputAnswerBox"><input class="input gameTextInput" id="textAnswer" placeholder="Что ты услышал?" autocomplete="off"><button class="gamePrimaryAction" type="button" id="inputAnswerButton">Ответить</button></div>`;

    quizArea.innerHTML = `
        <div class="questionTopLine">
            <div class="gameEyebrow">Задание ${currentIndex + 1} из ${ITEMS.length}</div>
            <button type="button" class="hintBtn" id="hintButton">💡 Подсказка</button>
        </div>
        <div class="listeningGameBox">
            <div class="listeningGameBox__icon">🎧</div>
            <div>
                <div class="modernQuestion__label">${escapeHtml(item.subprompt || 'Прослушай слово')}</div>
                <h2>${escapeHtml(item.prompt || 'Прослушай и выбери ответ')}</h2>
                <div class="listeningActions">
                    <button type="button" class="gamePrimaryAction" id="listenNormal">▶ Прослушать</button>
                    <button type="button" class="gameSecondaryAction" id="listenSlow">🐢 Медленно</button>
                </div>
            </div>
        </div>
        ${answerBlock}
        <div class="gameFeedback" id="feedback"></div>
    `;

    bindHintButton();
    document.getElementById('listenNormal')?.addEventListener('click', () => speakCurrent(false));
    document.getElementById('listenSlow')?.addEventListener('click', () => speakCurrent(true));

    const grid = document.getElementById('answerGrid');
    if (grid) {
        shuffle(item.options || []).forEach((option) => {
            grid.appendChild(makeButton('answerChoice', option, () => answerSelect(option)));
        });
    } else {
        document.getElementById('inputAnswerButton')?.addEventListener('click', answerInput);
        document.getElementById('textAnswer')?.addEventListener('keydown', event => {
            if (event.key === 'Enter') answerInput();
        });
    }

    setTimeout(() => speakCurrent(false), 250);
}

function renderSentence(item) {
    const words = shuffle(item.words || String(item.correct || '').split(' '));

    quizArea.innerHTML = baseQuestionHtml(item, `
        <div class="sentenceAnswerBox" id="sentenceAnswer">Нажимай слова ниже</div>
        <div class="sentenceWords" id="sentenceWords"></div>
        <div class="sentenceActions">
            <button class="gameSecondaryAction" type="button" id="sentenceResetButton">Сбросить</button>
            <button class="gamePrimaryAction" type="button" id="sentenceCheckButton">Проверить</button>
        </div>
    `);
    bindHintButton();

    const wordsBox = document.getElementById('sentenceWords');
    words.forEach((word) => {
        wordsBox.appendChild(makeButton('sentencePick', word, (event) => pickSentenceWord(event.currentTarget)));
    });

    document.getElementById('sentenceResetButton')?.addEventListener('click', resetSentence);
    document.getElementById('sentenceCheckButton')?.addEventListener('click', answerSentence);
}

function renderMemory() {
    memoryState = {
        first: null,
        second: null,
        locked: false,
        matched: 0,
        errors: 0,
    };
    currentIndex = 0;
    score = 0;
    updateProgress();

    const cards = [];

    ITEMS.forEach((item, index) => {
        cards.push({ pair: index, type: 'en', value: item.en });
        cards.push({ pair: index, type: 'ru', value: item.ru });
    });

    quizArea.innerHTML = `
        <div class="memoryIntro">
            <h2>Найди пары</h2>
            <p>Открой английское слово и его перевод. За ошибки баллы снижаются.</p>
        </div>
        <div class="modernMemoryBoard" id="memoryBoard"></div>
        <div class="gameFeedback" id="feedback">Ошибок: 0</div>
    `;

    const board = document.getElementById('memoryBoard');

    shuffle(cards).forEach(card => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'modernMemoryCard';
        button.dataset.pair = card.pair;
        button.dataset.type = card.type;
        button.innerHTML = `<span class="modernMemoryCard__front">?</span><span class="modernMemoryCard__back">${escapeHtml(card.value)}</span>`;
        button.addEventListener('click', () => flipMemory(button));
        board.appendChild(button);
    });

    startTimer();
}

function answerSelect(value) {
    checkAnswer(value);
}

function answerInput() {
    const input = document.getElementById('textAnswer');
    checkAnswer(input ? input.value : '');
}

function pickSentenceWord(button) {
    if (questionLocked || button.disabled) return;

    sentenceSelected.push(button.textContent);
    button.disabled = true;
    button.classList.add('sentencePick--used');
    document.getElementById('sentenceAnswer').textContent = sentenceSelected.join(' ');
}

function resetSentence() {
    if (questionLocked) return;

    sentenceSelected = [];
    document.getElementById('sentenceAnswer').textContent = 'Нажимай слова ниже';
    document.querySelectorAll('.sentencePick').forEach(button => {
        button.disabled = false;
        button.classList.remove('sentencePick--used');
    });
}

function answerSentence() {
    checkAnswer(sentenceSelected.join(' '));
}

function checkAnswer(answer) {
    if (finished || questionLocked) return;

    const item = ITEMS[currentIndex];
    if (!item) return;

    questionLocked = true;
    const givenAnswer = String(answer || '').trim();
    const isCorrect = normalize(givenAnswer) === normalize(item.correct);
    const feedback = document.getElementById('feedback');

    answers[currentIndex] = givenAnswer;

    if (isCorrect) {
        score++;
        feedback.className = 'gameFeedback gameFeedback--ok';
        feedback.textContent = 'Правильно! Отличная работа 🎉';
    } else {
        feedback.className = 'gameFeedback gameFeedback--bad';
        feedback.textContent = `Почти! Правильный ответ: ${item.correct}`;
    }

    currentIndex++;
    updateProgress();
    lockQuestionControls();

    setTimeout(renderQuestion, 850);
}

function updateMemoryScore() {
    score = Math.max(0, memoryState.matched - memoryState.errors);
    scoreEl.textContent = score;
}

function updateMemoryFeedback(message, className = 'gameFeedback') {
    const feedback = document.getElementById('feedback');
    if (!feedback) return;
    feedback.className = className;
    feedback.textContent = message;
}

function flipMemory(card) {
    if (finished || memoryState.locked || card.classList.contains('modernMemoryCard--open') || card.classList.contains('modernMemoryCard--done')) {
        return;
    }

    card.classList.add('modernMemoryCard--open');

    if (!memoryState.first) {
        memoryState.first = card;
        return;
    }

    memoryState.second = card;
    memoryState.locked = true;

    const isPair = memoryState.first.dataset.pair === memoryState.second.dataset.pair &&
        memoryState.first.dataset.type !== memoryState.second.dataset.type;

    if (isPair) {
        memoryState.first.classList.add('modernMemoryCard--done');
        memoryState.second.classList.add('modernMemoryCard--done');
        memoryState.matched++;
        currentIndex = memoryState.matched;
        updateMemoryScore();
        updateProgress();
        updateMemoryFeedback(`Пара найдена. Ошибок: ${memoryState.errors}`, 'gameFeedback gameFeedback--ok');
        memoryState.first = null;
        memoryState.second = null;
        memoryState.locked = false;

        if (memoryState.matched >= ITEMS.length) {
            score = Math.max(0, ITEMS.length - memoryState.errors);
            scoreEl.textContent = score;
            setTimeout(finishGame, 450);
        }
    } else {
        memoryState.errors++;
        updateMemoryScore();
        updateMemoryFeedback(`Не пара. Ошибок: ${memoryState.errors}`, 'gameFeedback gameFeedback--bad');

        setTimeout(() => {
            memoryState.first.classList.remove('modernMemoryCard--open');
            memoryState.second.classList.remove('modernMemoryCard--open');
            memoryState.first = null;
            memoryState.second = null;
            memoryState.locked = false;
            updateMemoryFeedback(`Продолжай искать пары. Ошибок: ${memoryState.errors}`);
        }, 750);
    }
}

function showHint() {
    if (questionLocked) return;

    const item = ITEMS[currentIndex];
    const feedback = document.getElementById('feedback');
    if (!feedback || !item) return;

    feedback.className = 'gameFeedback gameFeedback--hint';
    feedback.textContent = item.hint || 'Вспомни тему урока и не спеши.';
}

function speakCurrent(slow) {
    const item = ITEMS[currentIndex];
    if (!item || !('speechSynthesis' in window)) return;

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(item.speak || item.correct || item.prompt);
    utterance.lang = 'en-US';
    utterance.rate = slow ? 0.65 : 0.9;
    window.speechSynthesis.speak(utterance);
}

function finishGame() {
    if (finished) return;

    finished = true;
    if (interval) clearInterval(interval);
    updateProgress(true);

    if (ITEMS[0]?.mode === 'memory') {
        score = Math.max(0, ITEMS.length - memoryState.errors);
        scoreEl.textContent = score;
        answers = [];
    }

    fetch(REWARD_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({
            session_id: GAME_SESSION_ID,
            answers,
            score,
        }),
    })
        .then(response => response.json())
        .then(showResult)
        .catch(() => showResult({
            ok: false,
            score,
            total: ITEMS.length,
            accuracy: ITEMS.length ? Math.round(score / ITEMS.length * 100) : 0,
            message: 'Результат сохранён не был. Проверь подключение и попробуй ещё раз.',
        }));
}

function showResult(data) {
    playCard.hidden = true;
    resultCard.hidden = false;

    const total = data.total || ITEMS.length || 1;
    const accuracy = data.accuracy ?? Math.round(score / total * 100);

    document.getElementById('finalScore').textContent = `${data.score ?? score} / ${total}`;
    document.getElementById('finalAccuracy').textContent = `${Math.round(accuracy)}%`;
    document.getElementById('finalReward').textContent = data.xp || data.coins ? `+${data.xp} XP · +${data.coins} монет` : 'тренировка';

    const title = document.getElementById('resultTitle');
    const text = document.getElementById('resultText');
    const icon = document.getElementById('resultIcon');

    if (data.ok === false) {
        icon.textContent = '⚠️';
        title.textContent = 'Результат не сохранён';
        text.textContent = data.message || 'Игровая сессия устарела. Запусти игру заново.';
    } else if (accuracy >= 90) {
        icon.textContent = '🏆';
        title.textContent = 'Супер результат!';
        text.textContent = data.is_best ? 'Это новый личный рекорд. ' + data.message : data.message;
    } else if (accuracy >= 70) {
        icon.textContent = '🎉';
        title.textContent = 'Хорошая тренировка!';
        text.textContent = data.message;
    } else {
        icon.textContent = '💪';
        title.textContent = 'Есть что повторить';
        text.textContent = data.message || 'Попробуй ещё раз — получится лучше.';
    }

    if (data.daily_bonus) {
        text.textContent += ' · ' + data.daily_bonus.message;
    }

    if (data.wrong_items && data.wrong_items.length > 0) {
        const review = document.getElementById('wrongReview');
        const list = document.getElementById('wrongList');
        review.hidden = false;
        list.innerHTML = data.wrong_items.map(item => `
            <div class="wrongReviewItem">
                <b>${escapeHtml(item.prompt)}</b>
                <span>Правильно: ${escapeHtml(item.correct)}</span>
            </div>
        `).join('');
    }
}

render();
</script>
@endsection
