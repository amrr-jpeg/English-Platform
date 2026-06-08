@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('games.index') }}">← Назад к играм</a>

<div class="gameHeader">
    <div>
        <div class="badge">Уровень: {{ $levelName }}</div>
        <h1 class="h1">Найди перевод</h1>
        <p class="muted">Выбери правильный перевод до окончания времени.</p>
    </div>

    <div class="gameStats">
        <span>⏱️ <b id="timer">{{ $time }}</b> сек</span>
        <span>⭐ <b id="score">0</b></span>
    </div>
</div>

<div class="card gamePlayCard">
    <div class="gameQuestionLabel">Переведи слово:</div>
    <div class="gameBigWord" id="word">---</div>
    <div class="gameOptions" id="options"></div>
    <div class="gameResult" id="result"></div>
</div>

<script>
const words = @json($words);
const level = @json($level);
let index = 0;
let score = 0;
let time = {{ $time }};
let finished = false;

const wordEl = document.getElementById('word');
const optionsEl = document.getElementById('options');
const resultEl = document.getElementById('result');
const scoreEl = document.getElementById('score');
const timerEl = document.getElementById('timer');

function shuffle(arr) {
    return [...arr].sort(() => Math.random() - 0.5);
}

function renderQuestion() {
    if (finished) return;

    if (index >= words.length) {
        finishGame();
        return;
    }

    const item = words[index];
    wordEl.textContent = item.word;
    optionsEl.innerHTML = '';

    shuffle(item.options).forEach(option => {
        const btn = document.createElement('button');
        btn.className = 'gameOptionBtn';
        btn.textContent = option;

        btn.onclick = () => {
            if (option === item.correct) {
                score++;
                resultEl.textContent = 'Правильно! 🎉';
                resultEl.className = 'gameResult gameResult--ok';
            } else {
                resultEl.textContent = 'Ошибка 😅 Правильно: ' + item.correct;
                resultEl.className = 'gameResult gameResult--bad';
            }

            scoreEl.textContent = score;
            index++;
            setTimeout(renderQuestion, 650);
        };

        optionsEl.appendChild(btn);
    });
}

function finishGame() {
    finished = true;
    wordEl.textContent = 'Игра окончена!';
    optionsEl.innerHTML = '';
    resultEl.className = 'gameResult gameResult--ok';
    resultEl.textContent = `Твой результат: ${score}`;
    sendReward('translation', score, level);
}

function sendReward(game, score, level) {
    fetch('{{ route('games.reward') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ game, score, level })
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