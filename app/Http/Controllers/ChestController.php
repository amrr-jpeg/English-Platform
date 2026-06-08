<?php

namespace App\Http\Controllers;

use App\Models\ChestOpen;
use App\Services\RewardService;
use Illuminate\Support\Facades\Auth;

class ChestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $openedToday = ChestOpen::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();

        $history = ChestOpen::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('chests.index', compact('openedToday', 'history'));
    }

    public function open(RewardService $rewardService)
    {
        $user = Auth::user();

        $openedToday = ChestOpen::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($openedToday) {
            return back()->with('error', 'Сегодня сундук уже открыт 😅');
        }

        $rewards = [
            ['type' => 'coins', 'xp' => 0, 'coins' => 15, 'amount' => 15, 'label' => '+15 монет 🪙'],
            ['type' => 'coins', 'xp' => 0, 'coins' => 30, 'amount' => 30, 'label' => '+30 монет 🪙'],
            ['type' => 'xp', 'xp' => 25, 'coins' => 0, 'amount' => 25, 'label' => '+25 XP ✨'],
            ['type' => 'xp', 'xp' => 50, 'coins' => 0, 'amount' => 50, 'label' => '+50 XP ✨'],
            ['type' => 'mixed', 'xp' => 20, 'coins' => 20, 'amount' => 40, 'label' => '+20 XP и +20 монет 🎁'],
        ];

        $rewardItem = $rewards[array_rand($rewards)];

        $reward = $rewardService->grant(
            $user,
            'chest',
            null,
            $rewardItem['xp'],
            $rewardItem['coins'],
            'Ежедневный сундук: ' . $rewardItem['label'],
            ['reward_type' => $rewardItem['type']]
        );

        $rewardService->flashFirstAchievement($reward);

        ChestOpen::create([
            'user_id' => $user->id,
            'reward_type' => $rewardItem['type'],
            'reward_amount' => $rewardItem['amount'],
            'reward_label' => $rewardItem['label'],
        ]);

        return back()->with('success', 'Сундук открыт! Награда: ' . $rewardItem['label']);
    }
}
