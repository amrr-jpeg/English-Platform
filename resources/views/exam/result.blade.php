@extends('layouts.kids')

@section('content')
<div class="examPage">
<div class="examResultPage">
    <div class="card examResultCard">
        <div class="examIntro__icon">
            @if($warnings >= 3 || $autoFinished)
                🚫
            @elseif($passed)
                🏆
            @elseif($percent >= 50)
                ✅
            @else
                📚
            @endif
        </div>

        <h1 class="h1">Результат экзамена {{ $exam }}</h1>

        @if($warnings >= 3 || $autoFinished)
            <div class="toast toast--bad">Экзамен завершён автоматически из-за нарушений.</div>
        @elseif($passed)
            <div class="toast toast--ok">Экзамен сдан ✅</div>
        @else
            <div class="toast toast--bad">Экзамен не сдан. Нужно повторить материал.</div>
        @endif

        <div class="examResultScore">{{ $score }} / {{ $total }}</div>

        <div class="progress">
            <div class="progress__bar" style="width: {{ $percent }}%"></div>
        </div>

        <p class="muted">Процент выполнения: {{ $percent }}%</p>
        <p class="muted">Предупреждений: {{ $warnings }} / 3</p>
        <p class="muted">{{ $rewardMessage }}</p>

        <div class="rewards" style="justify-content:center;">
            <span class="pill">✨ +{{ $xp }} XP</span>
            <span class="pill">🪙 +{{ $coins }}</span>
        </div>

        <div class="row" style="justify-content:center; margin-top:18px;">
            <a class="btn" href="{{ route('dashboard') }}">На главную</a>
            <a class="btn btn--ghost" href="{{ route('exam.intro') }}">К экзаменам</a>
        </div>
    </div>
</div>
</div>
@endsection
