<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    protected $fillable = [
        'user_id',
        'game',
        'level',
        'source',
        'score',
        'total',
        'accuracy',
        'xp',
        'coins',
        'is_rewarded',
        'is_best',
        'meta',
    ];

    protected $casts = [
        'score' => 'integer',
        'total' => 'integer',
        'accuracy' => 'float',
        'xp' => 'integer',
        'coins' => 'integer',
        'is_rewarded' => 'boolean',
        'is_best' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
