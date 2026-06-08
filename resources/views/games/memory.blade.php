@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('games.index') }}">← Назад к играм</a>

<div class="gameHeader">
    <div>
        <div class="badge">Уровень: {{ $levelName }}</div>
        <h1 class="h1">Memory Game</h1>
        <p class="muted">Найди пары: слово + картинка</p>
    </div>

    <div class="gameStats">
        <span>🎯 Пары: <b id="pairsDone">0</b> / {{ count($cards) / 2 }}</span>
        <span>⭐ Очки: <b id="score">0</b></span>
    </div>
</div>

<div class="memoryLevels">
    <a href="{{ route('games.memory', 'easy') }}" class="btn">Лёгкий</a>
    <a href="{{ route('games.memory', 'medium') }}" class="btn">Средний</a>
    <a href="{{ route('games.memory', 'hard') }}" class="btn">Сложный</a>
</div>

<div class="memoryBoard" id="memoryBoard"></div>
<div class="gameResult" id="result"></div>

<script>
const cards = @json($cards);
const level = @json($level);

let first = null;
let second = null;
let locked = false;
let matched = 0;
let score = 0;

const board = document.getElementById('memoryBoard');
const resultEl = document.getElementById('result');
const scoreEl = document.getElementById('score');
const pairsDoneEl = document.getElementById('pairsDone');

// создаём карточки
cards.forEach((card, index) => {
    const el = document.createElement('button');
    el.className = 'memoryCard';

    el.dataset.pair = card.pair;
    el.dataset.type = card.type;

    let content = '';

    if (card.type === 'word') {
        content = card.value;
    } else {
        content = `<img src="/images/memory/${card.value}" />`;
    }

    el.innerHTML = `
        <span class="memoryCard__inner">
            <span class="memoryCard__front">❓</span>
            <span class="memoryCard__back">${content}</span>
        </span>
    `;

    el.onclick = () => flipCard(el);
    board.appendChild(el);
});

// логика игры
function flipCard(card) {
    if (locked || card.classList.contains('memoryCard--open') || card.classList.contains('memoryCard--done')) {
        return;
    }

    card.classList.add('memoryCard--open');

    if (!first) {
        first = card;
        return;
    }

    second = card;
    locked = true;

    const isPair =
        first.dataset.pair === second.dataset.pair &&
        first.dataset.type !== second.dataset.type;

    if (isPair) {
        first.classList.add('memoryCard--done');
        second.classList.add('memoryCard--done');

        matched++;
        score++;

        scoreEl.textContent = score;
        pairsDoneEl.textContent = matched;

        first = null;
        second = null;
        locked = false;

        if (matched >= cards.length / 2) {
            finishGame();
        }
    } else {
        setTimeout(() => {
            first.classList.remove('memoryCard--open');
            second.classList.remove('memoryCard--open');

            first = null;
            second = null;
            locked = false;
        }, 800);
    }
}

function finishGame() {
    resultEl.className = 'gameResult gameResult--ok';
    resultEl.textContent = `Отлично! Все пары найдены. Очки: ${score}`;
    AppSounds.play('correct');

    fetch('{{ route('games.reward') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            game: 'memory',
            score: score,
            level: level,
        })
    })
    .then(r => r.json())
    .then(data => {
        resultEl.textContent += ' • ' + data.message;
    });
}
</script>
@endsection