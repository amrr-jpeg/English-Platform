<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentLesson extends Model
{
    protected $fillable = [
        'content_course_id',
        'course_id',
        'title',
        'description',
        'content',
        'order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(ContentCourse::class, 'course_id');
    }

    public function contentCourse(): BelongsTo
    {
        return $this->belongsTo(ContentCourse::class, 'content_course_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(ContentExercise::class, 'lesson_id')
            ->orderBy('order');
    }
}