<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\ContentCourse;
use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagerCourseController extends Controller
{
    public function index()
    {
        $courses = ContentCourse::query()
            ->where('creator_id', auth()->id())
            ->withCount('lessons')
            ->latest()
            ->get();

        return view('manager.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('manager.courses.create', [
            'course' => new ContentCourse(['level' => 'easy', 'is_published' => false]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCourse($request);

        $course = ContentCourse::create([
            'creator_id' => auth()->id(),
            'title' => $data['title'],
            'slug' => ContentCourse::uniqueSlug($data['title']),
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('manager.courses.edit', $course)
            ->with('success', 'Курс создан. Теперь добавь уроки.');
    }

    public function edit(ContentCourse $course)
    {
        $this->authorizeCourse($course);

        $course->load(['lessons.exercises']);

        return view('manager.courses.edit', [
            'course' => $course,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, ContentCourse $course): RedirectResponse
    {
        $this->authorizeCourse($course);
        $data = $this->validateCourse($request);

        $course->update([
            'title' => $data['title'],
            'slug' => $course->slug ?: ContentCourse::uniqueSlug($data['title'], $course->id),
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Курс обновлён.');
    }

    public function destroy(ContentCourse $course): RedirectResponse
    {
        $this->authorizeCourse($course);
        $course->delete();

        return redirect()->route('manager.courses.index')->with('success', 'Курс удалён.');
    }

    public function storeLesson(Request $request, ContentCourse $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'theory' => ['nullable', 'string'],
            'level' => ['required', 'in:easy,medium,hard'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Lesson::create([
            'creator_id' => auth()->id(),
            'content_course_id' => $course->id,
            'order' => $data['order'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => 'Content Manager',
            'level' => $data['level'],
            'theory' => $data['theory'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Урок добавлен.');
    }

    public function updateLesson(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($lesson);

        $data = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'theory' => ['nullable', 'string'],
            'level' => ['required', 'in:easy,medium,hard'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $lesson->update([
            'order' => $data['order'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'theory' => $data['theory'] ?? null,
            'level' => $data['level'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Урок обновлён.');
    }

    public function deleteLesson(Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($lesson);
        $lesson->delete();

        return back()->with('success', 'Урок удалён.');
    }

    public function storeExercise(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($lesson);
        $prepared = $this->prepareExerciseData($request);

        if ($prepared['error']) {
            return back()->with('error', $prepared['error'])->withInput();
        }

        Exercise::create(array_merge($prepared['data'], [
            'lesson_id' => $lesson->id,
        ]));

        return back()->with('success', 'Упражнение добавлено.');
    }

    public function deleteExercise(Exercise $exercise): RedirectResponse
    {
        $exercise->load('lesson');
        $this->authorizeLesson($exercise->lesson);
        $exercise->delete();

        return back()->with('success', 'Упражнение удалено.');
    }

    private function validateCourse(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'level' => ['required', 'in:easy,medium,hard'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }

    private function authorizeCourse(ContentCourse $course): void
    {
        abort_unless(auth()->user()->isAdmin() || $course->creator_id === auth()->id(), 403);
    }

    private function authorizeLesson(?Lesson $lesson): void
    {
        abort_unless($lesson && (auth()->user()->isAdmin() || $lesson->creator_id === auth()->id()), 403);
    }

    private function prepareExerciseData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:choice,input,scramble,pairs,drag_sentence,listening'],
            'question' => ['required', 'string', 'max:1000'],
            'order' => ['required', 'integer', 'min:1'],
            'xp_reward' => ['required', 'integer', 'min:0'],
            'coin_reward' => ['required', 'integer', 'min:0'],
            'answer' => ['nullable', 'string', 'max:2000'],
            'options_text' => ['nullable', 'string', 'max:3000'],
            'pairs_text' => ['nullable', 'string', 'max:5000'],
            'listening_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $type = $data['type'];
        $answer = trim((string) ($data['answer'] ?? ''));
        $options = [];
        $extraData = [];

        if ($type === 'choice') {
            $options = collect(preg_split('/\R/u', (string) ($data['options_text'] ?? '')))
                ->map(fn ($row) => trim($row))
                ->filter()
                ->values()
                ->all();

            if ($answer === '' || count($options) < 2) {
                return ['error' => 'Для теста укажи правильный ответ и минимум 2 варианта ответа.', 'data' => []];
            }

            if (!in_array($answer, $options, true)) {
                $options[] = $answer;
            }
        }

        if (in_array($type, ['input', 'scramble', 'drag_sentence', 'listening'], true) && $answer === '') {
            return ['error' => 'Для этого типа задания нужен правильный ответ.', 'data' => []];
        }

        if ($type === 'pairs') {
            $pairs = [];
            foreach (preg_split('/\R/u', (string) ($data['pairs_text'] ?? '')) as $row) {
                $row = trim($row);
                if ($row === '' || !str_contains($row, '=')) {
                    continue;
                }
                [$left, $right] = array_map('trim', explode('=', $row, 2));
                if ($left !== '' && $right !== '') {
                    $pairs[] = ['left' => $left, 'right' => $right];
                }
            }

            if (count($pairs) < 2) {
                return ['error' => 'Для пар укажи минимум 2 строки в формате english=русский.', 'data' => []];
            }

            $extraData['pairs'] = $pairs;
            $answer = 'pairs';
        }

        if ($type === 'listening') {
            $extraData['listening_text'] = trim((string) ($data['listening_text'] ?? '')) ?: $answer;
        }

        return [
            'error' => null,
            'data' => [
                'type' => $type,
                'question' => $data['question'],
                'options' => $options,
                'answer' => $answer,
                'xp_reward' => $data['xp_reward'],
                'coin_reward' => $data['coin_reward'],
                'order' => $data['order'],
                'data' => $extraData,
            ],
        ];
    }
}
