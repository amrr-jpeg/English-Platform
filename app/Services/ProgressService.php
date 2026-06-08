<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserExercise;
use App\Models\UserLesson;
use Illuminate\Support\Collection;

class ProgressService
{
    public function mainLessons()
    {
        return Lesson::query()
            ->mainCourse()
            ->where('is_active', true)
            ->withCount('exercises')
            ->orderBy('order')
            ->get();
    }

    public function travelLessons()
    {
        return Lesson::query()
            ->travelCourse()
            ->where('is_active', true)
            ->withCount('exercises')
            ->orderBy('order')
            ->get();
    }

    public function progressByLesson(User $user): Collection
    {
        return UserLesson::where('user_id', $user->id)
            ->get()
            ->keyBy('lesson_id');
    }

    public function unlockedLessonIds($lessons, Collection $progress): array
    {
        $unlockedLessonIds = [];

        foreach ($lessons as $index => $lesson) {
            if ($index === 0) {
                $unlockedLessonIds[] = $lesson->id;
                continue;
            }

            $previousLesson = $lessons[$index - 1];
            $previousProgress = $progress->get($previousLesson->id);

            if ($previousProgress && $previousProgress->is_completed) {
                $unlockedLessonIds[] = $lesson->id;
            }
        }

        return $unlockedLessonIds;
    }

    public function nextLessonAfter(Lesson $lesson): ?Lesson
    {
        $query = Lesson::query()
            ->where('is_active', true)
            ->where('order', '>', $lesson->order)
            ->orderBy('order');

        if ($lesson->category === 'Travel English') {
            $query->travelCourse();
        } else {
            $query->mainCourse();
        }

        return $query->first();
    }

    public function ensureLessonProgress(User $user, Lesson $lesson): UserLesson
    {
        $total = $lesson->exercises()->count();

        $userLesson = UserLesson::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['completed_exercises' => 0, 'total_exercises' => $total, 'is_completed' => false]
        );

        $this->refreshLessonProgress($user, $lesson->id);

        return $userLesson->fresh();
    }

    public function refreshLessonProgress(User $user, int $lessonId): void
    {
        $userLesson = UserLesson::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            ['completed_exercises' => 0, 'total_exercises' => 0, 'is_completed' => false]
        );

        $correctCount = UserExercise::where('user_id', $user->id)
            ->where('is_correct', true)
            ->whereHas('exercise', function ($q) use ($lessonId) {
                $q->where('lesson_id', $lessonId);
            })
            ->count();

        $total = Exercise::where('lesson_id', $lessonId)->count();

        $userLesson->completed_exercises = $correctCount;
        $userLesson->total_exercises = $total;
        $userLesson->is_completed = ($total > 0 && $correctCount >= $total);
        $userLesson->save();
    }

    public function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    public function normalizeSentence(string $value): string
    {
        $value = $this->normalize($value);
        $value = preg_replace('/[.!?,;:]+/u', '', $value);

        return trim($value);
    }
}
