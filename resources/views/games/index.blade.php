@extends('layouts.kids')

@section('content')
<link rel="stylesheet" href="{{ asset('css/games-modern.css') }}">

<div class="gameCenterPage">
    <section class="gameCenterHero">
        <div class="gameCenterHero__content">
            <div class="gameEyebrow">🎮 Игровой центр</div>
            <h1 class="gameCenterHero__title">Мини-игры для тренировки английского</h1>
            <p class="gameCenterHero__text">
                Играй, повторяй слова из уроков, тренируй аудирование и исправляй ошибки.
                Награды начисляются честно: максимум за 3 результативные игры в день.
            </p>

            <div class="gameHeroActions">
                <a class="gamePrimaryAction" href="{{ route('games.listening', ['level' => 'easy', 'source' => 'learned']) }}">
                    🎧 Начать с аудирования
                </a>
                <a class="gameSecondaryAction" href="{{ route('games.mistakes', ['level' => 'easy']) }}">
                    🎯 Повторить ошибки
                </a>
            </div>
        </div>

        <aside class="dailyMissionCard">
            <div class="dailyMissionCard__top">
                <div>
                    <div class="gameEyebrow">Сегодняшняя миссия</div>
                    <h2>Дневной челлендж</h2>
                </div>
                <div class="dailyMissionCard__icon">🏆</div>
            </div>

            <div class="missionList">
                <div class="missionItem {{ $mission['played'] >= $mission['played_goal'] ? 'missionItem--done' : '' }}">
                    <span>{{ $mission['played'] >= $mission['played_goal'] ? '✅' : '🎮' }}</span>
                    <div>
                        <b>Сыграй 2 игры</b>
                        <small>{{ min($mission['played'], $mission['played_goal']) }} / {{ $mission['played_goal'] }}</small>
                    </div>
                </div>

                <div class="missionItem {{ $mission['correct'] >= $mission['correct_goal'] ? 'missionItem--done' : '' }}">
                    <span>{{ $mission['correct'] >= $mission['correct_goal'] ? '✅' : '⭐' }}</span>
                    <div>
                        <b>Набери 5 правильных ответов</b>
                        <small>{{ min($mission['correct'], $mission['correct_goal']) }} / {{ $mission['correct_goal'] }}</small>
                    </div>
                </div>

                <div class="missionReward {{ $mission['completed'] ? 'missionReward--ready' : '' }}">
                    {{ $mission['rewarded'] ? 'Награда уже получена' : 'Награда: ' . $mission['reward'] }}
                </div>
            </div>
        </aside>
    </section>

    <section class="gameSourcePanel">
        <div>
            <h2>Что тренируем?</h2>
            <p>Игры могут брать слова из разных частей платформы.</p>
        </div>

        <div class="sourceChips">
            <div class="sourceChip">
                <span>📚</span>
                <b>Изученные</b>
                <small>{{ $sourceStats['learned'] ?? 0 }} слов</small>
            </div>
            <div class="sourceChip">
                <span>🧭</span>
                <b>Текущий урок</b>
                <small>{{ $sourceStats['current'] ?? 0 }} слов</small>
            </div>
            <div class="sourceChip">
                <span>🎯</span>
                <b>Мои ошибки</b>
                <small>{{ $sourceStats['mistakes'] ?? 0 }} заданий</small>
            </div>
            <div class="sourceChip">
                <span>✈️</span>
                <b>Travel English</b>
                <small>{{ $sourceStats['travel'] ?? 0 }} слов</small>
            </div>
        </div>
    </section>

    <section class="gamesCatalog">
        @foreach($games as $game)
            @php
                $best = $bestScores[$game['key']] ?? null;
                $isLocked = (int) $user->level < (int) $game['min_level'];
            @endphp

            <article class="modernGameCard modernGameCard--{{ $game['accent'] }} {{ $isLocked ? 'modernGameCard--locked' : '' }}">
                <div class="modernGameCard__top">
                    <div class="modernGameCard__icon">{{ $game['icon'] }}</div>
                    <div class="modernGameCard__category">{{ $game['category'] }}</div>
                </div>

                <h2>{{ $game['title'] }}</h2>
                <p>{{ $game['description'] }}</p>

                @if($best)
                    <div class="bestScoreBadge">
                        🥇 Лучший результат: {{ $best['score'] }} · {{ (int) $best['accuracy'] }}%
                    </div>
                @else
                    <div class="bestScoreBadge bestScoreBadge--empty">
                        Новый тренажёр — рекорда пока нет
                    </div>
                @endif

                @if($isLocked)
                    <div class="lockedGameNotice">
                        🔒 Откроется на уровне {{ $game['min_level'] }}
                    </div>
                @else
                    <div class="gameLevelRow">
                        @foreach($levels as $key => $name)
                            <a class="modernLevelBtn modernLevelBtn--{{ $key }}"
                               href="{{ route($game['route'], ['level' => $key, 'source' => 'learned']) }}">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>

                    @if($game['key'] !== 'mistakes')
                        <details class="sourcePicker">
                            <summary>Выбрать источник слов</summary>
                            <div class="sourcePicker__grid">
                                @foreach($sources as $sourceKey => $sourceName)
                                    <a href="{{ route($game['route'], ['level' => 'easy', 'source' => $sourceKey]) }}">
                                        {{ $sourceName }}
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @endif
            </article>
        @endforeach
    </section>
</div>
@endsection
