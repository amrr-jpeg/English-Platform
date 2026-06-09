<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\ContentCourse;
use App\Models\ContentManagerSubscription;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicContentController extends Controller
{
    public function managers()
    {
        $managers = User::query()
            ->where('role', User::ROLE_CONTENT_MANAGER)
            ->where('is_blocked', false)
            ->withCount(['contentCourses as published_courses_count' => fn ($q) => $q->where('is_published', true)])
            ->withCount('contentManagerFollowers')
            ->orderByDesc('published_courses_count')
            ->get();

        $subscriptions = auth()->user()
            ->contentManagerSubscriptions()
            ->pluck('content_manager_id')
            ->all();

        return view('content.managers.index', compact('managers', 'subscriptions'));
    }

    public function courses()
{
    $courses = \App\Models\ContentCourse::with('manager')
        ->where('is_published', true)
        ->latest()
        ->get();

    return view('content.courses.index', compact('courses'));
}

    public function manager(User $manager)
    {
        abort_unless($manager->role === User::ROLE_CONTENT_MANAGER || $manager->isAdmin(), 404);

        $manager->loadCount('contentManagerFollowers');
        $courses = ContentCourse::query()
            ->where('creator_id', $manager->id)
            ->where('is_published', true)
            ->withCount('lessons')
            ->latest()
            ->get();

        $isSubscribed = auth()->user()->isSubscribedTo($manager);

        return view('content.managers.show', compact('manager', 'courses', 'isSubscribed'));
    }

    public function subscribe(User $manager): RedirectResponse
    {
        abort_unless($manager->role === User::ROLE_CONTENT_MANAGER || $manager->isAdmin(), 404);
        abort_if($manager->id === auth()->id(), 403, 'Нельзя подписаться на самого себя.');

        ContentManagerSubscription::firstOrCreate([
            'user_id' => auth()->id(),
            'content_manager_id' => $manager->id,
        ]);

        return back()->with('success', 'Ты подписался на обновления контент-менеджера.');
    }

    public function unsubscribe(User $manager): RedirectResponse
    {
        ContentManagerSubscription::query()
            ->where('user_id', auth()->id())
            ->where('content_manager_id', $manager->id)
            ->delete();

        return back()->with('success', 'Подписка отменена.');
    }

    public function subscriptions()
    {
        $managerIds = auth()->user()->contentManagerSubscriptions()->pluck('content_manager_id');

        $courses = ContentCourse::query()
            ->whereIn('creator_id', $managerIds)
            ->where('is_published', true)
            ->with(['creator'])
            ->withCount('lessons')
            ->latest()
            ->get();

        return view('content.courses.subscriptions', compact('courses'));
    }

    public function course(ContentCourse $course)
{
    abort_unless(
        $course->is_published || $course->creator_id === auth()->id() || auth()->user()->isAdmin(),
        404
    );

    $course->load(['creator', 'lessons.exercises']);

    return view('content.courses.show', compact('course'));
}

    public function lesson(Lesson $lesson)
    {
        abort_unless($lesson->content_course_id, 404);
        abort_unless($lesson->is_active || $lesson->creator_id === auth()->id() || auth()->user()->isAdmin(), 404);

        $lesson->load(['contentCourse.creator', 'exercises']);

        abort_unless($lesson->contentCourse?->is_published || $lesson->creator_id === auth()->id() || auth()->user()->isAdmin(), 404);

        $done = auth()->user()->userExercises()
            ->whereIn('exercise_id', $lesson->exercises->pluck('id'))
            ->get()
            ->keyBy('exercise_id');

        $completed = $done->where('is_correct', true)->count();
        $total = $lesson->exercises->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return view('content.lessons.show', compact('lesson', 'done', 'completed', 'total', 'percent'));
    }
}
