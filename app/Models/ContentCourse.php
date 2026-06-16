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
        'is_published' => 'boolean',
    ];

    public static function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'course';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (self::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id')
            ->withDefault([
                'name' => 'Контент-менеджер',
            ]);
    }

    public function manager(): BelongsTo
    {
        return $this->creator();
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'content_course_id')
            ->orderBy('order')
            ->orderBy('id');
    }
}