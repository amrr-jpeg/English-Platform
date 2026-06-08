<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin1@local.test'],
            [
                'name' => 'admin1',
                'password' => Hash::make('admin1'),
                'is_admin' => true,

                // на всякий случай игровые поля (если есть)
                'xp' => 0,
                'level' => 1,
                'coins' => 0,
                'skin' => 'blue',
            ]
        );
    }
}
