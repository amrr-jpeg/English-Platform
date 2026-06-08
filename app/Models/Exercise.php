<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = [
        'lesson_id',
        'type',
        'question',
        'options',
        'answer',
        'xp_reward',
        'coin_reward',
        'order',
        'data',
    ];

    protected $casts = [
        'options' => 'array',
        'data' => 'array',
        'xp_reward' => 'integer',
        'coin_reward' => 'integer',
        'order' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }
}
