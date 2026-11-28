<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $user = User::where('email', $this->input('email'))->first();

        // 1. Account lockout check (own implementation): runs first, so the
        //    password is never checked while the account is locked.
        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'This account is temporarily locked due to too many failed login attempts. Please try again '
                    . $user->locked_until->diffForHumans() . '.',
            ]);
        }

        // 2. Breeze built-in rate limiting (same email + IP).
        $this->ensureIsNotRateLimited();

        // 3. Verify the credentials.
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            $user?->registerFailedLogin();

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 4. Correct password, but the account is not active: log out at once.
        if (Auth::user()->account_status !== 'active') {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account is not active. Please contact the administrator.',
            ]);
        }

        // 5. Successful login: clear the rate limiter and the failed attempts.
        RateLimiter::clear($this->throttleKey());
        Auth::user()->clearFailedLogins();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}