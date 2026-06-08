<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\UserLesson;
use App\Models\UserSkin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function check(User $user): array
    {
        $unlocked = [];

        $completedLessons = class_exists(UserLesson::class)
            ? UserLesson::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count()
            : 0;

        $ownedSkins = class_exists(UserSkin::class)
            ? UserSkin::where('user_id', $user->id)->count()
            : 0;

        $rules = [
            'first_steps' => $user->xp >= 10,
            'student' => $user->xp >= 100,
            'master' => $user->xp >= 300,
            'rich' => $user->coins >= 50,
            'streak_3' => $user->streak >= 3,
            'streak_7' => $user->streak >= 7,
            'first_lesson' => $completedLessons >= 1,
            'five_lessons' => $completedLessons >= 5,
            'ten_lessons' => $completedLessons >= 10,
            'first_skin' => $ownedSkins >= 2,
        ];

        foreach ($rules as $key => $condition) {
            if (!$condition) {
                continue;
            }

            $achievement = Achievement::where('key', $key)->first();

            if (!$achievement) {
                continue;
            }

            $alreadyUnlocked = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->exists();

            if ($alreadyUnlocked) {
                continue;
            }

            DB::transaction(function () use ($user, $achievement, &$unlocked) {
                UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                ]);

                $user->xp += $achievement->reward_xp;
                $user->coins += $achievement->reward_coins;
                $user->level = max(1, intdiv($user->xp, 100) + 1);
                $user->save();

                $unlocked[] = $achievement;
            });
        }

        return $unlocked;
    }
}