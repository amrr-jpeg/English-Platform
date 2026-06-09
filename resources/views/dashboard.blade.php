@extends('layouts.kids')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard-responsive.css') }}?v=10">
@endpush

@section('content')
<div class="dashboardPage dashboardPage--adaptive">
    <section class="dashboardHero dashboardHero--adaptive">
        <div class="dashboardHero__main card">
            <div class="dashboardHero__label">🎮 English Platform</div>

            <h1 class="dashboardHero__title">Учись английскому играя</h1>

            <p class="dashboardHero__text">
                Проходи уроки по порядку, получай XP и монеты, открывай новые задания и прокачивай своего героя.
            </p>

            <div class="dashboardStats">
                <div class="dashboardStat">
                    <span class="dashboardStat__label">Уровень</span>
                    <strong class="dashboardStat__value">{{ $user->level }}</strong>
                </div>

                <div class="dashboardStat">
                    <span class="dashboardStat__label">Опыт</span>
                    <strong class="dashboardStat__value">{{ $user->xp }}</strong>
                </div>

                <div class="dashboardStat">
                    <span class="dashboardStat__label">Монеты</span>
                    <strong class="dashboardStat__value">{{ $user->coins }}</strong>
                </div>

                <div class="dashboardStat">
                    <span class="dashboardStat__label">Серия дней</span>
                    <strong class="dashboardStat__value">{{ $user->streak ?? 0 }}</strong>
                    <span class="dashboardStat__hint">Рекорд: {{ $user->best_streak ?? 0 }}</span>
                </div>
            </div>

            <div class="dashboardHint">
                🔥 Подсказка: проходи хотя бы одно упражнение в день, чтобы сохранять серию.
            </div>
        </div>

        <aside class="dashboardHero__side">
            <div class="dashboardMiniCard card dashboardMiniCard--mission">
                <div class="dashboardMiniCard__top">
                    <span class="badge">🎯 Миссия дня</span>
                    <span class="pill">🔥 Серия: {{ $user->streak ?? 0 }}</span>
                </div>

                <h2 class="dashboardMiniCard__title">Мини-цель на сегодня</h2>

                <p class="dashboardMiniCard__text">
                    Пройди хотя бы одно упражнение и получи прогресс за день.
                </p>
            </div>

            <div class="dashboardMiniCard card dashboardMiniCard--hero">
                @include('partials.player-avatar', [
                    'user' => $user,
                    'avatarClass' => 'dashboardAvatar dashboardAvatar--custom',
                ])

                <div>
                    <h2 class="dashboardMiniCard__title">Твой герой</h2>
                    <p class="dashboardMiniCard__text">
                        Чем больше уроков ты проходишь, тем быстрее растёт персонаж.
                    </p>
                </div>
            </div>

            <div class="dashboardMiniCard card lessonChestMiniCard">
                <div class="lessonChestMiniCard__top">
                    <span class="badge">🎁 Наградной путь</span>
                    <span class="pill">{{ $lessonChestProgress }}/5</span>
                </div>

                <div class="lessonChestMiniCard__body">
                    <div class="lessonChestMiniCard__icon {{ $lessonChestAvailable ? 'lessonChestMiniCard__icon--ready' : '' }}">
                        🎁
                    </div>

                    <div>
                        <h2 class="dashboardMiniCard__title">Сундук за уроки</h2>
                        <p class="dashboardMiniCard__text">
                            Пройдено: <b>{{ $completedLessonsCount }}</b> из {{ $totalLessonsCount }}.
                            До сундука: <b>{{ $lessonChestAvailable ? 0 : 5 - $lessonChestProgress }}</b>.
                        </p>
                    </div>
                </div>

                <div class="lessonChestMiniCard__progress">
                    <div class="lessonChestMiniCard__bar" style="width: {{ $lessonChestPercent }}%"></div>
                </div>

                @if ($lessonChestAvailable)
                    <form method="POST" action="{{ route('lesson-chest.claim') }}">
                        @csrf
                        <button class="lessonChestMiniCard__button" type="submit">
                            Открыть сундук ✨
                        </button>
                    </form>
                @else
                    <button class="lessonChestMiniCard__button lessonChestMiniCard__button--disabled" disabled>
                        Сундук закрыт 🔒
                    </button>
                @endif
            </div>
        </aside>
    </section>

    @if (session('lesson_chest_reward'))
        <div class="chestRewardOverlay" data-chest-reward>
            <div class="chestRewardModal card">
                <div class="chestRewardModal__shine"></div>
                <div class="chestRewardModal__chest">🎁</div>

                <h2>{{ session('lesson_chest_reward.title') }} открыт!</h2>

                <p>
                    За {{ session('lesson_chest_reward.milestone') }} пройденных уроков ты получил:
                </p>

                <div class="chestRewardModal__rewards">
                    <span>⚡ +{{ session('lesson_chest_reward.xp') }} XP</span>
                    <span>🪙 +{{ session('lesson_chest_reward.coins') }} монет</span>
                </div>

                <button class="btn" type="button" data-close-chest>
                    Забрать награду
                </button>
            </div>
        </div>
    @endif

    <section class="dashboardLessons">
        <div class="dashboardSectionHead">
            <div>
                <h2 class="dashboardSectionHead__title">Уроки</h2>
                <p class="dashboardSectionHead__text">Выбирай уроки по порядку и прокачивай героя.</p>
            </div>
        </div>

        <div class="dashboardLessonGrid" id="lessonsGrid">
            @foreach ($lessons as $lesson)
                @php
                    $p = $progress->get($lesson->id);
                    $done = $p?->completed_exercises ?? 0;
                    $total = $p?->total_exercises ?? $lesson->exercises_count;
                    $percent = $total > 0 ? intval(($done / $total) * 100) : 0;
                    $unlocked = in_array($lesson->id, $unlockedLessonIds);
                @endphp

                <article
                    class="card dashboardLessonCard {{ !$unlocked ? 'dashboardLessonCard--locked' : '' }}"
                    data-lesson-card
                    style="{{ $loop->index >= 8 ? 'display: none;' : '' }}"
                >
                    <div class="dashboardLessonCard__top">
                        <span class="badge {{ $percent >= 100 ? 'badge--ok' : '' }}">#{{ $lesson->order }}</span>
                        <span class="dashboardLessonCard__percent">{{ $percent }}%</span>
                    </div>

                    <h3 class="dashboardLessonCard__title">{{ $lesson->title }}</h3>
                    <p class="dashboardLessonCard__desc">{{ $lesson->description }}</p>

                    <div class="progress dashboardLessonCard__progress">
                        <div class="progress__bar" style="width: {{ $percent }}%"></div>
                    </div>

                    <div class="dashboardLessonCard__bottom">
                        <span class="dashboardLessonCard__meta">{{ $done }}/{{ $total }} заданий</span>

                        @if($unlocked)
                            <a class="btn dashboardLessonCard__button" href="{{ route('lessons.show', $lesson) }}">
                                {{ $percent >= 100 ? 'Повторить' : ($percent > 0 ? 'Продолжить' : 'Начать') }}
                            </a>
                        @else
                            <button class="btn btn--disabled dashboardLessonCard__button" disabled>
                                Закрыто 🔒
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @if ($lessons->count() > 8)
            <div class="dashboardMoreWrap">
                <button class="btn" type="button" id="showMoreLessonsBtn">
                    Больше уроков
                </button>
            </div>
        @endif
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const showMoreButton = document.getElementById('showMoreLessonsBtn');
    const lessonCards = document.querySelectorAll('[data-lesson-card]');
    let visibleCount = 8;
    const step = 8;

    function updateShowMoreButton() {
        if (!showMoreButton) return;

        if (visibleCount >= lessonCards.length) {
            showMoreButton.style.display = 'none';
        } else {
            showMoreButton.style.display = 'inline-flex';
        }
    }

    if (showMoreButton) {
        showMoreButton.addEventListener('click', () => {
            visibleCount += step;

            lessonCards.forEach((card, index) => {
                if (index < visibleCount) {
                    card.style.display = '';
                }
            });

            updateShowMoreButton();
        });

        updateShowMoreButton();
    }

    const chestOverlay = document.querySelector('[data-chest-reward]');
    const closeChestButton = document.querySelector('[data-close-chest]');

    if (chestOverlay && closeChestButton) {
        closeChestButton.addEventListener('click', () => {
            chestOverlay.style.opacity = '0';

            setTimeout(() => {
                chestOverlay.remove();
            }, 300);
        });
    }
});
</script>
@endsection