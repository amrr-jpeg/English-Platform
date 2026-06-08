@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('games.index') }}">← Назад к играм</a>

<div class="gameHeader">
    <div>
        <div class="badge">Уровень: {{ $levelName }}</div>
        <h1 class="h1">Быстрый ввод</h1>
        <p class="muted">Напиши английское слово по русскому переводу.</p>
    </div>

    <div class="gameStats">
        <span>⏱️ <b id="timer">{{ $time }}</b> сек</span>
        <span>⭐ <b id="score">0</b></span>
    </div>
</div>

<div class="card gamePlayCard">
    <div class="gameQuestionLabel">Напиши по-английски:</div>
    <div class="gameBigWord" id="ruWord">---</div>

    <input class="input typingInput" id="answerInput" placeholder="Введите ответ..." autocomplete="off">

    <div class="row" style="justify-content:center; margin-top:14px;">
        <button class="btn" type="button" onclick="checkTyping()">Ответить</button>
    </div>

    <div class="gameResult" id="result"></div>
</div>

<script>
const items = @json($items);
const level = @json($level);

let index = 0;
let score = 0;
let time = {{ $time }};
let finished = false;

const ruWord = document.getElementById('ruWord');
const input = document.getElementById('answerInput');
const resultEl = document.getElementById('result');
const timerEl = document.getElementById('timer');
const scoreEl = document.getElementById('score');

function renderQuestion() {
    if (finished) return;

    if (index >= items.length) {
        finishGame();
        return;
    }

    ruWord.textContent = items[index].ru;
    input.value = '';
    input.focus();
}

function normalize(text) {
    return String(text).toLowerCase().trim();
}

function checkTyping() {
    if (finished) return;

    const item = items[index];

    if (normalize(input.value) === normalize(item.answer)) {
        score++;
        scoreEl.textContent = score;
        resultEl.textContent = 'Правильно! 🎉';
        resultEl.className = 'gameResult gameResult--ok';
    } else {
        resultEl.textContent = 'Ошибка 😅 Правильно: ' + item.answer;
        resultEl.className = 'gameResult gameResult--bad';
    }

    index++;
    setTimeout(renderQuestion, 700);
}

input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        checkTyping();
    }
});

function finishGame() {
    finished = true;
    ruWord.textContent = 'Игра окончена!';
    input.disabled = true;
    resultEl.className = 'gameResult gameResult--ok';
    resultEl.textContent = `Твой результат: ${score}`;

    fetch('{{ route('games.reward') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            game: 'typing',
            score: score,
            level: level,
        })
    })
    .then(r => r.json())
    .then(data => {
        resultEl.textContent += ' • ' + data.message;
    });
}

const interval = setInterval(() => {
    if (finished) {
        clearInterval(interval);
        return;
    }

    time--;
    timerEl.textContent = time;

    if (time <= 0) {
        clearInterval(interval);
        finishGame();
    }
}, 1000);

renderQuestion();
</script>
@endsection