<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentManagerSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'content_manager_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'content_manager_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contentManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_manager_id');
    }
}
