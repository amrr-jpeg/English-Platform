<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardLog extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'source_id',
        'xp',
        'coins',
        'description',
        'meta',
    ];

    protected $casts = [
        'xp' => 'integer',
        'coins' => 'integer',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
