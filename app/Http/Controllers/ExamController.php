<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\UserLesson;
use App\Services\ProgressService;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    private array $exams = [
        1 => [
            'title' => 'Экзамен 1',
            'description' => 'Проверка после уроков 1–10.',
            'from' => 1,
            'to' => 10,
            'time' => 180,
            'pass_percent' => 60,
        ],
        2 => [
            'title' => 'Экзамен 2',
            'description' => 'Проверка после уроков 11–20.',
            'from' => 11,
            'to' => 20,
            'time' => 210,
            'pass_percent' => 65,
        ],
        3 => [
            'title' => 'Экзамен 3',
            'description' => 'Финальная проверка после уроков 21–30.',
            'from' => 21,
            'to' => 30,
            'time' => 240,
            'pass_percent' => 70,
        ],
    ];

    public function intro()
    {
        $user = Auth::user();
        $exams = [];

        foreach ($this->exams as $number => $exam) {
            $lessonIds = $this->lessonIdsForExam($exam);

            $completedCount = UserLesson::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->where('is_completed', true)
                ->count();

            $totalLessons = $lessonIds->count();

            $bestResult = ExamResult::where('user_id', $user->id)
                ->where('exam_number', $number)
                ->orderByDesc('percent')
                ->first();

            $exams[$number] = [
                ...$exam,
                'number' => $number,
                'completed' => $completedCount,
                'total' => $totalLessons,
                'unlocked' => $totalLessons > 0 && $completedCount >= $totalLessons,
                'best_result' => $bestResult,
            ];
        }

        return view('exam.intro', compact('exams'));
    }

    public function start(int $exam)
    {
        abort_unless(isset($this->exams[$exam]), 404);

        if (!$this->isExamUnlocked($exam)) {
            return redirect()
                ->route('exam.intro')
                ->with('error', 'Этот экзамен пока недоступен. Сначала пройди нужные уроки.');
        }

        $questions = $this->questions($exam);

        if (count($questions) === 0) {
            return redirect()
                ->route('exam.intro')
                ->with('error', 'Для экзамена пока нет подходящих заданий. Добавь упражнения в уроки.');
        }

        session()->put('exam_questions_' . $exam, $questions);
        session()->put('exam_started_at_' . $exam, now()->timestamp);

        return view('exam.start', [
            'examNumber' => $exam,
            'examData' => $this->exams[$exam],
            'questions' => $questions,
            'timeLimit' => $this->exams[$exam]['time'],
        ]);
    }

    public function submit(Request $request, int $exam, ProgressService $progressService, RewardService $rewardService)
    {
        abort_unless(isset($this->exams[$exam]), 404);

        if (!$this->isExamUnlocked($exam)) {
            return redirect()
                ->route('exam.intro')
                ->with('error', 'Этот экзамен пока недоступен. Сначала пройди нужные уроки.');
        }

        $user = Auth::user();
        $sessionKey = 'exam_questions_' . $exam;

        if (!session()->has($sessionKey)) {
            return redirect()
                ->route('exam.start', $exam)
                ->with('error', 'Сначала начни экзамен. Без активной экзаменационной сессии ответы не проверяются.');
        }

        $questions = collect(session($sessionKey));

        if ($questions->isEmpty()) {
            session()->forget($sessionKey);

            return redirect()->route('exam.intro')->with('error', 'Не удалось проверить экзамен: вопросы не найдены.');
        }

        $answers = $request->input('answers', []);
        $warnings = (int) $request->input('warnings', 0);
        $autoFinished = $request->boolean('auto_finished');
        $score = 0;

        foreach ($questions as $question) {
            $given = (string) ($answers[$question['id']] ?? '');

            if ($progressService->normalizeSentence($given) === $progressService->normalizeSentence((string) $question['answer'])) {
                $score++;
            }
        }

        $total = $questions->count();
        $percent = $total > 0 ? (int) round(($score / $total) * 100) : 0;
        $passed = $percent >= $this->exams[$exam]['pass_percent'] && $warnings < 3 && !$autoFinished;

        $previousBest = ExamResult::where('user_id', $user->id)
            ->where('exam_number', $exam)
            ->max('percent') ?? 0;

        $xp = 0;
        $coins = 0;
        $rewardMessage = 'Награда не начислена.';

        if ($passed && $percent > (int) $previousBest) {
            $xp = $score * 12 * $exam;
            $coins = $score * 5 * $exam;

            $reward = $rewardService->grant(
                $user,
                'exam',
                $exam,
                $xp,
                $coins,
                'Экзамен ' . $exam . ': результат ' . $percent . '%',
                ['score' => $score, 'total' => $total, 'previous_best' => $previousBest]
            );

            $rewardService->flashFirstAchievement($reward);
            $rewardMessage = 'Награда начислена за новый лучший результат.';
        } elseif ($passed) {
            $rewardMessage = 'Экзамен сдан, но награда уже была получена за такой или лучший результат.';
        } elseif ($warnings >= 3 || $autoFinished) {
            $rewardMessage = 'Награда не начислена из-за автоматического завершения экзамена.';
        } else {
            $rewardMessage = 'Награда начисляется только при успешной сдаче экзамена.';
        }

        ExamResult::create([
            'user_id' => $user->id,
            'exam_number' => $exam,
            'score' => $score,
            'total' => $total,
            'percent' => $percent,
            'passed' => $passed,
            'warnings' => $warnings,
            'auto_finished' => $warnings >= 3 || $autoFinished,
            'xp_reward' => $xp,
            'coin_reward' => $coins,
        ]);

        session()->forget($sessionKey);
        session()->forget('exam_started_at_' . $exam);

        return view('exam.result', compact(
            'exam',
            'score',
            'total',
            'percent',
            'warnings',
            'xp',
            'coins',
            'autoFinished',
            'passed',
            'rewardMessage'
        ));
    }

    private function isExamUnlocked(int $exam): bool
    {
        $user = Auth::user();
        $lessonIds = $this->lessonIdsForExam($this->exams[$exam]);

        if ($lessonIds->isEmpty()) {
            return false;
        }

        $completedCount = UserLesson::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->count();

        return $completedCount >= $lessonIds->count();
    }

    private function lessonIdsForExam(array $examData)
    {
        return Lesson::query()
            ->mainCourse()
            ->whereBetween('order', [$examData['from'], $examData['to']])
            ->where('is_active', true)
            ->pluck('id');
    }

    private function questions(int $exam): array
    {
        $examData = $this->exams[$exam];
        $lessonIds = $this->lessonIdsForExam($examData);

        $exercises = Exercise::whereIn('lesson_id', $lessonIds)
            ->whereNotNull('answer')
            ->where('answer', '!=', '')
            ->whereIn('type', ['choice', 'input', 'scramble'])
            ->with('lesson')
            ->orderBy('lesson_id')
            ->orderBy('order')
            ->get();

        $answerPool = $exercises->pluck('answer')
            ->filter()
            ->unique()
            ->values();

        return $exercises
            ->shuffle()
            ->take(10)
            ->map(function (Exercise $exercise) use ($answerPool) {
                $options = $exercise->options;

                if (!is_array($options) || count($options) < 2) {
                    $options = $answerPool
                        ->reject(fn ($answer) => $answer === $exercise->answer)
                        ->shuffle()
                        ->take(3)
                        ->push($exercise->answer)
                        ->shuffle()
                        ->values()
                        ->all();
                }

                if (!in_array($exercise->answer, $options, true)) {
                    $options[] = $exercise->answer;
                }

                return [
                    'id' => $exercise->id,
                    'question' => $exercise->question,
                    'options' => collect($options)->filter()->unique()->shuffle()->values()->all(),
                    'answer' => $exercise->answer,
                    'lesson' => $exercise->lesson?->title,
                ];
            })
            ->values()
            ->all();
    }
}
