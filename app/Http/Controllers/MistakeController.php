<?php

namespace App\Http\Controllers;

use App\Models\ExerciseAttempt;
use Illuminate\Support\Facades\Auth;

class MistakeController extends Controller
{
    public function index()
    {
        $latestAttempts = ExerciseAttempt::where('user_id', Auth::id())
            ->with('exercise.lesson')
            ->latest()
            ->get()
            ->unique('exercise_id')
            ->values();

        $mistakes = $latestAttempts
            ->filter(fn ($attempt) => !$attempt->is_correct)
            ->take(20)
            ->values();

        $hardExercises = ExerciseAttempt::query()
            ->where('user_id', Auth::id())
            ->where('is_correct', false)
            ->selectRaw('exercise_id, COUNT(*) as wrong_count, MAX(created_at) as last_wrong_at')
            ->groupBy('exercise_id')
            ->having('wrong_count', '>=', 2)
            ->with('exercise.lesson')
            ->orderByDesc('wrong_count')
            ->limit(20)
            ->get();

        return view('mistakes.index', compact('mistakes', 'hardExercises'));
    }
}
