<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Неверный email или пароль.',
                ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        $user = Auth::user();

        // 🚫 Полная блокировка
        if ($user->is_blocked) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Ваш аккаунт заблокирован администратором.',
                ]);
        }

        // ⏳ Временная блокировка (ИСПРАВЛЕНО)
        if ($user->blocked_until && now()->lessThan($user->blocked_until)) {
            Auth::logout();

            $seconds = now()->diffInSeconds($user->blocked_until);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Аккаунт временно заблокирован. Попробуйте через {$seconds} сек.",
                ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Слишком много попыток. Попробуйте через {$seconds} сек.",
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}