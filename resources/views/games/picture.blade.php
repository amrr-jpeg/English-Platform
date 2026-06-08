@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('games.index') }}">← Назад к играм</a>

<div class="gameHeader">
    <div>
        <div class="badge">Уровень: {{ $levelName }}</div>
        <h1 class="h1">Угадай слово по картинке</h1>
        <p class="muted">Посмотри на изображение и выбери английское слово.</p>
    </div>

    <div class="gameStats">
        <span>🖼️ <b id="step">1</b> / {{ count($items) }}</span>
        <span>⭐ <b id="score">0</b></span>
    </div>
</div>

<div class="card gamePlayCard">
    <div class="pictureEmoji" id="emoji">🐱</div>
    <div class="gameOptions" id="options"></div>
    <div class="gameResult" id="result"></div>
</div>

<script>
const items = @json($items);
const level = @json($level);
let index = 0;
let score = 0;
let finished = false;

const emojiEl = document.getElementById('emoji');
const optionsEl = document.getElementById('options');
const resultEl = document.getElementById('result');
const scoreEl = document.getElementById('score');
const stepEl = document.getElementById('step');

function shuffle(arr) {
    return [...arr].sort(() => Math.random() - 0.5);
}

function renderQuestion() {
    if (finished) return;

    if (index >= items.length) {
        finishGame();
        return;
    }

    const item = items[index];
    emojiEl.textContent = item.emoji;
    stepEl.textContent = index + 1;
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
    emojiEl.textContent = '🏁';
    optionsEl.innerHTML = '';
    resultEl.className = 'gameResult gameResult--ok';
    resultEl.textContent = `Твой результат: ${score}`;
    sendReward('picture', score, level);
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

renderQuestion();
</script>
@endsection