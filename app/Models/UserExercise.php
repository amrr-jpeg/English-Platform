<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserExercise extends Model
{
    protected $fillable = [
        'user_id',
        'exercise_id',
        'is_correct',
        'user_answer',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
