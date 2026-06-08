<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\RewardLog;
use App\Models\UserExercise;
use App\Models\UserLesson;
use App\Services\ProgressService;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends Controller
{
    public function submit(Request $request, Exercise $exercise, ProgressService $progressService, RewardService $rewardService)
    {
        $user = Auth::user();
        $exercise->load('lesson');

        abort_unless($exercise->lesson && (bool) $exercise->lesson->is_active, 404);

        $request->validate([
            'answer' => ['required', 'string', 'max:2000'],
        ]);

        $existing = UserExercise::where('user_id', $user->id)
            ->where('exercise_id', $exercise->id)
            ->first();

        $alreadyCorrect = (bool) ($existing?->is_correct ?? false);

        $wasLessonCompleted = UserLesson::where('user_id', $user->id)
            ->where('lesson_id', $exercise->lesson_id)
            ->value('is_completed');

        [$userAnswer, $isCorrect] = $this->checkAnswer($request, $exercise, $progressService);

        ExerciseAttempt::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'lesson_id' => $exercise->lesson_id,
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'xp_reward' => ($isCorrect && !$alreadyCorrect && $exercise->type !== 'flashcards') ? (int) $exercise->xp_reward : 0,
            'coin_reward' => ($isCorrect && !$alreadyCorrect && $exercise->type !== 'flashcards') ? (int) $exercise->coin_reward : 0,
            'source' => $exercise->lesson?->category === 'Travel English' ? 'travel_lesson' : 'lesson',
            'meta' => [
                'exercise_type' => $exercise->type,
                'lesson_title' => $exercise->lesson?->title,
                'repeat_attempt' => $alreadyCorrect,
            ],
        ]);

        if (!$isCorrect) {
            if (!$alreadyCorrect) {
                UserExercise::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'exercise_id' => $exercise->id,
                    ],
                    [
                        'is_correct' => false,
                        'user_answer' => $userAnswer,
                    ]
                );
            }

            $hint = $this->hintFor($exercise);

            return back()
                ->with('error', $alreadyCorrect
                    ? 'Это повторение. Сейчас ответ неверный, но твой прошлый правильный результат сохранён.'
                    : 'Почти! Попробуй ещё раз 🙂')
                ->with('hint', $hint)
                ->withFragment('current-exercise');
        }

        UserExercise::updateOrCreate(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
            ],
            [
                'is_correct' => true,
                'user_answer' => $userAnswer,
            ]
        );

        if ($alreadyCorrect) {
            $progressService->refreshLessonProgress($user, $exercise->lesson_id);

            return back()->with(
                'success',
                'Правильно! Это повторение: прогресс сохранён, но награда за это упражнение уже была получена ранее.'
            )->withFragment('current-exercise');
        }

        $source = $exercise->lesson?->category === 'Travel English' ? 'travel_exercise' : 'exercise';
        $exerciseXp = $exercise->type === 'flashcards' ? 0 : (int) $exercise->xp_reward;
        $exerciseCoins = $exercise->type === 'flashcards' ? 0 : (int) $exercise->coin_reward;

        $reward = $rewardService->grant(
            $user,
            $source,
            $exercise->id,
            $exerciseXp,
            $exerciseCoins,
            $exercise->type === 'flashcards'
                ? 'Изучена карточка: ' . $exercise->question
                : 'Правильный ответ: ' . $exercise->question,
            ['lesson_id' => $exercise->lesson_id],
            true
        );

        $progressService->refreshLessonProgress($user, $exercise->lesson_id);
        $rewardService->flashFirstAchievement($reward);

        $userLesson = UserLesson::where('user_id', $user->id)
            ->where('lesson_id', $exercise->lesson_id)
            ->first();

        $lessonCompletionMessage = $this->grantLessonCompletionBonusIfNeeded(
            $user,
            $exercise,
            $rewardService,
            (bool) $wasLessonCompleted,
            (bool) ($userLesson?->is_completed ?? false),
            $progressService
        );

        $msg = $exercise->type === 'flashcards'
            ? 'Отлично! Карточка изучена. Награда за карточки не начисляется, чтобы прогресс был честным.'
            : 'Правильно! 🎉 +' . $reward['xp'] . ' XP и +' . $reward['coins'] . ' монет.';

        if ($reward['streak_changed']) {
            $user->refresh();
            $msg .= ' 🔥 Серия: ' . $user->streak;
        }

        if ($lessonCompletionMessage !== null) {
            $msg .= ' ' . $lessonCompletionMessage;
        }

        return back()->with('success', $msg)->withFragment('current-exercise');
    }

    private function grantLessonCompletionBonusIfNeeded(
        $user,
        Exercise $exercise,
        RewardService $rewardService,
        bool $wasLessonCompleted,
        bool $isLessonCompletedNow,
        ProgressService $progressService
    ): ?string {
        if (!$isLessonCompletedNow || $wasLessonCompleted) {
            return null;
        }

        $alreadyRewarded = RewardLog::where('user_id', $user->id)
            ->where('source', 'lesson_complete')
            ->where('source_id', $exercise->lesson_id)
            ->exists();

        if ($alreadyRewarded) {
            return null;
        }

        $completionReward = $rewardService->grant(
            $user,
            'lesson_complete',
            $exercise->lesson_id,
            20,
            10,
            'Завершён урок: ' . $exercise->lesson->title,
            ['lesson_title' => $exercise->lesson->title]
        );

        $rewardService->flashFirstAchievement($completionReward);

        $nextLesson = $progressService->nextLessonAfter($exercise->lesson);

        session()->flash('lesson_completed_summary', [
            'title' => $exercise->lesson->title,
            'xp' => 20,
            'coins' => 10,
            'next_lesson' => $nextLesson?->title,
            'next_lesson_id' => $nextLesson?->id,
        ]);

        if ($nextLesson) {
            return 'Урок завершён: +20 XP и +10 монет. Открыт следующий урок: «' . $nextLesson->title . '».';
        }

        return 'Урок завершён: +20 XP и +10 монет.';
    }

    private function checkAnswer(Request $request, Exercise $exercise, ProgressService $progressService): array
    {
        $userAnswer = trim((string) $request->input('answer'));
        $isCorrect = false;

        if (in_array($exercise->type, ['choice', 'input', 'scramble', 'listening'], true)) {
            $isCorrect = $progressService->normalize($userAnswer) === $progressService->normalize((string) $exercise->answer);
        }

        if (in_array($exercise->type, ['drag_word', 'syllables'], true)) {
            $correct = (string) ($exercise->data['answer'] ?? $exercise->answer ?? '');
            $isCorrect = $progressService->normalize($userAnswer) === $progressService->normalize($correct);
        }

        if ($exercise->type === 'drag_sentence') {
            $correct = (string) ($exercise->data['answer'] ?? $exercise->answer ?? '');
            $isCorrect = $progressService->normalizeSentence($userAnswer) === $progressService->normalizeSentence($correct);
        }

        if ($exercise->type === 'pairs') {
            $pairs = $request->input('pairs', []);
            $truePairs = $exercise->data['true_pairs'] ?? [];

            ksort($pairs);
            ksort($truePairs);

            $isCorrect = !empty($truePairs) && $pairs == $truePairs;
            $userAnswer = json_encode($pairs, JSON_UNESCAPED_UNICODE);
        }

        if ($exercise->type === 'flashcards') {
            $isCorrect = $userAnswer === 'done';
        }

        return [$userAnswer, $isCorrect];
    }

    private function hintFor(Exercise $exercise): string
    {
        $answer = (string) ($exercise->data['answer'] ?? $exercise->answer ?? '');

        if ($exercise->type === 'choice') {
            return 'Сравни варианты ответа с английским словом и вспомни тему урока.';
        }

        if ($exercise->type === 'listening') {
            return 'Прослушай запись ещё раз. Обрати внимание на первые и последние звуки в слове или фразе.';
        }

        if ($exercise->type === 'pairs') {
            return 'Проверь каждую пару отдельно: английское слово должно совпадать со своим переводом.';
        }

        if ($answer === '') {
            return 'Внимательно перечитай условие и попробуй ещё раз.';
        }

        $first = mb_substr($answer, 0, 1);
        $length = mb_strlen($answer);

        return 'Подсказка: правильный ответ начинается с «' . $first . '» и содержит ' . $length . ' символов.';
    }
}
