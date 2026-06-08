<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации формы входа.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Пользовательские сообщения.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'password.required' => 'Введите пароль.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
        ];
    }

    /**
     * Проверка лимита попыток.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Слишком много попыток входа. Повторите через {$seconds} сек.",
        ]);
    }

    /**
     * Ключ ограничения попыток.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('email')).'|'.$this->ip()
        );
    }
}