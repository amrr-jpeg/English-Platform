<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'code' => ['required', 'digits:6'],
            ],
            [
                'code.required' => 'Введите код подтверждения.',
                'code.digits' => 'Код должен содержать 6 цифр.',
            ]
        );

        $userId = $request->session()->get('2fa:user:id');
        $remember = $request->session()->get('2fa:remember', false);

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Пользователь не найден.']);
        }

        if (! $user->two_factor_code || ! $user->two_factor_expires_at) {
            return back()->withErrors([
                'code' => 'Код подтверждения отсутствует. Запросите новый вход.',
            ]);
        }

        if ($user->two_factor_expires_at->isPast()) {
            return back()->withErrors([
                'code' => 'Срок действия кода истёк. Выполните вход заново.',
            ]);
        }

        if ($request->code !== $user->two_factor_code) {
            return back()->withErrors([
                'code' => 'Неверный код подтверждения.',
            ]);
        }

        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        Auth::login($user, $remember);

        $request->session()->forget('2fa:user:id');
        $request->session()->forget('2fa:remember');
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa:user:id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login');
        }

        $code = (string) random_int(100000, 999999);

        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));

        return back()->with('status', 'Новый код отправлен на вашу почту.');
    }
}