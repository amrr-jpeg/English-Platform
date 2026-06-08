<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\ChestOpen;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Lesson;
use App\Models\RewardLog;
use App\Models\User;
use App\Models\UserLesson;

class AdminStatsController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $lessonsCount = Lesson::count();
        $activeLessonsCount = Lesson::where('is_active', true)->count();
        $exercisesCount = Exercise::count();

        $answersCount = ExerciseAttempt::count();
        $correctAnswersCount = ExerciseAttempt::where('is_correct', true)->count();

        $accuracy = $answersCount > 0 ? round(($correctAnswersCount / $answersCount) * 100) : 0;

        $completedLessonsCount = UserLesson::where('is_completed', true)->count();
        $achievementsCount = Achievement::count();
        $chestsCount = ChestOpen::count();
        $totalXpIssued = RewardLog::sum('xp');
        $totalCoinsIssued = RewardLog::sum('coins');

        $topUsers = User::orderByDesc('xp')->limit(10)->get();

        $popularLessons = Lesson::withCount('userLessons')
            ->orderByDesc('user_lessons_count')
            ->limit(8)
            ->get();

        return view('admin.stats.index', compact(
            'usersCount',
            'lessonsCount',
            'activeLessonsCount',
            'exercisesCount',
            'answersCount',
            'correctAnswersCount',
            'accuracy',
            'completedLessonsCount',
            'achievementsCount',
            'chestsCount',
            'totalXpIssued',
            'totalCoinsIssued',
            'topUsers',
            'popularLessons'
        ));
    }
}
