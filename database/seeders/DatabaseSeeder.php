<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ItemSeeder::class,
            AchievementSeeder::class,
            DiplomaLessonsSeeder::class,
            TravelLessonsSeeder::class,
            AddListeningExercisesSeeder::class,
        ]);
    }
}
