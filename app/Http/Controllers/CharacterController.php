<?php

namespace App\Http\Controllers;

use App\Models\UserSkin;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CharacterController extends Controller
{
    private array $skins = [
        'blue' => [
            'key' => 'blue',
            'name' => 'Синий',
            'price' => 0,
            'emoji' => '😄',
        ],
        'pink' => [
            'key' => 'pink',
            'name' => 'Розовый',
            'price' => 30,
            'emoji' => '😊',
        ],
        'green' => [
            'key' => 'green',
            'name' => 'Зелёный',
            'price' => 30,
            'emoji' => '😎',
        ],
    ];

    public function index()
    {
        $user = Auth::user();

        UserSkin::firstOrCreate([
            'user_id' => $user->id,
            'skin' => 'blue',
        ]);

        if (!empty($user->skin)) {
            UserSkin::firstOrCreate([
                'user_id' => $user->id,
                'skin' => $user->skin,
            ]);
        }

        $skins = array_values($this->skins);

        $ownedSkins = UserSkin::where('user_id', $user->id)
            ->pluck('skin')
            ->toArray();

        return view('character', compact('user', 'skins', 'ownedSkins'));
    }

    public function buySkin(Request $request, AchievementService $achievementService)
    {
        $user = Auth::user();

        $request->validate([
            'skin' => ['required', 'in:blue,pink,green'],
        ]);

        $skinKey = $request->input('skin');
        $skin = $this->skins[$skinKey];
        $price = (int) $skin['price'];

        $alreadyOwned = UserSkin::where('user_id', $user->id)
            ->where('skin', $skinKey)
            ->exists();

        if ($user->skin === $skinKey) {
            return back()->with('info', 'Этот стиль уже выбран ✅');
        }

        if (!$alreadyOwned && $price > 0 && $user->coins < $price) {
            return back()->with('error', 'Не хватает монет 😅');
        }

        DB::transaction(function () use ($user, $skinKey, $price, $alreadyOwned) {
            if (!$alreadyOwned) {
                $user->coins -= $price;

                UserSkin::create([
                    'user_id' => $user->id,
                    'skin' => $skinKey,
                ]);
            }

            $user->skin = $skinKey;
            $user->save();
        });

        $user->refresh();

        $unlockedAchievements = $achievementService->check($user);

        if (count($unlockedAchievements) > 0) {
            session()->flash('achievement_unlocked', [
                'icon' => $unlockedAchievements[0]->icon,
                'title' => $unlockedAchievements[0]->title,
                'description' => $unlockedAchievements[0]->description,
            ]);
        }

        return back()->with(
            'success',
            $alreadyOwned ? 'Стиль выбран ✅' : 'Стиль куплен и выбран ✨'
        );
    }
}