<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentCourse extends Model
{
    protected $fillable = [
        'user_id',
        'creator_id',
        'manager_id',
        'title',
        'description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id')
            ->withDefault(function ($user) {
                $user->name = 'Контент-менеджер';
            });
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id')
            ->withDefault(function ($user) {
                $user->name = 'Контент-менеджер';
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
            ->withDefault(function ($user) {
                $user->name = 'Контент-менеджер';
            });
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(ContentLesson::class, 'course_id')
            ->orderBy('order');
    }
}