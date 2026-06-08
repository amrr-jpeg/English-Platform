<?php

namespace App\Http\Controllers;

use App\Models\ExerciseAttempt;
use App\Models\RewardLog;
use App\Models\UserLesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalAnswers = ExerciseAttempt::where('user_id', $user->id)->count();
        $correctAnswers = ExerciseAttempt::where('user_id', $user->id)->where('is_correct', true)->count();
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100) : 0;

        $completedLessons = UserLesson::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $learnedWords = ExerciseAttempt::where('user_id', $user->id)
            ->where('is_correct', true)
            ->with('exercise')
            ->get()
            ->pluck('exercise.answer')
            ->filter()
            ->unique()
            ->count();

        $chart = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            $xp = RewardLog::where('user_id', $user->id)
                ->whereDate('created_at', $date->toDateString())
                ->sum('xp');

            $chart[] = [
                'label' => $date->format('d.m'),
                'xp' => (int) $xp,
            ];
        }

        $maxXp = max(10, collect($chart)->max('xp'));

        $rewardSources = RewardLog::where('user_id', $user->id)
            ->selectRaw('source, SUM(xp) as xp, SUM(coins) as coins, COUNT(*) as count')
            ->groupBy('source')
            ->orderByDesc('xp')
            ->get();

        $recentActivity = RewardLog::where('user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();

        $weakExercises = ExerciseAttempt::where('user_id', $user->id)
            ->where('is_correct', false)
            ->with('exercise.lesson')
            ->latest()
            ->limit(8)
            ->get();

        $hardExercises = ExerciseAttempt::query()
            ->where('user_id', $user->id)
            ->where('is_correct', false)
            ->selectRaw('exercise_id, COUNT(*) as wrong_count, MAX(created_at) as last_wrong_at')
            ->groupBy('exercise_id')
            ->having('wrong_count', '>=', 2)
            ->with('exercise.lesson')
            ->orderByDesc('wrong_count')
            ->limit(5)
            ->get();

        $todayXp = RewardLog::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->sum('xp');

        $recommendations = $this->buildRecommendations(
            $completedLessons,
            $accuracy,
            $totalAnswers,
            $weakExercises,
            $hardExercises,
            (int) $todayXp
        );

        return view('stats.index', compact(
            'user',
            'totalAnswers',
            'correctAnswers',
            'accuracy',
            'completedLessons',
            'learnedWords',
            'chart',
            'maxXp',
            'rewardSources',
            'recentActivity',
            'weakExercises',
            'hardExercises',
            'recommendations'
        ));
    }

    private function buildRecommendations($completedLessons, $accuracy, $totalAnswers, $weakExercises, $hardExercises, int $todayXp): array
    {
        $recommendations = [];

        if ($completedLessons === 0) {
            $recommendations[] = [
                'icon' => '📚',
                'title' => 'Начни с первого урока',
                'text' => 'Пройди первый урок полностью: после завершения откроется следующий и начислится бонус.',
                'route' => route('dashboard'),
                'button' => 'К урокам',
            ];
        }

        if ($totalAnswers > 0 && $accuracy < 70) {
            $recommendations[] = [
                'icon' => '🎯',
                'title' => 'Повтори сложные ответы',
                'text' => 'Точность ниже 70%. Лучше закрепить ошибки перед новым уроком.',
                'route' => route('mistakes.index'),
                'button' => 'К ошибкам',
            ];
        }

        $hard = $hardExercises->first();
        if ($hard && $hard->exercise) {
            $recommendations[] = [
                'icon' => '🧠',
                'title' => 'Сложное задание',
                'text' => 'Чаще всего ошибки встречались в задании: «' . $hard->exercise->question . '».',
                'route' => $hard->exercise->lesson ? route('lessons.show', ['lesson' => $hard->exercise->lesson, 'exercise' => $hard->exercise->id]) : route('mistakes.index'),
                'button' => 'Повторить',
            ];
        }

        if ($weakExercises->count() > 0 && count($recommendations) < 3) {
            $recommendations[] = [
                'icon' => '✍️',
                'title' => 'Есть свежие ошибки',
                'text' => 'Раздел «Мои ошибки» покажет задания, где последняя попытка была неправильной.',
                'route' => route('mistakes.index'),
                'button' => 'Исправить',
            ];
        }

        if ($todayXp === 0 && count($recommendations) < 3) {
            $recommendations[] = [
                'icon' => '🔥',
                'title' => 'Сохрани серию',
                'text' => 'Сегодня ещё нет XP. Выполни хотя бы одно упражнение, чтобы день был активным.',
                'route' => route('dashboard'),
                'button' => 'Начать',
            ];
        }

        if (count($recommendations) === 0) {
            $recommendations[] = [
                'icon' => '🚀',
                'title' => 'Продолжай курс',
                'text' => 'Прогресс стабильный. Можно переходить к следующему уроку или потренироваться в играх.',
                'route' => route('dashboard'),
                'button' => 'Продолжить',
            ];
        }

        return array_slice($recommendations, 0, 3);
    }
}
