@extends('layouts.kids')

@section('content')
<div class="travelPage">
<a class="link" href="{{ route('travel.index') }}">← Назад к Travel English</a>

<div class="hero">
    <div>
        <h1 class="h1">Travel-игры</h1>
        <p class="muted">Короткие тренажёры по ситуациям путешествия. Результат влияет на общий прогресс.</p>
    </div>
</div>

<div class="gameGrid">
    @foreach($games as $game)
        <div class="card travelGameTile" data-game="{{ $game['key'] }}" data-questions='@json($game['questions'])'>
            <div class="gameTile__icon">{{ $game['icon'] }}</div>
            <h2>{{ $game['title'] }}</h2>
            <p class="muted">{{ $game['description'] }}</p>
            <button class="btn travelGameStart" type="button">Начать</button>
        </div>
    @endforeach
</div>

<div class="card gamePlayCard" id="travelGameBox" hidden>
    <div class="gameHeader">
        <div>
            <div class="badge">Travel English</div>
            <h2 class="h2" id="travelGameTitle">Игра</h2>
        </div>
        <div class="gameStats">
            <span>🎯 <b id="travelStep">1</b></span>
            <span>⭐ <b id="travelScore">0</b></span>
        </div>
    </div>

    <div class="gameQuestionLabel">Переведи:</div>
    <div class="gameBigWord" id="travelWord">---</div>
    <div class="gameOptions" id="travelOptions"></div>
    <div class="gameResult" id="travelResult"></div>
</div>

<script>
const box = document.getElementById('travelGameBox');
const titleEl = document.getElementById('travelGameTitle');
const wordEl = document.getElementById('travelWord');
const optionsEl = document.getElementById('travelOptions');
const resultEl = document.getElementById('travelResult');
const scoreEl = document.getElementById('travelScore');
const stepEl = document.getElementById('travelStep');

let questions = [];
let activeGame = null;
let index = 0;
let score = 0;

function shuffle(arr) {
    return [...arr].sort(() => Math.random() - 0.5);
}

document.querySelectorAll('.travelGameStart').forEach(btn => {
    btn.addEventListener('click', () => {
        const tile = btn.closest('.travelGameTile');
        questions = JSON.parse(tile.dataset.questions);
        activeGame = tile.dataset.game;
        index = 0;
        score = 0;
        titleEl.textContent = tile.querySelector('h2').textContent;
        box.hidden = false;
        renderTravelQuestion();
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

function renderTravelQuestion() {
    if (index >= questions.length) {
        finishTravelGame();
        return;
    }

    const item = questions[index];
    const wrong = ['дом', 'книга', 'яблоко', 'машина', 'школа', 'отель', 'такси', 'парк'];
    const options = shuffle([item.a, ...shuffle(wrong.filter(x => x !== item.a)).slice(0, 3)]);

    wordEl.textContent = item.q;
    optionsEl.innerHTML = '';
    resultEl.textContent = '';
    scoreEl.textContent = score;
    stepEl.textContent = `${index + 1} / ${questions.length}`;

    options.forEach(opt => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'gameOptionBtn';
        button.textContent = opt;

        button.onclick = () => {
            if (opt === item.a) {
                score++;
                resultEl.className = 'gameResult gameResult--ok';
                resultEl.textContent = 'Правильно 🎉';
                if (window.AppSounds) AppSounds.play('correct');
            } else {
                resultEl.className = 'gameResult gameResult--bad';
                resultEl.textContent = 'Ошибка 😅 Правильно: ' + item.a;
                if (window.AppSounds) AppSounds.play('wrong');
            }

            index++;
            setTimeout(renderTravelQuestion, 700);
        };

        optionsEl.appendChild(button);
    });
}

function finishTravelGame() {
    wordEl.textContent = 'Готово!';
    optionsEl.innerHTML = '';
    resultEl.className = 'gameResult gameResult--ok';
    resultEl.textContent = `Результат: ${score} / ${questions.length}`;
    if (window.AppSounds) AppSounds.play('finish');

    fetch('{{ route('travel.games.reward') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            game: activeGame,
            score: score,
            total: questions.length,
        })
    })
    .then(r => r.json())
    .then(data => {
        resultEl.textContent += ' • ' + data.message;
    })
    .catch(() => {
        resultEl.textContent += ' • Не удалось начислить награду.';
    });
}
</script>
</div>
@endsection
