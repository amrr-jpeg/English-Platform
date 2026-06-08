<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'email' => ['required', 'email:rfc,dns'],
            ],
            [
                'email.required' => 'Введите email.',
                'email.email' => 'Введите корректный email.',
            ]
        );

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', 'Ссылка для восстановления пароля отправлена на вашу почту.')
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Не удалось отправить письмо для восстановления пароля.']);
    }
}