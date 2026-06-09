<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'order',
        'title',
        'description',
        'category',
        'level',
        'theory',
        'is_active',
        'creator_id',
        'content_course_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'creator_id' => 'integer',
        'content_course_id' => 'integer',
    ];

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class)->orderBy('order');
    }

    public function userLessons(): HasMany
    {
        return $this->hasMany(UserLesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function contentCourse(): BelongsTo
    {
        return $this->belongsTo(ContentCourse::class, 'content_course_id');
    }

    public function scopeMainCourse($query)
    {
        return $query->whereNull('content_course_id')
            ->where(function ($q) {
                $q->whereNull('category')
                    ->orWhere('category', '!=', 'Travel English');
            });
    }

    public function scopeTravelCourse($query)
    {
        return $query->where('category', 'Travel English');
    }

    public function scopeContentManagerLessons($query)
    {
        return $query->whereNotNull('content_course_id');
    }
}
