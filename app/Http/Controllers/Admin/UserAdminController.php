<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q'));

        $users = User::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%');
                });
            })
            ->withCount([
                'userLessons as completed_lessons_count' => fn ($q) => $q->where('is_completed', true),
                'exerciseAttempts as attempts_count',
                'exerciseAttempts as correct_attempts_count' => fn ($q) => $q->where('is_correct', true),
                'contentCourses as content_courses_count',
                'contentManagerFollowers as followers_count',
            ])
            ->orderByDesc('xp')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'query'));
    }

    public function toggleBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя заблокировать самого себя.');
        }

        $user->is_blocked = !$user->is_blocked;
        $user->blocked_until = $user->is_blocked ? now()->addYears(10) : null;
        $user->save();

        return back()->with('success', $user->is_blocked ? 'Пользователь заблокирован.' : 'Пользователь разблокирован.');
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя изменить роль самому себе.');
        }

        $user->is_admin = !$user->is_admin;
        $user->role = $user->is_admin ? User::ROLE_ADMIN : User::ROLE_USER;
        $user->save();

        return back()->with('success', $user->is_admin ? 'Пользователь получил права администратора.' : 'Права администратора сняты.');
    }

    public function toggleContentManager(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя изменить роль самому себе.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Администратор уже имеет полный доступ. Сначала сними права администратора, если нужно.');
        }

        $user->role = $user->role === User::ROLE_CONTENT_MANAGER
            ? User::ROLE_USER
            : User::ROLE_CONTENT_MANAGER;

        $user->save();

        return back()->with('success', $user->role === User::ROLE_CONTENT_MANAGER
            ? 'Пользователь стал контент-менеджером.'
            : 'Роль контент-менеджера снята.');
    }
}
