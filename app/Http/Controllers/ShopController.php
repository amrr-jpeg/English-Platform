<?php

namespace App\Http\Controllers;

use App\Models\UserShopItem;
use App\Services\RewardService;
use App\Services\ShopCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function __construct(private ShopCatalog $catalog)
    {
    }

    public function index()
    {
        $user = Auth::user();

        $owned = UserShopItem::where('user_id', $user->id)
            ->pluck('item_key')
            ->toArray();

        $items = $this->catalog->grouped();
        $equippedItems = $this->catalog->equippedByUser($user);

        return view('shop', compact('user', 'items', 'owned', 'equippedItems'));
    }

    public function buy(Request $request, RewardService $rewardService)
    {
        $user = Auth::user();

        $data = $request->validate([
            'item_key' => ['required', 'string'],
        ]);

        $item = $this->catalog->find($data['item_key']);

        if (!$item) {
            return back()->with('error', 'Такого предмета нет.');
        }

        $alreadyOwned = UserShopItem::where('user_id', $user->id)
            ->where('item_key', $item['key'])
            ->exists();

        if ($alreadyOwned) {
            return back()->with('info', 'Этот предмет уже куплен ✅');
        }

        if ($user->coins < $item['price']) {
            return back()->with('error', 'Не хватает монет 😅');
        }

        DB::transaction(function () use ($user, $item) {
            $user->coins -= $item['price'];

            $column = match ($item['type']) {
                'hat' => 'equipped_hat',
                'accessory' => 'equipped_accessory',
                'effect' => 'equipped_effect',
                'background' => 'profile_background',
                'frame' => 'profile_frame',
                default => null,
            };

            if ($column) {
                // Купленный предмет сразу надевается, чтобы пользователь сразу видел изменение.
                $user->{$column} = $item['key'];
            }

            $user->save();

            UserShopItem::create([
                'user_id' => $user->id,
                'item_key' => $item['key'],
            ]);
        });

        $reward = $rewardService->grant(
            $user->fresh(),
            'shop_purchase',
            null,
            0,
            0,
            'Куплен предмет: ' . $item['name'],
            ['item_key' => $item['key'], 'type' => $item['type'], 'price' => $item['price']]
        );

        $rewardService->flashFirstAchievement($reward);

        return back()->with('success', 'Предмет куплен и сразу надет на персонажа ✨');
    }

    public function equip(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'item_key' => ['required', 'string'],
        ]);

        $item = $this->catalog->find($data['item_key']);

        if (!$item) {
            return back()->with('error', 'Такого предмета нет.');
        }

        $owned = UserShopItem::where('user_id', $user->id)
            ->where('item_key', $item['key'])
            ->exists();

        if (!$owned) {
            return back()->with('error', 'Сначала нужно купить этот предмет.');
        }

        $column = match ($item['type']) {
            'hat' => 'equipped_hat',
            'accessory' => 'equipped_accessory',
            'effect' => 'equipped_effect',
            'background' => 'profile_background',
            'frame' => 'profile_frame',
            default => null,
        };

        if (!$column) {
            return back()->with('error', 'Неверный тип предмета.');
        }

        $user->{$column} = $user->{$column} === $item['key'] ? null : $item['key'];
        $user->save();

        return back()->with('success', $user->{$column} ? 'Предмет выбран ✅' : 'Предмет снят');
    }
}
