<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\RewardLog;
use App\Services\ProgressService;
use App\Services\RewardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function index(ProgressService $progressService)
    {
        $user = Auth::user();

        $lessons = $progressService->mainLessons();
        $progress = $progressService->progressByLesson($user);
        $unlockedLessonIds = $progressService->unlockedLessonIds($lessons, $progress);

        $completedLessonsCount = $progress
            ->filter(fn ($item) => (bool) $item->is_completed)
            ->count();

        $totalLessonsCount = $lessons->count();

        $currentMilestone = intdiv($completedLessonsCount, 5) * 5;
        $nextMilestone = $currentMilestone + 5;

        $lessonChestAvailable = $currentMilestone >= 5
            && !RewardLog::where('user_id', $user->id)
                ->where('source', 'lesson_chest')
                ->where('source_id', $currentMilestone)
                ->exists();

        $lessonChestProgress = $lessonChestAvailable
            ? 5
            : $completedLessonsCount % 5;

        $lessonChestPercent = min(100, (int) (($lessonChestProgress / 5) * 100));

        return view('dashboard', compact(
            'lessons',
            'progress',
            'unlockedLessonIds',
            'user',
            'completedLessonsCount',
            'totalLessonsCount',
            'nextMilestone',
            'currentMilestone',
            'lessonChestAvailable',
            'lessonChestProgress',
            'lessonChestPercent'
        ));
    }

    public function claimLessonChest(ProgressService $progressService, RewardService $rewardService): RedirectResponse
    {
        $user = Auth::user();

        $progress = $progressService->progressByLesson($user);

        $completedLessonsCount = $progress
            ->filter(fn ($item) => (bool) $item->is_completed)
            ->count();

        $milestone = intdiv($completedLessonsCount, 5) * 5;

        if ($milestone < 5) {
            return back()->with('error', 'Сундук пока закрыт. Пройди ещё уроки.');
        }

        $alreadyClaimed = RewardLog::where('user_id', $user->id)
            ->where('source', 'lesson_chest')
            ->where('source_id', $milestone)
            ->exists();

        if ($alreadyClaimed) {
            return back()->with('error', 'Этот сундук уже был открыт.');
        }

        $rewards = [
            ['xp' => 40, 'coins' => 20, 'title' => 'Малый сундук'],
            ['xp' => 60, 'coins' => 30, 'title' => 'Золотой сундук'],
            ['xp' => 90, 'coins' => 45, 'title' => 'Эпический сундук'],
        ];

        $reward = $rewards[array_rand($rewards)];

        $result = $rewardService->grant(
            $user,
            'lesson_chest',
            $milestone,
            $reward['xp'],
            $reward['coins'],
            $reward['title'] . ' за ' . $milestone . ' пройденных уроков',
            [
                'completed_lessons' => $completedLessonsCount,
                'milestone' => $milestone,
            ]
        );

        $rewardService->flashFirstAchievement($result);

        return back()->with('lesson_chest_reward', [
            'title' => $reward['title'],
            'xp' => $reward['xp'],
            'coins' => $reward['coins'],
            'milestone' => $milestone,
        ]);
    }

    public function show(Request $request, Lesson $lesson, ProgressService $progressService)
    {
        $user = Auth::user();

        abort_unless((bool) $lesson->is_active, 404);

        $lessonTrack = $lesson->category === 'Travel English'
            ? $progressService->travelLessons()
            : $progressService->mainLessons();

        $progress = $progressService->progressByLesson($user);
        $unlockedLessonIds = $progressService->unlockedLessonIds($lessonTrack, $progress);

        if (!in_array($lesson->id, $unlockedLessonIds, true)) {
            $route = $lesson->category === 'Travel English' ? 'travel.index' : 'dashboard';

            return redirect()
                ->route($route)
                ->with('error', 'Этот урок пока закрыт. Сначала пройди предыдущий урок.');
        }

        $lesson->load(['exercises' => fn ($query) => $query->orderBy('order')]);

        $userLesson = $progressService->ensureLessonProgress($user, $lesson);

        $done = $user->userExercises()
            ->whereIn('exercise_id', $lesson->exercises->pluck('id'))
            ->get()
            ->keyBy('exercise_id');

        $selectedExerciseId = (int) $request->query('exercise', 0);
        $repeatMode = false;
        $currentExercise = null;

        if ($userLesson->is_completed && ($request->boolean('repeat') || $selectedExerciseId > 0)) {
            $repeatMode = true;
            $currentExercise = $selectedExerciseId > 0
                ? $lesson->exercises->firstWhere('id', $selectedExerciseId)
                : $lesson->exercises->first();
        }

        if (!$currentExercise) {
            $currentExercise = $lesson->exercises->first(function ($exercise) use ($done) {
                return !($done->get($exercise->id)?->is_correct ?? false);
            });
        }

        $currentIndex = $currentExercise
            ? $lesson->exercises->search(fn ($exercise) => $exercise->id === $currentExercise->id) + 1
            : $lesson->exercises->count();

        $theoryBlocks = $this->prepareTheoryBlocks($lesson);
        $nextLesson = $userLesson->is_completed ? $progressService->nextLessonAfter($lesson) : null;

        return view('lessons.show', compact(
            'lesson',
            'userLesson',
            'done',
            'user',
            'currentExercise',
            'currentIndex',
            'theoryBlocks',
            'repeatMode',
            'nextLesson'
        ));
    }

    private function prepareTheoryBlocks(Lesson $lesson): array
    {
        $theory = trim((string) $lesson->theory);

        if ($theory === '') {
            return [];
        }

        return collect(preg_split('/\R{2,}/u', $theory))
            ->map(fn ($block) => trim($block))
            ->filter()
            ->values()
            ->all();
    }
}