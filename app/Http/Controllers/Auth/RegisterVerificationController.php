<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RegisterVerificationController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (!session('registration_user_id')) {
            return redirect()->route('register');
        }

        return view('auth.register-verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::find(session('registration_user_id'));

        if (!$user) {
            return redirect()->route('register');
        }

        if ($user->registration_code_expires_at && $user->registration_code_expires_at->isPast()) {
            return back()->withErrors([
                'code' => 'Срок действия кода истёк. Отправьте код повторно.',
            ]);
        }

        if ($request->code !== $user->registration_code) {
            return back()->withErrors([
                'code' => 'Неверный код подтверждения.',
            ]);
        }

        $user->update([
            'is_registration_verified' => true,
            'registration_code' => null,
            'registration_code_expires_at' => null,
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        session()->forget('registration_user_id');
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Почта подтверждена. Добро пожаловать!');
    }

    public function resend(): RedirectResponse
    {
        $user = User::find(session('registration_user_id'));

        if (!$user) {
            return redirect()->route('register');
        }

        $code = (string) random_int(100000, 999999);

        $user->update([
            'registration_code' => $code,
            'registration_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new RegistrationCodeMail($user, $code));

        return back()->with('status', 'Новый код отправлен на вашу почту.');
    }
}