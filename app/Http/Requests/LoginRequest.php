<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Attempt login with a per-email/IP throttle to slow brute-force attempts.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->recordFailedAttempt();

            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $this->clearAttempts();
    }

    private function ensureIsNotRateLimited(): void
    {
        if (cache()->get($this->throttleKey(), 0) < self::MAX_ATTEMPTS) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please wait a few minutes and try again.',
        ]);
    }

    private function recordFailedAttempt(): void
    {
        cache()->put($this->throttleKey(), cache()->get($this->throttleKey(), 0) + 1, now()->addMinutes(5));
    }

    private function clearAttempts(): void
    {
        cache()->forget($this->throttleKey());
    }

    private function throttleKey(): string
    {
        return 'login-attempts:'.hash('sha256', Str::lower($this->string('email')).'|'.$this->ip());
    }
}
