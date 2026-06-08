<?php

namespace App\Http\Controllers;

use App\Models\GameScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GameController extends Controller
{
    private array $levels = [
        'easy' => 'Лёгкий',
        'medium' => 'Средний',
        'hard' => 'Сложный',
    ];

    private array $sources = [
        'learned' => 'Изученные слова',
        'current' => 'Текущий урок',
        'mistakes' => 'Мои ошибки',
        'travel' => 'Travel English',
    ];

    public function index()
    {
        $user = auth()->user();

        $games = [
            ['key' => 'translation', 'title' => 'Найди перевод', 'icon' => '📘', 'description' => 'Выбирай правильный перевод слов.', 'route' => 'games.translation', 'category' => 'Слова', 'accent' => 'blue', 'min_level' => 1],
            ['key' => 'listening', 'title' => 'Аудирование', 'icon' => '🎧', 'description' => 'Слушай слово и выбирай перевод.', 'route' => 'games.listening', 'category' => 'Аудирование', 'accent' => 'green', 'min_level' => 1],
            ['key' => 'typing', 'title' => 'Быстрый ввод', 'icon' => '⌨️', 'description' => 'Пиши английские слова.', 'route' => 'games.typing', 'category' => 'Скорость', 'accent' => 'orange', 'min_level' => 1],
            ['key' => 'memory', 'title' => 'Memory Game', 'icon' => '🧠', 'description' => 'Находи пары слов.', 'route' => 'games.memory', 'category' => 'Память', 'accent' => 'purple', 'min_level' => 2],
            ['key' => 'sentence', 'title' => 'Собери предложение', 'icon' => '🧩', 'description' => 'Собирай предложения.', 'route' => 'games.sentence', 'category' => 'Грамматика', 'accent' => 'yellow', 'min_level' => 3],
            ['key' => 'picture', 'title' => 'Угадай по картинке', 'icon' => '🖼️', 'description' => 'Выбирай слово по картинке.', 'route' => 'games.picture', 'category' => 'Слова', 'accent' => 'pink', 'min_level' => 1],
            ['key' => 'mistakes', 'title' => 'Исправь ошибки', 'icon' => '🎯', 'description' => 'Повтори сложные слова.', 'route' => 'games.mistakes', 'category' => 'Повторение', 'accent' => 'red', 'min_level' => 1],
        ];

        return view('games.index', [
            'games' => $games,
            'levels' => $this->levels,
            'sources' => $this->sources,
            'mission' => $this->dailyMission($user->id),
            'bestScores' => $this->bestScores($user->id),
            'sourceStats' => [
                'learned' => 16,
                'current' => 16,
                'mistakes' => 16,
                'travel' => 16,
            ],
            'user' => $user,
        ]);
    }

    public function translation(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'translation', $level);
    }

    public function picture(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'picture', $level);
    }

    public function memory(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'memory', $level);
    }

    public function sentence(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'sentence', $level);
    }

    public function typing(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'typing', $level);
    }

    public function listening(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'listening', $level);
    }

    public function mistakes(Request $request, string $level = 'easy')
    {
        return $this->launchGame($request, 'mistakes', $level);
    }

    public function reward(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'session_id' => ['required', 'string'],
            'answers' => ['nullable', 'array'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $sessionId = $validated['session_id'];
        $gameSession = session('game_sessions.' . $sessionId);

        if (!$gameSession || !is_array($gameSession)) {
            return response()->json([
                'ok' => false,
                'message' => 'Игровая сессия устарела. Запусти игру заново.',
            ], 419);
        }

        $game = $gameSession['game'] ?? 'translation';
        $level = $gameSession['level'] ?? 'easy';
        $source = $gameSession['source'] ?? 'learned';
        $items = $gameSession['items'] ?? [];

        [$score, $total, $wrongItems] = $this->calculateScore(
            $game,
            $items,
            $validated['answers'] ?? [],
            (int) ($validated['score'] ?? 0)
        );

        $accuracy = $total > 0 ? round(($score / $total) * 100, 1) : 0;

        [$xp, $coins, $rewardText] = $this->rewardForResult($game, $level, $score, $total, $accuracy);

        $rewardLimit = $this->gameRewardsToday($user->id) >= 3;
        $alreadyRewarded = session()->has('rewarded_game_sessions.' . $sessionId);

        if ($rewardLimit || $alreadyRewarded) {
            $xp = 0;
            $coins = 0;
            $rewardText = $rewardLimit
                ? 'Лимит наград за мини-игры на сегодня достигнут. Играть можно дальше для тренировки.'
                : 'Награда за эту попытку уже была начислена.';
        }

        $rewardResult = ['xp' => 0, 'coins' => 0];

        if ($xp > 0 || $coins > 0) {
            $rewardResult = $this->grantReward(
                $user,
                'game',
                null,
                $xp,
                $coins,
                'Мини-игра: ' . $this->gameTitle($game),
                [
                    'game' => $game,
                    'level' => $level,
                    'source' => $source,
                    'score' => $score,
                    'total' => $total,
                    'accuracy' => $accuracy,
                ]
            );

            session()->put('rewarded_game_sessions.' . $sessionId, true);
        }

        $isBest = $this->storeGameScore(
            $user->id,
            $game,
            $level,
            $source,
            $score,
            $total,
            $accuracy,
            $xp,
            $coins,
            ['wrong_items' => $wrongItems]
        );

        $dailyBonus = $this->tryGrantDailyMissionReward($user->fresh());

        session()->forget('game_sessions.' . $sessionId);

        return response()->json([
            'ok' => true,
            'score' => $score,
            'total' => $total,
            'accuracy' => $accuracy,
            'xp' => (int) ($rewardResult['xp'] ?? $xp),
            'coins' => (int) ($rewardResult['coins'] ?? $coins),
            'is_best' => $isBest,
            'wrong_items' => $wrongItems,
            'daily_bonus' => $dailyBonus,
            'message' => $rewardText,
        ]);
    }

    private function launchGame(Request $request, string $game, string $level = 'easy')
    {
        $this->checkLevel($level);

        $source = (string) $request->query('source', 'learned');
        $source = array_key_exists($source, $this->sources) ? $source : 'learned';

        $items = $this->itemsForGame($game, $level);

        $sessionId = (string) Str::uuid();

        session()->put('game_sessions.' . $sessionId, [
            'game' => $game,
            'level' => $level,
            'source' => $source,
            'items' => $items,
            'created_at' => now()->toDateTimeString(),
        ]);

        return view('games.play', [
            'sessionId' => $sessionId,
            'game' => [
                'key' => $game,
                'title' => $this->gameTitle($game),
                'icon' => $this->gameIcon($game),
                'description' => $this->gameDescription($game),
                'accent' => $this->gameAccent($game),
            ],
            'items' => $items,
            'level' => $level,
            'levelName' => $this->levels[$level],
            'source' => $source,
            'sourceName' => $this->sources[$source] ?? 'Тренировка',
            'time' => $this->timeForLevel($level, $game),
        ]);
    }

    private function itemsForGame(string $game, string $level): array
    {
        if ($game === 'sentence') {
            return $this->fallbackSentences($level);
        }

        $vocabulary = $this->fallbackVocabulary($level);

        shuffle($vocabulary);

        $vocabulary = array_slice($vocabulary, 0, $this->limitForLevel($level));

        $allRu = array_column($this->fallbackVocabulary($level), 'ru');
        $allEn = array_column($this->fallbackVocabulary($level), 'en');

        return match ($game) {
            'translation' => array_map(fn ($item) => [
                'mode' => 'select',
                'prompt' => $item['en'],
                'subprompt' => 'Выбери правильный перевод',
                'correct' => $item['ru'],
                'options' => $this->options($item['ru'], $allRu),
                'hint' => 'Вспомни перевод слова.',
            ], $vocabulary),

            'picture' => array_map(fn ($item) => [
                'mode' => 'select',
                'prompt' => $this->emojiForWord($item['en']),
                'subprompt' => 'Как это будет по-английски?',
                'correct' => $item['en'],
                'options' => $this->options($item['en'], $allEn),
                'hint' => $item['ru'],
                'is_picture' => true,
            ], $vocabulary),

            'typing' => array_map(fn ($item) => [
                'mode' => 'input',
                'prompt' => $item['ru'],
                'subprompt' => 'Напиши это слово по-английски',
                'correct' => $item['en'],
                'hint' => 'Ответ начинается на: ' . mb_substr($item['en'], 0, 1),
            ], $vocabulary),

            'listening' => array_map(fn ($item) => [
                'mode' => 'listening',
                'prompt' => 'Прослушай слово и выбери перевод',
                'subprompt' => 'Нажми «Прослушать», затем выбери правильный перевод.',
                'speak' => $item['en'],
                'correct' => $item['ru'],
                'options' => $this->options($item['ru'], $allRu),
                'hint' => 'Слово звучит как: ' . mb_substr($item['en'], 0, 1) . '...',
            ], $vocabulary),

            'memory' => array_map(fn ($item) => [
                'mode' => 'memory',
                'en' => $item['en'],
                'ru' => $item['ru'],
            ], $vocabulary),

            'mistakes' => array_map(fn ($item) => [
                'mode' => 'select',
                'prompt' => $item['en'],
                'subprompt' => 'Повтори слово и выбери перевод',
                'correct' => $item['ru'],
                'options' => $this->options($item['ru'], $allRu),
                'hint' => 'Тренировка ошибок.',
            ], $vocabulary),

            default => [],
        };
    }

    private function fallbackVocabulary(string $level): array
    {
        $data = [
            'easy' => [
                ['en' => 'cat', 'ru' => 'кот'],
                ['en' => 'dog', 'ru' => 'собака'],
                ['en' => 'bird', 'ru' => 'птица'],
                ['en' => 'fish', 'ru' => 'рыба'],
                ['en' => 'apple', 'ru' => 'яблоко'],
                ['en' => 'bread', 'ru' => 'хлеб'],
                ['en' => 'milk', 'ru' => 'молоко'],
                ['en' => 'water', 'ru' => 'вода'],
                ['en' => 'book', 'ru' => 'книга'],
                ['en' => 'pen', 'ru' => 'ручка'],
                ['en' => 'school', 'ru' => 'школа'],
                ['en' => 'desk', 'ru' => 'парта'],
                ['en' => 'sun', 'ru' => 'солнце'],
                ['en' => 'tree', 'ru' => 'дерево'],
                ['en' => 'house', 'ru' => 'дом'],
                ['en' => 'room', 'ru' => 'комната'],
            ],
            'medium' => [
                ['en' => 'teacher', 'ru' => 'учитель'],
                ['en' => 'doctor', 'ru' => 'доктор'],
                ['en' => 'driver', 'ru' => 'водитель'],
                ['en' => 'family', 'ru' => 'семья'],
                ['en' => 'window', 'ru' => 'окно'],
                ['en' => 'breakfast', 'ru' => 'завтрак'],
                ['en' => 'street', 'ru' => 'улица'],
                ['en' => 'museum', 'ru' => 'музей'],
                ['en' => 'shop', 'ru' => 'магазин'],
                ['en' => 'park', 'ru' => 'парк'],
                ['en' => 'train', 'ru' => 'поезд'],
                ['en' => 'mountain', 'ru' => 'гора'],
                ['en' => 'river', 'ru' => 'река'],
                ['en' => 'message', 'ru' => 'сообщение'],
                ['en' => 'conversation', 'ru' => 'разговор'],
                ['en' => 'future', 'ru' => 'будущее'],
            ],
            'hard' => [
                ['en' => 'knowledge', 'ru' => 'знание'],
                ['en' => 'education', 'ru' => 'образование'],
                ['en' => 'environment', 'ru' => 'окружающая среда'],
                ['en' => 'responsibility', 'ru' => 'ответственность'],
                ['en' => 'achievement', 'ru' => 'достижение'],
                ['en' => 'opportunity', 'ru' => 'возможность'],
                ['en' => 'development', 'ru' => 'развитие'],
                ['en' => 'improvement', 'ru' => 'улучшение'],
                ['en' => 'communication', 'ru' => 'общение'],
                ['en' => 'technology', 'ru' => 'технология'],
                ['en' => 'pollution', 'ru' => 'загрязнение'],
                ['en' => 'protection', 'ru' => 'защита'],
                ['en' => 'safety', 'ru' => 'безопасность'],
                ['en' => 'success', 'ru' => 'успех'],
                ['en' => 'career', 'ru' => 'карьера'],
                ['en' => 'choice', 'ru' => 'выбор'],
            ],
        ];

        return $data[$level] ?? $data['easy'];
    }

    private function fallbackSentences(string $level): array
    {
        $data = [
            'easy' => [
                ['prompt' => 'Я люблю английский.', 'correct' => 'I like English'],
                ['prompt' => 'Это моя книга.', 'correct' => 'This is my book'],
                ['prompt' => 'У меня есть кот.', 'correct' => 'I have a cat'],
                ['prompt' => 'Собака пьёт воду.', 'correct' => 'The dog drinks water'],
                ['prompt' => 'Книга на парте.', 'correct' => 'The book is on the desk'],
            ],
            'medium' => [
                ['prompt' => 'Она читает книгу.', 'correct' => 'She reads a book'],
                ['prompt' => 'Мы ходим в школу.', 'correct' => 'We go to school'],
                ['prompt' => 'Учитель объясняет урок.', 'correct' => 'The teacher explains the lesson'],
                ['prompt' => 'Я живу на этой улице.', 'correct' => 'I live on this street'],
                ['prompt' => 'Мы идём в музей.', 'correct' => 'We are going to the museum'],
                ['prompt' => 'Они играют вместе.', 'correct' => 'They play together'],
            ],
            'hard' => [
                ['prompt' => 'Изучение английского помогает мне развиваться.', 'correct' => 'Learning English helps me develop'],
                ['prompt' => 'Ответственность важна для успеха.', 'correct' => 'Responsibility is important for success'],
                ['prompt' => 'Знания открывают новые возможности.', 'correct' => 'Knowledge opens new opportunities'],
                ['prompt' => 'Технологии меняют наше общение.', 'correct' => 'Technology changes our communication'],
                ['prompt' => 'Защита окружающей среды важна для будущего.', 'correct' => 'Environmental protection is important for the future'],
                ['prompt' => 'Образование помогает строить карьеру.', 'correct' => 'Education helps build a career'],
            ],
        ];

        $sentences = $data[$level] ?? $data['easy'];

        shuffle($sentences);

        return array_map(fn ($item) => [
            'mode' => 'sentence',
            'prompt' => $item['prompt'],
            'subprompt' => 'Собери английское предложение',
            'correct' => $item['correct'],
            'words' => preg_split('/\s+/u', $item['correct'], -1, PREG_SPLIT_NO_EMPTY),
            'hint' => 'Собери слова в правильном порядке.',
        ], array_slice($sentences, 0, $this->limitForLevel($level)));
    }

    private function options(string $correct, array $pool): array
    {
        $pool = collect($pool)
            ->filter(fn ($item) => trim((string) $item) !== '')
            ->unique()
            ->reject(fn ($item) => $this->normalizeAnswer((string) $item) === $this->normalizeAnswer($correct))
            ->shuffle()
            ->take(3)
            ->values()
            ->all();

        $pool[] = $correct;

        shuffle($pool);

        return $pool;
    }

    private function calculateScore(string $game, array $items, array $answers, int $fallbackScore): array
    {
        if ($game === 'memory') {
            $total = count($items);
            $score = min(max($fallbackScore, 0), $total);

            return [$score, max($total, 1), []];
        }

        $score = 0;
        $wrong = [];
        $total = count($items);

        foreach ($items as $index => $item) {
            $correct = $this->normalizeAnswer((string) ($item['correct'] ?? ''));
            $given = $this->normalizeAnswer((string) ($answers[$index] ?? ''));

            if ($correct !== '' && $given === $correct) {
                $score++;
            } else {
                $wrong[] = [
                    'prompt' => $item['prompt'] ?? ($item['speak'] ?? 'Задание'),
                    'correct' => $item['correct'] ?? '',
                    'answer' => $answers[$index] ?? '',
                    'hint' => $item['hint'] ?? null,
                ];
            }
        }

        return [$score, max($total, 1), array_slice($wrong, 0, 5)];
    }

    private function rewardForResult(string $game, string $level, int $score, int $total, float $accuracy): array
    {
        if ($total <= 0 || $score <= 0 || $accuracy < 50) {
            return [0, 0, 'Награда не начислена: попробуй набрать больше правильных ответов.'];
        }

        $multiplier = match ($level) {
            'medium' => 1.4,
            'hard' => 1.8,
            default => 1,
        };

        $baseXp = $accuracy >= 90 ? 25 : ($accuracy >= 70 ? 15 : 8);
        $baseCoins = $accuracy >= 90 ? 7 : ($accuracy >= 70 ? 4 : 2);

        if (in_array($game, ['listening', 'mistakes'], true)) {
            $baseXp += 5;
        }

        $xp = (int) round($baseXp * $multiplier);
        $coins = (int) round($baseCoins * $multiplier);

        return [$xp, $coins, "+{$xp} XP и +{$coins} монет"];
    }

    private function grantReward($user, string $source, ?int $sourceId, int $xp, int $coins, string $description, array $meta = []): array
    {
        if (class_exists(\App\Services\RewardService::class) && Schema::hasTable('reward_logs')) {
            return app(\App\Services\RewardService::class)->grant(
                $user,
                $source,
                $sourceId,
                $xp,
                $coins,
                $description,
                $meta,
                true
            );
        }

        $user->xp = (int) $user->xp + $xp;
        $user->coins = (int) $user->coins + $coins;
        $user->level = max(1, intdiv((int) $user->xp, 100) + 1);
        $user->save();

        return ['xp' => $xp, 'coins' => $coins];
    }

    private function storeGameScore(int $userId, string $game, string $level, string $source, int $score, int $total, float $accuracy, int $xp, int $coins, array $meta = []): bool
    {
        if (!Schema::hasTable('game_scores')) {
            return false;
        }

        $bestBefore = GameScore::query()
            ->where('user_id', $userId)
            ->where('game', $game)
            ->where('level', $level)
            ->where('source', $source)
            ->max('score');

        $isBest = $bestBefore === null || $score > (int) $bestBefore;

        GameScore::create([
            'user_id' => $userId,
            'game' => $game,
            'level' => $level,
            'source' => $source,
            'score' => $score,
            'total' => $total,
            'accuracy' => $accuracy,
            'xp' => $xp,
            'coins' => $coins,
            'is_rewarded' => $xp > 0 || $coins > 0,
            'is_best' => $isBest,
            'meta' => $meta,
        ]);

        return $isBest;
    }

    private function dailyMission(int $userId): array
    {
        $played = 0;
        $correct = 0;
        $rewarded = false;

        if (Schema::hasTable('game_scores')) {
            $todayQuery = GameScore::query()
                ->where('user_id', $userId)
                ->whereDate('created_at', now()->toDateString());

            $played = (clone $todayQuery)->count();
            $correct = (int) (clone $todayQuery)->sum('score');
        }

        if (Schema::hasTable('reward_logs')) {
            $rewarded = DB::table('reward_logs')
                ->where('user_id', $userId)
                ->where('source', 'daily_game_mission')
                ->whereDate('created_at', now()->toDateString())
                ->exists();
        }

        return [
            'played' => $played,
            'played_goal' => 2,
            'correct' => $correct,
            'correct_goal' => 5,
            'mistakes_played' => false,
            'completed' => $played >= 2 && $correct >= 5,
            'rewarded' => $rewarded,
            'reward' => '+15 XP и +5 монет',
        ];
    }

    private function tryGrantDailyMissionReward($user): ?array
    {
        if (!$user) {
            return null;
        }

        $mission = $this->dailyMission($user->id);

        if (!$mission['completed'] || $mission['rewarded']) {
            return null;
        }

        $reward = $this->grantReward(
            $user,
            'daily_game_mission',
            null,
            15,
            5,
            'Дневная миссия мини-игр',
            ['mission_date' => now()->toDateString()]
        );

        return [
            'xp' => $reward['xp'] ?? 15,
            'coins' => $reward['coins'] ?? 5,
            'message' => 'Дневная миссия выполнена: +15 XP и +5 монет',
        ];
    }

    private function bestScores(int $userId): array
    {
        if (!Schema::hasTable('game_scores')) {
            return [];
        }

        return GameScore::query()
            ->where('user_id', $userId)
            ->select('game', DB::raw('MAX(score) as best_score'), DB::raw('MAX(accuracy) as best_accuracy'))
            ->groupBy('game')
            ->get()
            ->keyBy('game')
            ->map(fn ($row) => [
                'score' => (int) $row->best_score,
                'accuracy' => (float) $row->best_accuracy,
            ])
            ->all();
    }

    private function gameRewardsToday(int $userId): int
    {
        if (Schema::hasTable('reward_logs')) {
            return DB::table('reward_logs')
                ->where('user_id', $userId)
                ->where('source', 'game')
                ->whereDate('created_at', now()->toDateString())
                ->count();
        }

        if (Schema::hasTable('game_scores')) {
            return GameScore::query()
                ->where('user_id', $userId)
                ->where('is_rewarded', true)
                ->whereDate('created_at', now()->toDateString())
                ->count();
        }

        return 0;
    }

    private function normalizeAnswer(string $text): string
    {
        return Str::of($text)
            ->lower()
            ->replaceMatches('/[.!?,;:]/u', '')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
    }

    private function limitForLevel(string $level): int
    {
        return match ($level) {
            'medium' => 7,
            'hard' => 8,
            default => 5,
        };
    }

    private function timeForLevel(string $level, string $game): int
    {
        if (in_array($game, ['memory', 'sentence', 'mistakes', 'listening'], true)) {
            return 0;
        }

        return match ($level) {
            'medium' => 45,
            'hard' => 40,
            default => 50,
        };
    }

    private function checkLevel(string $level): void
    {
        abort_unless(array_key_exists($level, $this->levels), 404);
    }

    private function gameTitle(string $game): string
    {
        return [
            'translation' => 'Найди перевод',
            'picture' => 'Угадай по картинке',
            'memory' => 'Memory Game',
            'sentence' => 'Собери предложение',
            'typing' => 'Быстрый ввод',
            'listening' => 'Аудирование',
            'mistakes' => 'Исправь ошибки',
        ][$game] ?? 'Мини-игра';
    }

    private function gameIcon(string $game): string
    {
        return [
            'translation' => '📘',
            'picture' => '🖼️',
            'memory' => '🧠',
            'sentence' => '🧩',
            'typing' => '⌨️',
            'listening' => '🎧',
            'mistakes' => '🎯',
        ][$game] ?? '🎮';
    }

    private function gameDescription(string $game): string
    {
        return [
            'translation' => 'Выбирай правильный перевод слов.',
            'picture' => 'Определи английское слово по картинке.',
            'memory' => 'Открывай карточки и находи пары.',
            'sentence' => 'Собери английское предложение из слов.',
            'typing' => 'Пиши английские слова по русскому переводу.',
            'listening' => 'Слушай слово и выбирай правильный перевод.',
            'mistakes' => 'Повтори сложные слова.',
        ][$game] ?? 'Тренировка английского.';
    }

    private function gameAccent(string $game): string
    {
        return [
            'translation' => 'blue',
            'picture' => 'pink',
            'memory' => 'purple',
            'sentence' => 'yellow',
            'typing' => 'orange',
            'listening' => 'green',
            'mistakes' => 'red',
        ][$game] ?? 'blue';
    }

    private function emojiForWord(string $word): string
    {
        $map = [
            'cat' => '🐱',
            'dog' => '🐶',
            'bird' => '🐦',
            'fish' => '🐟',
            'apple' => '🍎',
            'bread' => '🍞',
            'milk' => '🥛',
            'water' => '💧',
            'book' => '📚',
            'pen' => '✏️',
            'school' => '🏫',
            'desk' => '🪑',
            'sun' => '☀️',
            'tree' => '🌳',
            'house' => '🏠',
            'room' => '🛏️',
            'teacher' => '👩‍🏫',
            'doctor' => '🧑‍⚕️',
            'driver' => '🚗',
            'family' => '👨‍👩‍👧',
            'window' => '🪟',
            'breakfast' => '🍳',
            'street' => '🛣️',
            'museum' => '🏛️',
            'shop' => '🏪',
            'park' => '🌲',
            'train' => '🚆',
            'mountain' => '⛰️',
            'river' => '🏞️',
            'message' => '💬',
            'conversation' => '🗣️',
            'future' => '🔮',
            'knowledge' => '🧠',
            'education' => '🎓',
            'environment' => '🌍',
            'responsibility' => '✅',
            'achievement' => '🏆',
            'opportunity' => '🚪',
            'development' => '📈',
            'improvement' => '⬆️',
            'communication' => '📞',
            'technology' => '💻',
            'pollution' => '🏭',
            'protection' => '🛡️',
            'safety' => '🚦',
            'success' => '🥇',
            'career' => '💼',
            'choice' => '🔀',
        ];

        return $map[mb_strtolower($word)] ?? '⭐';
    }
}