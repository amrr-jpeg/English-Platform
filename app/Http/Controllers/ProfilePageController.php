<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\ChestOpen;
use App\Models\ExerciseAttempt;
use App\Models\UserAchievement;
use App\Models\UserLesson;
use App\Services\ShopCatalog;
use Illuminate\Support\Facades\Auth;

class ProfilePageController extends Controller
{
    public function index(ShopCatalog $catalog)
    {
        $user = Auth::user();

        $completedLessons = UserLesson::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $totalAnswers = ExerciseAttempt::where('user_id', $user->id)->count();
        $correctAnswers = ExerciseAttempt::where('user_id', $user->id)->where('is_correct', true)->count();
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100) : 0;

        $achievementIds = UserAchievement::where('user_id', $user->id)
            ->pluck('achievement_id')
            ->toArray();

        $achievements = Achievement::whereIn('id', $achievementIds)
            ->limit(6)
            ->get();

        $lastChests = ChestOpen::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $equippedItems = $catalog->equippedByUser($user);

        return view('profile-page.index', compact(
            'user',
            'completedLessons',
            'totalAnswers',
            'correctAnswers',
            'accuracy',
            'achievements',
            'lastChests',
            'equippedItems'
        ));
    }
}
