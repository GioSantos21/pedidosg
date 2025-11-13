<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
        public function authenticate(): void
        {
            $this->ensureIsNotRateLimited();

            // --- PASO 1: Intentar autenticar SÓLO con nombre y contraseña ---
            // (Quitamos la condición 'is_active' de aquí)
            $credentials = $this->only('name', 'password');

            if (! Auth::attempt($credentials, $this->boolean('remember'))) {
                // SI FALLA: El nombre o la contraseña son incorrectos.
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'name' => trans('auth.failed'), // Mensaje: "Estas credenciales no coinciden..."
                ]);
            }

            // --- PASO 2: Las credenciales SON correctas. Ahora revisamos el estado. ---
            $user = Auth::user(); // Obtenemos el usuario que acaba de iniciar sesión

            if (! $user->is_active) {
                // SI ESTÁ INACTIVO: Lo expulsamos y mostramos el error personalizado.
                Auth::guard('web')->logout(); // Forzamos el cierre de sesión

                $this->session()->invalidate();
                $this->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'name' => 'Tu usuario se encuentra desactivado por el momento.', // ¡Tu mensaje personalizado!
                ]);
            }

            // --- PASO 3: Las credenciales son correctas y está activo ---
            RateLimiter::clear($this->throttleKey());
        }

    /**
     * Ensure the login request is not rate limited.
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
            // CAMBIADO: 'email' por 'name'
            'name' => trans('auth.throttle', [
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
        return Str::transliterate(Str::lower($this->string('name')).'|'.$this->ip());
    }
}
