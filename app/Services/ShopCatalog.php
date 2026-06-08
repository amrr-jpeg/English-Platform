<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ShopCatalog
{
    public function all(): Collection
    {
        return collect([
            ['key' => 'hat_crown', 'name' => 'Корона', 'description' => 'Для настоящего короля английского.', 'type' => 'hat', 'price' => 40, 'icon' => '👑'],
            ['key' => 'hat_cap', 'name' => 'Кепка', 'description' => 'Стильный образ для учёбы.', 'type' => 'hat', 'price' => 25, 'icon' => '🧢'],
            ['key' => 'hat_grad', 'name' => 'Шапочка выпускника', 'description' => 'Идеально для дипломного проекта.', 'type' => 'hat', 'price' => 50, 'icon' => '🎓'],
            ['key' => 'acc_glasses', 'name' => 'Очки', 'description' => 'Добавляют +100 к умному виду.', 'type' => 'accessory', 'price' => 35, 'icon' => '😎'],
            ['key' => 'acc_star', 'name' => 'Звезда', 'description' => 'Покажи, что ты лучший ученик.', 'type' => 'accessory', 'price' => 30, 'icon' => '⭐'],
            ['key' => 'acc_fire', 'name' => 'Огонёк', 'description' => 'Для тех, кто держит серию.', 'type' => 'accessory', 'price' => 45, 'icon' => '🔥'],
            ['key' => 'effect_sparkles', 'name' => 'Сияние', 'description' => 'Вокруг персонажа появляются искры.', 'type' => 'effect', 'price' => 60, 'icon' => '✨'],
            ['key' => 'effect_magic', 'name' => 'Магия', 'description' => 'Волшебное свечение вокруг персонажа.', 'type' => 'effect', 'price' => 70, 'icon' => '🔮'],
            ['key' => 'effect_fire', 'name' => 'Огненная аура', 'description' => 'Максимально эпичный эффект.', 'type' => 'effect', 'price' => 80, 'icon' => '🔥'],
            ['key' => 'effect_stars', 'name' => 'Звёздный след', 'description' => 'Красивые звёзды вокруг персонажа.', 'type' => 'effect', 'price' => 90, 'icon' => '🌟'],
            ['key' => 'bg_space', 'name' => 'Космос', 'description' => 'Профиль с космическим фоном.', 'type' => 'background', 'price' => 70, 'icon' => '🌌'],
            ['key' => 'bg_sunset', 'name' => 'Закат', 'description' => 'Тёплый фон профиля.', 'type' => 'background', 'price' => 70, 'icon' => '🌅'],
            ['key' => 'bg_forest', 'name' => 'Лес', 'description' => 'Спокойный зелёный фон.', 'type' => 'background', 'price' => 70, 'icon' => '🌲'],
            ['key' => 'frame_gold', 'name' => 'Золотая рамка', 'description' => 'Премиальная рамка персонажа.', 'type' => 'frame', 'price' => 85, 'icon' => '🟨'],
            ['key' => 'frame_neon', 'name' => 'Неоновая рамка', 'description' => 'Яркая светящаяся рамка.', 'type' => 'frame', 'price' => 95, 'icon' => '💠'],
            ['key' => 'frame_rainbow', 'name' => 'Радужная рамка', 'description' => 'Самая заметная рамка.', 'type' => 'frame', 'price' => 110, 'icon' => '🌈'],
        ]);
    }

    public function grouped(): Collection
    {
        return $this->all()->groupBy('type');
    }

    public function find(string $key): ?array
    {
        return $this->all()->firstWhere('key', $key);
    }

    public function equippedByUser($user): array
    {
        return [
            'hat' => $this->find((string) $user->equipped_hat),
            'accessory' => $this->find((string) $user->equipped_accessory),
            'effect' => $this->find((string) $user->equipped_effect),
            'background' => $this->find((string) $user->profile_background),
            'frame' => $this->find((string) $user->profile_frame),
        ];
    }
}
