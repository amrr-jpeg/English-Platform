<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\RewardLog;
use App\Models\UserLesson;
use App\Services\ProgressService;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TravelController extends Controller
{
    public function index(ProgressService $progressService)
    {
        $user = Auth::user();
        $lessons = $progressService->travelLessons();
        $progress = $progressService->progressByLesson($user);
        $unlockedLessonIds = $progressService->unlockedLessonIds($lessons, $progress);

        $completedIds = UserLesson::where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        return view('travel.index', compact('lessons', 'completedIds', 'unlockedLessonIds'));
    }

    public function games()
    {
        $games = [
            [
                'key' => 'airport',
                'title' => 'Аэропорт',
                'icon' => '✈️',
                'description' => 'Слова и фразы для регистрации, багажа и посадки.',
                'questions' => [
                    ['q' => 'boarding pass', 'a' => 'посадочный талон'],
                    ['q' => 'luggage', 'a' => 'багаж'],
                    ['q' => 'gate', 'a' => 'выход на посадку'],
                    ['q' => 'passport', 'a' => 'паспорт'],
                ],
            ],
            [
                'key' => 'hotel',
                'title' => 'Отель',
                'icon' => '🏨',
                'description' => 'Фразы для заселения, брони и вопросов на ресепшене.',
                'questions' => [
                    ['q' => 'reservation', 'a' => 'бронь'],
                    ['q' => 'check-in', 'a' => 'заселение'],
                    ['q' => 'room key', 'a' => 'ключ от номера'],
                    ['q' => 'reception', 'a' => 'ресепшен'],
                ],
            ],
            [
                'key' => 'restaurant',
                'title' => 'Ресторан',
                'icon' => '🍽️',
                'description' => 'Заказ еды, счёт, меню и общение с официантом.',
                'questions' => [
                    ['q' => 'menu', 'a' => 'меню'],
                    ['q' => 'bill', 'a' => 'счёт'],
                    ['q' => 'water', 'a' => 'вода'],
                    ['q' => 'table', 'a' => 'столик'],
                ],
            ],
            [
                'key' => 'taxi',
                'title' => 'Такси',
                'icon' => '🚕',
                'description' => 'Адрес, цена, маршрут и простые фразы для поездки.',
                'questions' => [
                    ['q' => 'address', 'a' => 'адрес'],
                    ['q' => 'price', 'a' => 'цена'],
                    ['q' => 'station', 'a' => 'станция'],
                    ['q' => 'How much is it?', 'a' => 'Сколько это стоит?'],
                ],
            ],
        ];

        return view('travel.games', compact('games'));
    }

    public function gameReward(Request $request, RewardService $rewardService)
    {
        $user = Auth::user();

        $data = $request->validate([
            'game' => ['required', 'string', 'max:50'],
            'score' => ['required', 'integer', 'min:0', 'max:10'],
            'total' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        if ((int) $data['score'] === 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Попробуй ещё раз: награда выдаётся хотя бы за один правильный ответ.',
                'xp' => 0,
                'coins' => 0,
            ], 422);
        }

        $alreadyRewardedToday = RewardLog::where('user_id', $user->id)
            ->where('source', 'travel_game')
            ->whereDate('created_at', today())
            ->where('meta->game', $data['game'])
            ->exists();

        if ($alreadyRewardedToday) {
            return response()->json([
                'ok' => false,
                'message' => 'Сегодня награда за этот travel-тренажёр уже получена.',
                'xp' => 0,
                'coins' => 0,
            ], 429);
        }

        $xp = (int) $data['score'] * 8;
        $coins = (int) $data['score'] * 3;

        $reward = $rewardService->grant(
            $user,
            'travel_game',
            null,
            $xp,
            $coins,
            'Travel English: тренажёр ' . $data['game'],
            $data
        );

        $rewardService->flashFirstAchievement($reward);

        return response()->json([
            'ok' => true,
            'message' => '+' . $xp . ' XP и +' . $coins . ' монет',
            'xp' => $xp,
            'coins' => $coins,
        ]);
    }

    public function scenario()
    {
        if (!session()->has('travel_deepseek_history')) {
            session()->put('travel_deepseek_history', [
                [
                    'role' => 'assistant',
                    'content' => 'Hi! Welcome to the travel simulator ✈️ I will help you practice English for real travel situations. First scene: you are at the airport. Tell me in English: where are you flying today?'
                ]
            ]);
        }

        $messages = session('travel_deepseek_history', []);

        return view('travel.scenario', compact('messages'));
    }

    public function scenarioChat(Request $request, RewardService $rewardService)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $history = session('travel_deepseek_history', []);

        $history[] = [
            'role' => 'user',
            'content' => $data['message'],
        ];

        $answer = $this->askDeepSeek($history);

        $history[] = [
            'role' => 'assistant',
            'content' => $answer,
        ];

        session()->put('travel_deepseek_history', array_slice($history, -14));

        $userMessagesCount = collect($history)->where('role', 'user')->count();
        $rewardMessage = null;

        if ($userMessagesCount >= 5) {
            $alreadyRewarded = RewardLog::where('user_id', Auth::id())
                ->where('source', 'travel_scenario')
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadyRewarded) {
                $reward = $rewardService->grant(
                    Auth::user(),
                    'travel_scenario',
                    null,
                    35,
                    15,
                    'Завершён travel-сценарий',
                    ['messages' => $userMessagesCount]
                );

                $rewardService->flashFirstAchievement($reward);
                $rewardMessage = '+35 XP и +15 монет за завершение сценария';
            }
        }

        return response()->json([
            'answer' => $answer,
            'reward' => $rewardMessage,
        ]);
    }

    public function scenarioReset()
    {
        session()->forget('travel_deepseek_history');

        session()->put('travel_deepseek_history', [
            [
                'role' => 'assistant',
                'content' => 'Hi! Welcome to the travel simulator ✈️ I will help you practice English for real travel situations. First scene: you are at the airport. Tell me in English: where are you flying today?'
            ]
        ]);

        return response()->json([
            'messages' => session('travel_deepseek_history'),
        ]);
    }

    private function askDeepSeek(array $history): string
    {
        if (!config('services.deepseek.key')) {
            return $this->localTravelAssistant($history);
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->travelSystemPrompt()]],
            $this->prepareHistoryForDeepSeek($history)
        );

        try {
            $response = Http::withToken(config('services.deepseek.key'))
                ->acceptJson()
                ->asJson()
                ->timeout(45)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => config('services.deepseek.model', 'deepseek-v4-flash'),
                    'messages' => $messages,
                    'stream' => false,
                    'temperature' => 0.8,
                    'max_tokens' => 500,
                    'thinking' => ['type' => 'disabled'],
                ]);

            if (!$response->successful()) {
                Log::warning('DeepSeek API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->localTravelAssistant($history);
            }

            $text = $this->extractDeepSeekText($response->json());

            return $text ?: 'Я не смог сформировать ответ. Попробуй написать ещё раз.';
        } catch (\Throwable $e) {
            Log::error('DeepSeek connection error', ['message' => $e->getMessage()]);
            return $this->localTravelAssistant($history);
        }
    }

    private function travelSystemPrompt(): string
    {
        return 'Ты — ИИ-преподаватель английского языка в дипломном веб-приложении. Формат игры: симулятор путешествия. Общайся коротко, дружелюбно, на уровне A1–A2. Исправляй ошибки мягко, давай правильный вариант и задавай один следующий вопрос по теме путешествий.';
    }

    private function prepareHistoryForDeepSeek(array $history): array
    {
        $prepared = [];

        foreach (array_slice($history, -12) as $message) {
            $role = $message['role'] ?? 'user';
            $content = trim((string) ($message['content'] ?? ''));

            if (in_array($role, ['user', 'assistant'], true) && $content !== '') {
                $prepared[] = ['role' => $role, 'content' => $content];
            }
        }

        return $prepared;
    }

    private function extractDeepSeekText(array $data): string
    {
        return trim($data['choices'][0]['message']['content'] ?? '');
    }

    private function localTravelAssistant(array $history): string
    {
        $questions = [
            'Great! ✅ Now you are at the airport. Say in English: "Где мой выход на посадку?"',
            'Good try! Better phrase: "Where is my gate?" ✈️ Now you are at the hotel. Say: "У меня есть бронь."',
            'Nice! Better phrase: "I have a reservation." 🏨 Now you are in a restaurant. Ask for water in English.',
            'Good! You can say: "Can I have some water, please?" 🍽️ Now ask how much the taxi costs.',
            'Great work! You can say: "How much is the taxi?" 🚕 Your travel practice is complete. You did well!',
        ];

        $userMessagesCount = collect($history)->where('role', 'user')->count();

        return $questions[min(max($userMessagesCount - 1, 0), count($questions) - 1)];
    }
}
