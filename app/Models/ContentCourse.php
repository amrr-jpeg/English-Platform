<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ContentCourse extends Model
{
    protected $fillable = [
        'creator_id',
        'title',
        'slug',
        'description',
        'level',
        'is_published',
    ];

    protected $casts = [
        'creator_id' => 'integer',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContentCourse $course) {
            if (!$course->slug) {
                $course->slug = static::uniqueSlug($course->title);
            }
        });

        static::updating(function (ContentCourse $course) {
            if (!$course->slug) {
                $course->slug = static::uniqueSlug($course->title, $course->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $i = 2;

        while (static::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'content_course_id')->orderBy('order');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
