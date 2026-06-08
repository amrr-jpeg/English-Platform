<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'key' => 'first_steps',
                'title' => 'Первые шаги',
                'description' => 'Получи первые 10 XP.',
                'icon' => '👣',
                'reward_xp' => 5,
                'reward_coins' => 3,
            ],
            [
                'key' => 'student',
                'title' => 'Старательный ученик',
                'description' => 'Накопи 100 XP.',
                'icon' => '📚',
                'reward_xp' => 10,
                'reward_coins' => 5,
            ],
            [
                'key' => 'master',
                'title' => 'Мастер английского',
                'description' => 'Накопи 300 XP.',
                'icon' => '🧠',
                'reward_xp' => 20,
                'reward_coins' => 10,
            ],
            [
                'key' => 'rich',
                'title' => 'Копилка',
                'description' => 'Накопи 50 монет.',
                'icon' => '🪙',
                'reward_xp' => 10,
                'reward_coins' => 5,
            ],
            [
                'key' => 'streak_3',
                'title' => 'Три дня подряд',
                'description' => 'Получи серию 3 дня.',
                'icon' => '🔥',
                'reward_xp' => 15,
                'reward_coins' => 8,
            ],
            [
                'key' => 'streak_7',
                'title' => 'Неделя без пропусков',
                'description' => 'Получи серию 7 дней.',
                'icon' => '⚡',
                'reward_xp' => 30,
                'reward_coins' => 15,
            ],
            [
                'key' => 'first_lesson',
                'title' => 'Первый урок',
                'description' => 'Заверши первый урок.',
                'icon' => '✅',
                'reward_xp' => 10,
                'reward_coins' => 5,
            ],
            [
                'key' => 'five_lessons',
                'title' => 'Уверенный старт',
                'description' => 'Заверши 5 уроков.',
                'icon' => '🚀',
                'reward_xp' => 25,
                'reward_coins' => 12,
            ],
            [
                'key' => 'ten_lessons',
                'title' => 'Большой путь',
                'description' => 'Заверши 10 уроков.',
                'icon' => '🌟',
                'reward_xp' => 50,
                'reward_coins' => 25,
            ],
            [
                'key' => 'first_skin',
                'title' => 'Новый стиль',
                'description' => 'Купи первый стиль персонажа.',
                'icon' => '🎨',
                'reward_xp' => 15,
                'reward_coins' => 5,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['key' => $achievement['key']],
                $achievement
            );
        }
    }
}