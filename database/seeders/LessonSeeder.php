<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Exercise;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = [
            [
                'order' => 1,
                'title' => 'Приветствие и знакомство',
                'description' => 'Научись здороваться, прощаться и представляться на английском.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "Hello"?',
                        'options' => ['Привет', 'Пока', 'Спасибо', 'Пожалуйста'],
                        'answer' => 'Привет',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: Привет',
                        'answer' => 'Hello',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как сказать "Меня зовут..."?',
                        'options' => ['My name is...', 'I am old...', 'Good night', 'Thank you'],
                        'answer' => 'My name is...',
                    ],
                ],
            ],
            [
                'order' => 2,
                'title' => 'Цвета',
                'description' => 'Изучи основные цвета на английском языке.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "red"?',
                        'options' => ['Красный', 'Синий', 'Зелёный', 'Жёлтый'],
                        'answer' => 'Красный',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: синий',
                        'answer' => 'blue',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Какой цвет означает "green"?',
                        'options' => ['Зелёный', 'Чёрный', 'Белый', 'Оранжевый'],
                        'answer' => 'Зелёный',
                    ],
                ],
            ],
            [
                'order' => 3,
                'title' => 'Числа от 1 до 10',
                'description' => 'Научись понимать и писать простые числа на английском.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "one"?',
                        'options' => ['Один', 'Два', 'Три', 'Четыре'],
                        'answer' => 'Один',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: пять',
                        'answer' => 'five',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "ten"?',
                        'options' => ['Десять', 'Семь', 'Шесть', 'Восемь'],
                        'answer' => 'Десять',
                    ],
                ],
            ],
            [
                'order' => 4,
                'title' => 'Животные',
                'description' => 'Запомни названия популярных животных.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "cat"?',
                        'options' => ['Кошка', 'Собака', 'Птица', 'Лошадь'],
                        'answer' => 'Кошка',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: собака',
                        'answer' => 'dog',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "bird"?',
                        'options' => ['Птица', 'Рыба', 'Кролик', 'Мышь'],
                        'answer' => 'Птица',
                    ],
                ],
            ],
            [
                'order' => 5,
                'title' => 'Семья',
                'description' => 'Изучи слова по теме семья.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "mother"?',
                        'options' => ['Мама', 'Папа', 'Брат', 'Сестра'],
                        'answer' => 'Мама',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: папа',
                        'answer' => 'father',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "sister"?',
                        'options' => ['Сестра', 'Брат', 'Бабушка', 'Дедушка'],
                        'answer' => 'Сестра',
                    ],
                ],
            ],
            [
                'order' => 6,
                'title' => 'Еда',
                'description' => 'Познакомься с простыми словами по теме еда.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "apple"?',
                        'options' => ['Яблоко', 'Банан', 'Хлеб', 'Молоко'],
                        'answer' => 'Яблоко',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: вода',
                        'answer' => 'water',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "bread"?',
                        'options' => ['Хлеб', 'Сыр', 'Сок', 'Рис'],
                        'answer' => 'Хлеб',
                    ],
                ],
            ],
            [
                'order' => 7,
                'title' => 'Школа',
                'description' => 'Выучи слова, связанные со школой и учебой.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "book"?',
                        'options' => ['Книга', 'Ручка', 'Стол', 'Доска'],
                        'answer' => 'Книга',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: ручка',
                        'answer' => 'pen',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "teacher"?',
                        'options' => ['Учитель', 'Ученик', 'Друг', 'Доктор'],
                        'answer' => 'Учитель',
                    ],
                ],
            ],
            [
                'order' => 8,
                'title' => 'Глаголы действия',
                'description' => 'Изучи базовые английские глаголы.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "run"?',
                        'options' => ['Бежать', 'Спать', 'Есть', 'Читать'],
                        'answer' => 'Бежать',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: читать',
                        'answer' => 'read',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "write"?',
                        'options' => ['Писать', 'Смотреть', 'Играть', 'Пить'],
                        'answer' => 'Писать',
                    ],
                ],
            ],
            [
                'order' => 9,
                'title' => 'Простые предложения',
                'description' => 'Научись составлять короткие английские предложения.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как сказать "Я ученик"?',
                        'options' => ['I am a student', 'You are a student', 'He is a teacher', 'I have a book'],
                        'answer' => 'I am a student',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: Я люблю английский',
                        'answer' => 'I like English',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "She is happy"?',
                        'options' => ['Она счастлива', 'Он счастлив', 'Они дома', 'Я устал'],
                        'answer' => 'Она счастлива',
                    ],
                ],
            ],
            [
                'order' => 10,
                'title' => 'Итоговое повторение',
                'description' => 'Повтори слова и фразы из предыдущих уроков.',
                'exercises' => [
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "Thank you"?',
                        'options' => ['Спасибо', 'Пожалуйста', 'Привет', 'Пока'],
                        'answer' => 'Спасибо',
                    ],
                    [
                        'type' => 'input',
                        'question' => 'Напиши по-английски: книга',
                        'answer' => 'book',
                    ],
                    [
                        'type' => 'choice',
                        'question' => 'Как переводится "dog"?',
                        'options' => ['Собака', 'Кошка', 'Птица', 'Рыба'],
                        'answer' => 'Собака',
                    ],
                ],
            ],
        ];

        foreach ($lessons as $lessonData) {
            $exercises = $lessonData['exercises'];
            unset($lessonData['exercises']);

            $lesson = Lesson::updateOrCreate(
                ['order' => $lessonData['order']],
                [
                    'title' => $lessonData['title'],
                    'description' => $lessonData['description'],
                    'is_active' => true,
                ]
            );

            foreach ($exercises as $index => $exerciseData) {
                Exercise::updateOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                        'order' => $index + 1,
                    ],
                    [
                        'type' => $exerciseData['type'],
                        'question' => $exerciseData['question'],
                        'options' => $exerciseData['options'] ?? null,
                        'answer' => $exerciseData['answer'],
                        'xp_reward' => 10,
                        'coin_reward' => 3,
                        'data' => null,
                    ]
                );
            }
        }
    }
}