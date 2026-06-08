<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = [
        'user_id',
        'exam_number',
        'score',
        'total',
        'percent',
        'passed',
        'warnings',
        'auto_finished',
        'xp_reward',
        'coin_reward',
    ];

    protected $casts = [
        'exam_number' => 'integer',
        'score' => 'integer',
        'total' => 'integer',
        'percent' => 'integer',
        'passed' => 'boolean',
        'warnings' => 'integer',
        'auto_finished' => 'boolean',
        'xp_reward' => 'integer',
        'coin_reward' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
