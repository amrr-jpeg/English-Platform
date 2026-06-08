<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
        ]);

        $code = (string) random_int(100000, 999999);

        $user = User::create([
            'name' => trim($request->name),
            'email' => mb_strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'registration_code' => $code,
            'registration_code_expires_at' => now()->addMinutes(10),
            'is_registration_verified' => false,
        ]);

        Mail::to($user->email)->send(new RegistrationCodeMail($user, $code));

        session(['registration_user_id' => $user->id]);

        return redirect()->route('register.verify.notice')
            ->with('status', 'Код подтверждения отправлен на вашу почту.');
    }
}