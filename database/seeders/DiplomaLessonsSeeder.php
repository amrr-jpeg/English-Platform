<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class DiplomaLessonsSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = [
            ['Animals', 'Животные', 'easy', ['cat' => 'кот', 'dog' => 'собака', 'bird' => 'птица'], ['I have a cat' => 'У меня есть кот']],
            ['Food', 'Еда', 'easy', ['apple' => 'яблоко', 'bread' => 'хлеб', 'milk' => 'молоко'], ['I like milk' => 'Я люблю молоко']],
            ['School', 'Школа', 'easy', ['book' => 'книга', 'pen' => 'ручка', 'desk' => 'парта'], ['This is my book' => 'Это моя книга']],
            ['Colors', 'Цвета', 'easy', ['red' => 'красный', 'blue' => 'синий', 'green' => 'зелёный'], ['The apple is red' => 'Яблоко красное']],
            ['Family', 'Семья', 'easy', ['mother' => 'мама', 'father' => 'папа', 'sister' => 'сестра'], ['My mother is kind' => 'Моя мама добрая']],
            ['Numbers', 'Числа', 'easy', ['one' => 'один', 'two' => 'два', 'three' => 'три'], ['I have three books' => 'У меня есть три книги']],
            ['House', 'Дом', 'easy', ['house' => 'дом', 'room' => 'комната', 'window' => 'окно'], ['This is my room' => 'Это моя комната']],
            ['Weather', 'Погода', 'easy', ['sun' => 'солнце', 'rain' => 'дождь', 'snow' => 'снег'], ['The sun is bright' => 'Солнце яркое']],
            ['Body', 'Тело', 'easy', ['head' => 'голова', 'hand' => 'рука', 'eye' => 'глаз'], ['My hand is clean' => 'Моя рука чистая']],
            ['Daily actions', 'Ежедневные действия', 'easy', ['run' => 'бегать', 'read' => 'читать', 'play' => 'играть'], ['I read a book' => 'Я читаю книгу']],
            ['Professions', 'Профессии', 'medium', ['teacher' => 'учитель', 'doctor' => 'доктор', 'driver' => 'водитель'], ['My teacher is nice' => 'Мой учитель хороший']],
            ['City', 'Город', 'medium', ['street' => 'улица', 'shop' => 'магазин', 'park' => 'парк'], ['We go to the park' => 'Мы идём в парк']],
            ['Transport', 'Транспорт', 'medium', ['car' => 'машина', 'bus' => 'автобус', 'train' => 'поезд'], ['The bus is big' => 'Автобус большой']],
            ['Time', 'Время', 'medium', ['morning' => 'утро', 'evening' => 'вечер', 'today' => 'сегодня'], ['Today is a good day' => 'Сегодня хороший день']],
            ['Hobbies', 'Хобби', 'medium', ['music' => 'музыка', 'drawing' => 'рисование', 'sport' => 'спорт'], ['I like music' => 'Я люблю музыку']],
            ['Nature', 'Природа', 'medium', ['tree' => 'дерево', 'river' => 'река', 'mountain' => 'гора'], ['The tree is green' => 'Дерево зелёное']],
            ['Clothes', 'Одежда', 'medium', ['shirt' => 'рубашка', 'shoes' => 'обувь', 'hat' => 'шапка'], ['My hat is blue' => 'Моя шапка синяя']],
            ['Emotions', 'Эмоции', 'medium', ['happy' => 'счастливый', 'sad' => 'грустный', 'angry' => 'злой'], ['I am happy today' => 'Я сегодня счастлив']],
            ['Questions', 'Вопросы', 'medium', ['what' => 'что', 'where' => 'где', 'why' => 'почему'], ['Where is my book' => 'Где моя книга']],
            ['Sentences', 'Предложения', 'medium', ['I like English' => 'Я люблю английский', 'She reads a book' => 'Она читает книгу', 'We go to school' => 'Мы ходим в школу'], []],
            ['Knowledge', 'Знания', 'hard', ['knowledge' => 'знание', 'education' => 'образование', 'learning' => 'обучение'], ['Knowledge helps me' => 'Знания помогают мне']],
            ['Environment', 'Окружающая среда', 'hard', ['environment' => 'окружающая среда', 'pollution' => 'загрязнение', 'nature' => 'природа'], ['Nature is important' => 'Природа важна']],
            ['Technology', 'Технологии', 'hard', ['technology' => 'технология', 'computer' => 'компьютер', 'internet' => 'интернет'], ['Technology helps people' => 'Технологии помогают людям']],
            ['Responsibility', 'Ответственность', 'hard', ['responsibility' => 'ответственность', 'choice' => 'выбор', 'result' => 'результат'], ['Responsibility is important' => 'Ответственность важна']],
            ['Achievements', 'Достижения', 'hard', ['achievement' => 'достижение', 'success' => 'успех', 'goal' => 'цель'], ['Success needs practice' => 'Успех требует практики']],
            ['Development', 'Развитие', 'hard', ['development' => 'развитие', 'improvement' => 'улучшение', 'progress' => 'прогресс'], ['Progress takes time' => 'Прогресс требует времени']],
            ['Communication', 'Коммуникация', 'hard', ['communication' => 'общение', 'message' => 'сообщение', 'conversation' => 'разговор'], ['Communication helps people' => 'Общение помогает людям']],
            ['Opportunities', 'Возможности', 'hard', ['opportunity' => 'возможность', 'future' => 'будущее', 'career' => 'карьера'], ['English opens opportunities' => 'Английский открывает возможности']],
            ['Safety', 'Безопасность', 'hard', ['safety' => 'безопасность', 'danger' => 'опасность', 'protection' => 'защита'], ['Safety is very important' => 'Безопасность очень важна']],
            ['Final practice', 'Итоговая практика', 'hard', ['Learning English helps me develop' => 'Изучение английского помогает мне развиваться', 'Knowledge opens new opportunities' => 'Знания открывают новые возможности', 'Responsibility is important for success' => 'Ответственность важна для успеха'], []],
        ];

        foreach ($lessons as $index => $item) {
            [$title, $description, $level, $words, $sentences] = $item;
            $order = $index + 1;

            $lesson = Lesson::updateOrCreate(
                ['order' => $order],
                [
                    'title' => 'Урок ' . $order . ': ' . $title,
                    'description' => $description,
                    'level' => $level,
                    'category' => 'Дипломный курс',
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
                    'type' => 'scramble',
                    'question' => 'Собери английскую фразу: ' . $ru,
                    'options' => null,
                    'answer' => $en,
                    'order' => $exerciseOrder++,
                    'xp_reward' => 15,
                    'coin_reward' => 5,
                    'data' => ['letters' => preg_split('//u', str_replace(' ', '', $en), -1, PREG_SPLIT_NO_EMPTY)],
                ]);
            }
        }
    }

    private function theory(string $topic, array $words, array $sentences): string
    {
        $lines = [];
        $lines[] = 'Цель урока: изучить тему «' . $topic . '», запомнить новые слова и научиться использовать их в простых фразах.';
        $lines[] = 'Словарь урока:';

        foreach ($words as $en => $ru) {
            $lines[] = $en . ' — ' . $ru;
        }

        if (!empty($sentences)) {
            $lines[] = '';
            $lines[] = 'Примеры фраз:';
            foreach ($sentences as $en => $ru) {
                $lines[] = $en . ' — ' . $ru . '.';
            }
        }

        $lines[] = '';
        $lines[] = 'Совет: сначала прочитай слова вслух, затем закрой перевод и попробуй вспомнить значение самостоятельно.';

        return implode(PHP_EOL, $lines);
    }

    private function options(string $correct): array
    {
        $pool = ['дом', 'школа', 'яблоко', 'книга', 'мама', 'парк', 'машина', 'солнце', 'учитель', 'город', 'друг', 'вода'];

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
