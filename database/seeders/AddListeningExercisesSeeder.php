<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class AddListeningExercisesSeeder extends Seeder
{
    /**
     * Добавляет задания с аудированием в уже существующие уроки.
     * Сидер безопасно запускать повторно: дубликаты не создаются.
     */
    public function run(): void
    {
        Lesson::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->each(function (Lesson $lesson): void {
                $alreadyExists = Exercise::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('type', 'listening')
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $sourceExercise = Exercise::query()
                    ->where('lesson_id', $lesson->id)
                    ->whereIn('type', ['input', 'scramble', 'drag_word', 'drag_sentence'])
                    ->whereNotNull('answer')
                    ->where('answer', '!=', '')
                    ->orderBy('order')
                    ->first();

                if (!$sourceExercise) {
                    return;
                }

                $text = trim((string) $sourceExercise->answer);

                if ($text === '') {
                    return;
                }

                $nextOrder = ((int) Exercise::where('lesson_id', $lesson->id)->max('order')) + 1;

                Exercise::create([
                    'lesson_id' => $lesson->id,
                    'type' => 'listening',
                    'question' => 'Прослушай и напиши английское слово или фразу.',
                    'options' => null,
                    'answer' => $text,
                    'order' => $nextOrder,
                    'xp_reward' => 12,
                    'coin_reward' => 4,
                    'data' => [
                        'listening_text' => $text,
                        'voice_lang' => 'en-US',
                    ],
                ]);
            });
    }
}
