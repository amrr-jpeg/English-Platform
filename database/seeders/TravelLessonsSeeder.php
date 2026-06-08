<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class TravelLessonsSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = [
            ['Travel 1: Airport', 'Фразы и слова для аэропорта.', 'easy', ['airport' => 'аэропорт', 'ticket' => 'билет', 'passport' => 'паспорт', 'luggage' => 'багаж'], ['Where is my gate' => 'Где мой выход на посадку']],
            ['Travel 2: Hotel', 'Заселение в отель и общение на ресепшене.', 'easy', ['hotel' => 'отель', 'reservation' => 'бронь', 'room' => 'номер', 'key' => 'ключ'], ['I have a reservation' => 'У меня есть бронь']],
            ['Travel 3: Restaurant', 'Как заказать еду и попросить счёт.', 'easy', ['menu' => 'меню', 'water' => 'вода', 'bill' => 'счёт', 'table' => 'столик'], ['Can I have some water please' => 'Можно мне воды, пожалуйста']],
            ['Travel 4: Taxi', 'Фразы для поездок на такси.', 'medium', ['taxi' => 'такси', 'address' => 'адрес', 'station' => 'станция', 'price' => 'цена'], ['How much is the taxi' => 'Сколько стоит такси']],
            ['Travel 5: City', 'Ориентация в городе.', 'medium', ['street' => 'улица', 'museum' => 'музей', 'map' => 'карта', 'square' => 'площадь'], ['Where is the museum' => 'Где находится музей']],
            ['Travel 6: Emergency', 'Важные фразы в экстренных ситуациях.', 'hard', ['help' => 'помощь', 'doctor' => 'врач', 'police' => 'полиция', 'lost' => 'потерялся'], ['I need help' => 'Мне нужна помощь']],
        ];

        foreach ($lessons as $index => $item) {
            [$title, $description, $level, $words, $sentences] = $item;
            $order = 100 + $index + 1;

            $lesson = Lesson::updateOrCreate(
                ['order' => $order],
                [
                    'title' => $title,
                    'description' => $description,
                    'level' => $level,
                    'category' => 'Travel English',
                    'theory' => $this->theory($description, $words, $sentences),
                    'is_active' => true,
                ]
            );

            Exercise::where('lesson_id', $lesson->id)->delete();
            $exerciseOrder = 1;

            foreach ($words as $en => $ru) {
                Exercise::create([
                    'lesson_id' => $lesson->id,
                    'type' => 'choice',
                    'question' => 'Как переводится "' . $en . '"?',
                    'options' => $this->options($ru),
                    'answer' => $ru,
                    'order' => $exerciseOrder++,
                    'xp_reward' => 10,
                    'coin_reward' => 3,
                ]);

                Exercise::create([
                    'lesson_id' => $lesson->id,
                    'type' => 'input',
                    'question' => 'Напиши по-английски: ' . $ru,
                    'options' => null,
                    'answer' => $en,
                    'order' => $exerciseOrder++,
                    'xp_reward' => 12,
                    'coin_reward' => 4,
                ]);
            }

            $firstListeningText = array_key_first($words);

            if ($firstListeningText !== null) {
                Exercise::create([
                    'lesson_id' => $lesson->id,
                    'type' => 'listening',
                    'question' => 'Прослушай и напиши английское слово или фразу.',
                    'options' => null,
                    'answer' => $firstListeningText,
                    'order' => $exerciseOrder++,
                    'xp_reward' => 12,
                    'coin_reward' => 4,
                    'data' => [
                        'listening_text' => $firstListeningText,
                        'voice_lang' => 'en-US',
                    ],
                ]);
            }

            foreach ($sentences as $en => $ru) {
                Exercise::create([
                    'lesson_id' => $lesson->id,
                    'type' => 'input',
                    'question' => 'Напиши фразу по-английски: ' . $ru,
                    'options' => null,
                    'answer' => $en,
                    'order' => $exerciseOrder++,
                    'xp_reward' => 15,
                    'coin_reward' => 5,
                ]);
            }
        }
    }

    private function theory(string $topic, array $words, array $sentences): string
    {
        $lines = [];
        $lines[] = 'Цель travel-урока: подготовиться к реальной ситуации — ' . mb_strtolower($topic);
        $lines[] = 'Полезные слова:';

        foreach ($words as $en => $ru) {
            $lines[] = $en . ' — ' . $ru;
        }

        $lines[] = '';
        $lines[] = 'Полезные фразы:';
        foreach ($sentences as $en => $ru) {
            $lines[] = $en . ' — ' . $ru . '.';
        }

        $lines[] = '';
        $lines[] = 'Совет: в путешествии используй короткие вежливые фразы с please. Так тебя легче поймут.';

        return implode(PHP_EOL, $lines);
    }

    private function options(string $correct): array
    {
        $pool = ['дом', 'еда', 'книга', 'аэропорт', 'отель', 'багаж', 'вода', 'адрес', 'карта', 'помощь'];

        return collect($pool)
            ->reject(fn ($item) => $item === $correct)
            ->shuffle()
            ->take(3)
            ->push($correct)
            ->shuffle()
            ->values()
            ->all();
    }
}
