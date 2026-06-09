<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentCourse extends Model
{
    protected $fillable = [
        'user_id',
        'manager_id',
        'title',
        'description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        if (array_key_exists('manager_id', $this->attributes)) {
            return $this->belongsTo(User::class, 'manager_id');
        }

        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(ContentLesson::class, 'course_id')->orderBy('order');
    }
}