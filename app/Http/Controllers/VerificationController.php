<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function show()
    {
        return view('auth.verify-code');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required'],
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'code' => 'Неверный код подтверждения.',
            ]);
        }

        $user->update([
            'is_verified' => true,
            'verification_code' => null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Регистрация успешно подтверждена.');
    }
}