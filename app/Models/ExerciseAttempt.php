<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'exercise_id',
        'lesson_id',
        'user_answer',
        'is_correct',
        'xp_reward',
        'coin_reward',
        'source',
        'meta',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'meta' => 'array',
        'xp_reward' => 'integer',
        'coin_reward' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
