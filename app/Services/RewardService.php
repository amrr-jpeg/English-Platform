<?php

namespace App\Services;

use App\Models\RewardLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public function __construct(private AchievementService $achievementService)
    {
    }

    public function grant(
        User $user,
        string $source,
        ?int $sourceId,
        int $xp,
        int $coins,
        string $description,
        array $meta = [],
        bool $updateStreak = false
    ): array {
        $xp = max(0, $xp);
        $coins = max(0, $coins);
        $streakChanged = false;

        DB::transaction(function () use ($user, $source, $sourceId, $xp, $coins, $description, $meta, $updateStreak, &$streakChanged) {
            $user->xp = (int) $user->xp + $xp;
            $user->coins = (int) $user->coins + $coins;
            $user->level = max(1, intdiv((int) $user->xp, 100) + 1);

            if ($updateStreak) {
                $streakChanged = $this->updateStreak($user);

                if ($streakChanged && (int) $user->streak > 0 && (int) $user->streak % 3 === 0) {
                    $coins += 5;
                    $user->coins = (int) $user->coins + 5;
                    $meta['streak_bonus'] = 5;
                }
            }

            $user->save();

            RewardLog::create([
                'user_id' => $user->id,
                'source' => $source,
                'source_id' => $sourceId,
                'xp' => $xp,
                'coins' => $coins,
                'description' => $description,
                'meta' => $meta,
            ]);
        });

        $user->refresh();
        $unlockedAchievements = $this->achievementService->check($user);

        return [
            'xp' => $xp,
            'coins' => $coins,
            'streak_changed' => $streakChanged,
            'unlocked_achievements' => $unlockedAchievements,
        ];
    }

    public function flashFirstAchievement(array $rewardResult): void
    {
        $achievements = $rewardResult['unlocked_achievements'] ?? [];

        if (count($achievements) === 0) {
            return;
        }

        session()->flash('achievement_unlocked', [
            'icon' => $achievements[0]->icon,
            'title' => $achievements[0]->title,
            'description' => $achievements[0]->description,
        ]);
    }

    private function updateStreak(User $user): bool
    {
        $today = now()->toDateString();

        if ($user->last_activity_date && $user->last_activity_date->toDateString() === $today) {
            return false;
        }

        $yesterday = now()->subDay()->toDateString();

        if ($user->last_activity_date && $user->last_activity_date->toDateString() === $yesterday) {
            $user->streak = ((int) $user->streak) + 1;
        } else {
            $user->streak = 1;
        }

        $user->best_streak = max((int) $user->best_streak, (int) $user->streak);
        $user->last_activity_date = $today;

        return true;
    }
}
