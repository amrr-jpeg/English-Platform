<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
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

    public function scopeMainCourse($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('category')
                ->orWhere('category', '!=', 'Travel English');
        });
    }

    public function scopeTravelCourse($query)
    {
        return $query->where('category', 'Travel English');
    }
}
