@extends('layouts.kids')

@section('content')
<div class="examLockScreen" id="examLockScreen">
    <div class="card examLockScreen__card">
        <div class="examIntro__icon">🔒</div>
        <h1 class="h1">{{ $examData['title'] }}</h1>
        <p class="muted">{{ $examData['description'] }}</p>
        <p class="muted">Вопросы закреплены за этой попыткой. Если отправить ответы без активной сессии, экзамен не засчитается.</p>
        <p class="muted">Экзамен будет проходить в полноэкранном режиме.</p>
        <button class="btn" type="button" id="startExamBtn">
            Перейти в fullscreen и начать
        </button>
    </div>
</div>

<div class="examPage" id="examPage" hidden>
    <div class="examTop">
        <div>
            <div class="badge">{{ $examData['title'] }}</div>
            <h1 class="h1">Итоговая проверка</h1>
        </div>

        <div class="examStatus">
            <span>⏱️ <b id="examTimer">{{ $timeLimit }}</b> сек</span>
            <span>⚠️ <b id="warningCount">0</b> / 3</span>
        </div>
    </div>

    <div class="toast toast--bad" id="warningBox" hidden></div>

    <form method="POST" action="{{ route('exam.submit', $examNumber) }}" id="examForm">
        @csrf

        <input type="hidden" name="warnings" id="warningsInput" value="0">
        <input type="hidden" name="auto_finished" id="autoFinishedInput" value="0">

        <div class="stack">
            @foreach($questions as $index => $q)
                <div class="card examQuestion">
                    <div class="badge">Вопрос {{ $index + 1 }}</div>
                    <h2>{{ $q['question'] }}</h2>
                    @if(!empty($q['lesson']))
                        <p class="muted small">Тема: {{ $q['lesson'] }}</p>
                    @endif

                    <div class="examOptions">
                        @foreach($q['options'] as $option)
                            <label class="choice">
                                <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $option }}" required>
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="examSubmitBar card">
            <div>
                <b>Проверь ответы перед завершением</b>
                <div class="muted small">После отправки изменить ответы нельзя.</div>
            </div>

            <button class="btn" type="submit">Завершить экзамен</button>
        </div>
    </form>
</div>

<script>
const startBtn = document.getElementById('startExamBtn');
const lockScreen = document.getElementById('examLockScreen');
const examPage = document.getElementById('examPage');
const form = document.getElementById('examForm');

const timerEl = document.getElementById('examTimer');
const warningBox = document.getElementById('warningBox');
const warningCountEl = document.getElementById('warningCount');
const warningsInput = document.getElementById('warningsInput');
const autoFinishedInput = document.getElementById('autoFinishedInput');

let time = {{ $timeLimit }};
let warnings = 0;
let examStarted = false;
let submitting = false;

startBtn.addEventListener('click', async () => {
    try {
        await document.documentElement.requestFullscreen();
    } catch (e) {}

    examStarted = true;
    lockScreen.hidden = true;
    examPage.hidden = false;
    startTimer();
});

function startTimer() {
    const interval = setInterval(() => {
        if (!examStarted || submitting) {
            clearInterval(interval);
            return;
        }

        time--;
        timerEl.textContent = time;

        if (time <= 0) {
            clearInterval(interval);
            finishExam(false);
        }
    }, 1000);
}

function addWarning(message) {
    if (!examStarted || submitting) return;

    warnings++;
    warningsInput.value = warnings;
    warningCountEl.textContent = warnings;

    warningBox.hidden = false;
    warningBox.textContent = message + ` Предупреждение ${warnings}/3`;

    if (warnings >= 3) {
        finishExam(true);
    }
}

function finishExam(auto) {
    submitting = true;
    autoFinishedInput.value = auto ? '1' : '0';
    warningsInput.value = warnings;
    form.submit();
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) addWarning('Обнаружено переключение вкладки.');
});

window.addEventListener('blur', () => {
    addWarning('Окно экзамена потеряло фокус.');
});

document.addEventListener('fullscreenchange', () => {
    if (examStarted && !submitting && !document.fullscreenElement) {
        addWarning('Обнаружен выход из полноэкранного режима.');
    }
});

form.addEventListener('submit', () => {
    submitting = true;
});
</script>
@endsection
