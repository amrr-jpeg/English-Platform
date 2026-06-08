<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Services\AchievementService;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index(AchievementService $achievementService)
    {
        $user = Auth::user();

        $achievementService->check($user);

        $achievements = Achievement::orderBy('id')->get();

        $unlockedIds = UserAchievement::where('user_id', $user->id)
            ->pluck('achievement_id')
            ->toArray();

        return view('achievements', compact('achievements', 'unlockedIds'));
    }
}