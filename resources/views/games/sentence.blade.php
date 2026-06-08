@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('games.index') }}">← Назад к играм</a>

<div class="gameHeader">
    <div>
        <div class="badge">Уровень: {{ $levelName }}</div>
        <h1 class="h1">Собери предложение</h1>
        <p class="muted">Нажимай на слова в правильном порядке.</p>
    </div>

    <div class="gameStats">
        <span>🧩 <b id="step">1</b> / {{ count($sentences) }}</span>
        <span>⭐ <b id="score">0</b></span>
    </div>
</div>

<div class="card gamePlayCard">
    <div class="gameQuestionLabel">Переведи предложение:</div>
    <div class="sentenceRu" id="ruText">---</div>

    <div class="sentenceAnswer" id="answerBox"></div>
    <div class="gameOptions sentenceOptions" id="wordsBox"></div>

    <div class="row" style="justify-content:center; margin-top:14px;">
        <button class="btn btn--ghost" type="button" onclick="resetAnswer()">Сбросить</button>
        <button class="btn" type="button" onclick="checkAnswer()">Проверить</button>
    </div>

    <div class="gameResult" id="result"></div>
</div>

<script>
const sentences = @json($sentences);
const level = @json($level);

let index = 0;
let score = 0;
let selected = [];

const ruText = document.getElementById('ruText');
const answerBox = document.getElementById('answerBox');
const wordsBox = document.getElementById('wordsBox');
const resultEl = document.getElementById('result');
const scoreEl = document.getElementById('score');
const stepEl = document.getElementById('step');

function shuffle(arr) {
    return [...arr].sort(() => Math.random() - 0.5);
}

function renderQuestion() {
    if (index >= sentences.length) {
        finishGame();
        return;
    }

    selected = [];
    const item = sentences[index];

    ruText.textContent = item.ru;
    stepEl.textContent = index + 1;
    answerBox.innerHTML = '';
    wordsBox.innerHTML = '';
    resultEl.textContent = '';

    shuffle(item.words).forEach(word => {
        const btn = document.createElement('button');
        btn.className = 'gameOptionBtn sentenceWordBtn';
        btn.textContent = word;

        btn.onclick = () => {
            selected.push(word);
            btn.disabled = true;
            btn.classList.add('sentenceWordBtn--used');
            renderAnswer();
        };

        wordsBox.appendChild(btn);
    });
}

function renderAnswer() {
    answerBox.textContent = selected.join(' ');
}

function resetAnswer() {
    selected = [];
    document.querySelectorAll('.sentenceWordBtn').forEach(btn => {
        btn.disabled = false;
        btn.classList.remove('sentenceWordBtn--used');
    });
    renderAnswer();
}

function normalize(text) {
    return String(text).toLowerCase().replace(/[.!?,;:]/g, '').replace(/\s+/g, ' ').trim();
}

function checkAnswer() {
    const item = sentences[index];
    const answer = selected.join(' ');

    if (normalize(answer) === normalize(item.answer)) {
        score++;
        scoreEl.textContent = score;
        resultEl.textContent = 'Правильно! 🎉';
        resultEl.className = 'gameResult gameResult--ok';
    } else {
        resultEl.textContent = 'Ошибка 😅 Правильно: ' + item.answer;
        resultEl.className = 'gameResult gameResult--bad';
    }

    index++;
    setTimeout(renderQuestion, 900);
}

function finishGame() {
    ruText.textContent = 'Игра окончена!';
    answerBox.innerHTML = '';
    wordsBox.innerHTML = '';
    resultEl.className = 'gameResult gameResult--ok';
    resultEl.textContent = `Твой результат: ${score}`;

    fetch('{{ route('games.reward') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            game: 'sentence',
            score: score,
            level: level,
        })
    })
    .then(r => r.json())
    .then(data => {
        resultEl.textContent += ' • ' + data.message;
    });
}

renderQuestion();
</script>
@endsection