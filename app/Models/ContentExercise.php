<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentExercise extends Model
{
    protected $fillable = [
        'content_lesson_id',
        'lesson_id',
        'question',
        'type',
        'options',
        'answer',
        'correct_answer',
        'xp_reward',
        'coins_reward',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(ContentLesson::class, 'lesson_id');
    }

    public function contentLesson(): BelongsTo
    {
        return $this->belongsTo(ContentLesson::class, 'content_lesson_id');
    }
}