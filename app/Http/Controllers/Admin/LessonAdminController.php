<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonAdminController extends Controller
{
    public function index()
    {
        $lessons = Lesson::withCount('exercises')->orderBy('order')->get();

        return view('admin.lessons.index', compact('lessons'));
    }

    public function create()
    {
        return view('admin.lessons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $lesson = Lesson::create([
            'order' => $data['order'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.exercises', $lesson)
            ->with('success', 'Урок создан ✅ Теперь добавь упражнения.');
    }

    public function exercises(Lesson $lesson)
    {
        $lesson->load(['exercises' => fn ($q) => $q->orderBy('order')]);

        return view('admin.exercises.index', compact('lesson'));
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $lesson->update([
            'order' => $data['order'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Урок обновлён ✅');
    }

    public function storeExercise(Request $request, Lesson $lesson)
    {
        $prepared = $this->prepareExerciseData($request);

        if ($prepared['error']) {
            return back()->with('error', $prepared['error'])->withInput();
        }

        Exercise::create(array_merge($prepared['data'], [
            'lesson_id' => $lesson->id,
        ]));

        return back()->with('success', 'Упражнение добавлено ✅');
    }

    public function updateExercise(Request $request, Exercise $exercise)
    {
        $prepared = $this->prepareExerciseData($request);

        if ($prepared['error']) {
            return back()->with('error', $prepared['error'])->withInput();
        }

        $exercise->update($prepared['data']);

        return back()->with('success', 'Упражнение обновлено ✅');
    }

    public function deleteExercise(Exercise $exercise)
    {
        $exercise->delete();

        return back()->with('success', 'Упражнение удалено 🗑️');
    }

    public function preview(Lesson $lesson)
    {
        $lesson->load(['exercises' => fn ($q) => $q->orderBy('order')]);

        return view('admin.lessons.preview', compact('lesson'));
    }

    private function prepareExerciseData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:choice,input,scramble,pairs,syllables,drag_word,drag_sentence,flashcards,listening'],
            'question' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:1'],
            'xp_reward' => ['required', 'integer', 'min:0'],
            'coin_reward' => ['required', 'integer', 'min:0'],

            'answer' => ['nullable', 'string', 'max:1000'],
            'options_text' => ['nullable', 'string'],
            'chips_text' => ['nullable', 'string'],
            'pairs_text' => ['nullable', 'string'],
            'cards_text' => ['nullable', 'string'],
            'listening_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $type = $data['type'];
        $answer = trim((string) ($data['answer'] ?? ''));

        $options = null;
        $extraData = null;

        if ($type === 'choice') {
            $options = $this->lines($data['options_text'] ?? '');

            if (count($options) < 2) {
                return ['error' => 'Для выбора нужно минимум 2 варианта ответа.', 'data' => []];
            }

            if ($answer === '') {
                return ['error' => 'Для выбора нужно указать правильный ответ.', 'data' => []];
            }
        }

        if ($type === 'input') {
            if ($answer === '') {
                return ['error' => 'Для ввода нужно указать правильный ответ.', 'data' => []];
            }
        }

        if ($type === 'scramble') {
            if ($answer === '') {
                return ['error' => 'Для scramble нужно указать правильный ответ.', 'data' => []];
            }

            $letters = $this->chips($data['chips_text'] ?? '');

            if (count($letters) === 0) {
                $letters = $this->splitLetters($answer);
            }

            $extraData = [
                'letters' => $letters,
            ];
        }

        if ($type === 'drag_word') {
            if ($answer === '') {
                return ['error' => 'Для drag_word нужно указать правильное слово.', 'data' => []];
            }

            $letters = $this->chips($data['chips_text'] ?? '');

            if (count($letters) === 0) {
                $letters = $this->splitLetters($answer);
            }

            $extraData = [
                'letters' => $letters,
                'answer' => $answer,
            ];
        }

        if ($type === 'drag_sentence') {
            if ($answer === '') {
                return ['error' => 'Для drag_sentence нужно указать правильное предложение.', 'data' => []];
            }

            $words = $this->chips($data['chips_text'] ?? '');

            if (count($words) === 0) {
                $words = preg_split('/\s+/u', trim($answer));
                $words = array_values(array_filter($words));
            }

            $extraData = [
                'words' => $words,
                'answer' => $answer,
            ];
        }

        if ($type === 'syllables') {
            if ($answer === '') {
                return ['error' => 'Для syllables нужно указать правильное слово.', 'data' => []];
            }

            $syllables = $this->chips($data['chips_text'] ?? '');

            if (count($syllables) === 0) {
                return ['error' => 'Для syllables нужно указать слоги через пробел или запятую.', 'data' => []];
            }

            $extraData = [
                'syllables' => $syllables,
                'answer' => $answer,
            ];
        }

        if ($type === 'pairs') {
            $parsed = $this->pairs($data['pairs_text'] ?? '');

            if (count($parsed['pairs']) === 0) {
                return ['error' => 'Для pairs нужно указать пары в формате Cat=Кот.', 'data' => []];
            }

            $extraData = $parsed;
            $answer = 'pairs';
        }

        if ($type === 'flashcards') {
            $cards = $this->cards($data['cards_text'] ?? '');

            if (count($cards) === 0) {
                return ['error' => 'Для flashcards нужно указать карточки в формате Cat=Кот.', 'data' => []];
            }

            $extraData = [
                'cards' => $cards,
            ];

            $answer = 'done';
        }

        if ($type === 'listening') {
            $listeningText = trim((string) ($data['listening_text'] ?? ''));

            if ($listeningText === '') {
                return ['error' => 'Для аудирования нужно указать текст для озвучивания.', 'data' => []];
            }

            if ($answer === '') {
                return ['error' => 'Для аудирования нужно указать правильный ответ.', 'data' => []];
            }

            $listeningOptions = $this->lines($data['options_text'] ?? '');

            if (count($listeningOptions) >= 2) {
                $options = $listeningOptions;
            }

            $extraData = [
                'listening_text' => $listeningText,
                'voice_lang' => 'en-US',
            ];
        }

        return [
            'error' => null,
            'data' => [
                'type' => $type,
                'question' => $data['question'],
                'order' => $data['order'],
                'xp_reward' => $data['xp_reward'],
                'coin_reward' => $data['coin_reward'],
                'answer' => $answer,
                'options' => $options,
                'data' => $extraData,
            ],
        ];
    }

    private function lines(string $value): array
    {
        $lines = preg_split("/\r\n|\n|\r/u", trim($value));

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function chips(string $value): array
    {
        $items = preg_split('/[\s,;]+/u', trim($value));

        return array_values(array_filter(array_map('trim', $items)));
    }

    private function splitLetters(string $value): array
    {
        $value = preg_replace('/\s+/u', '', $value);

        return preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function pairs(string $value): array
    {
        $lines = $this->lines($value);

        $pairs = [];
        $rightOptions = [];
        $truePairs = [];

        foreach ($lines as $line) {
            if (!str_contains($line, '=')) {
                continue;
            }

            [$left, $right] = array_map('trim', explode('=', $line, 2));

            if ($left === '' || $right === '') {
                continue;
            }

            $pairs[] = [
                'left' => $left,
            ];

            $rightOptions[] = $right;
            $truePairs[$left] = $right;
        }

        shuffle($rightOptions);

        return [
            'pairs' => $pairs,
            'right_options' => $rightOptions,
            'true_pairs' => $truePairs,
        ];
    }

    private function cards(string $value): array
    {
        $lines = $this->lines($value);

        $cards = [];

        foreach ($lines as $line) {
            if (!str_contains($line, '=')) {
                continue;
            }

            [$front, $back] = array_map('trim', explode('=', $line, 2));

            if ($front === '' || $back === '') {
                continue;
            }

            $cards[] = [
                'front' => $front,
                'back' => $back,
            ];
        }

        return $cards;
    }

    public static function exerciseToFormText(Exercise $exercise, string $field): string
    {
        if ($field === 'options') {
            return implode("\n", $exercise->options ?? []);
        }

        if ($field === 'chips') {
            if ($exercise->type === 'drag_word') {
                return implode(' ', $exercise->data['letters'] ?? []);
            }

            if ($exercise->type === 'drag_sentence') {
                return implode(' ', $exercise->data['words'] ?? []);
            }

            if ($exercise->type === 'syllables') {
                return implode(' ', $exercise->data['syllables'] ?? []);
            }

            if ($exercise->type === 'scramble') {
                return implode(' ', $exercise->data['letters'] ?? []);
            }
        }

        if ($field === 'pairs') {
            $truePairs = $exercise->data['true_pairs'] ?? [];
            $rows = [];

            foreach ($truePairs as $left => $right) {
                $rows[] = $left . '=' . $right;
            }

            return implode("\n", $rows);
        }

        if ($field === 'listening_text') {
            return (string) ($exercise->data['listening_text'] ?? '');
        }

        if ($field === 'cards') {
            $cards = $exercise->data['cards'] ?? [];
            $rows = [];

            foreach ($cards as $card) {
                $rows[] = ($card['front'] ?? '') . '=' . ($card['back'] ?? '');
            }

            return implode("\n", $rows);
        }

        return '';
    }
}