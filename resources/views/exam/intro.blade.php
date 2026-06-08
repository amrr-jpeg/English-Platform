@extends('layouts.kids')

@section('content')
<div class="examPage">
<div class="hero">
    <div>
        <h1 class="h1">Экзамены</h1>
        <p class="muted">Экзамены автоматически собирают вопросы из пройденных уроков. Награда выдаётся за новый лучший результат.</p>
    </div>
</div>

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<div class="grid">
    @foreach($exams as $exam)
        <div class="card">
            <div class="badge">Уроки {{ $exam['from'] }}–{{ $exam['to'] }}</div>
            <h2 class="h2">{{ $exam['title'] }}</h2>
            <p class="muted">{{ $exam['description'] }}</p>

            <div class="progress">
                <div class="progress__bar" style="width: {{ $exam['total'] > 0 ? ($exam['completed'] / $exam['total']) * 100 : 0 }}%"></div>
            </div>

            <p class="muted small">Пройдено уроков: {{ $exam['completed'] }} / {{ $exam['total'] }}</p>
            <p class="muted small">Порог сдачи: {{ $exam['pass_percent'] }}%</p>

            @if($exam['best_result'])
                <div class="toast toast--ok">Лучший результат: {{ $exam['best_result']->percent }}%</div>
            @endif

            @if($exam['unlocked'])
                <a class="btn" href="{{ route('exam.start', $exam['number']) }}">Начать экзамен</a>
            @else
                <button class="btn btn--disabled" disabled>Заблокировано</button>
            @endif
        </div>
    @endforeach
</div>
</div>
@endsection
