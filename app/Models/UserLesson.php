<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLesson extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed_exercises',
        'total_exercises',
        'is_completed',
    ];

    protected $casts = [
        'completed_exercises' => 'integer',
        'total_exercises' => 'integer',
        'is_completed' => 'boolean',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
