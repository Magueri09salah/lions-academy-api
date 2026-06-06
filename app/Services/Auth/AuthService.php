<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

/**
 * Encapsulates auth flows so controllers stay thin.
 *
 * Auth strategy:
 *   - Bearer tokens (Sanctum personal access tokens) for the admin SPA.
 *     The token is returned once at login; the SPA stores it and sends
 *     `Authorization: Bearer <token>` on every subsequent request.
 *   - Stateful cookie auth is also supported (configured in
 *     bootstrap/app.php via statefulApi()) for same-site setups.
 */
final class AuthService
{
    /**
     * Attempt password login, return the user + a freshly minted token.
     *
     * @throws ValidationException when credentials are invalid or account is disabled
     */
    public function login(string $email, string $password, string $deviceName, Request $request): array
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est désactivé.'],
            ]);
        }

        // Revoke any prior token issued to the same device so a single
        // device only ever holds one valid token at a time.
        $user->tokens()->where('name', $deviceName)->delete();

        $abilities = $user->isAdmin() ? ['*'] : ['content:*'];
        $token = $user->createToken($deviceName, $abilities);

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return [
            'user' => $user,
            'token' => $token,
            'plain_text_token' => $token->plainTextToken,
        ];
    }

    /**
     * Revoke the current access token (Bearer) or session-based auth.
     */
    public function logout(Request $request): void
    {
        $user = $request->user();
        if ($user === null) {
            return;
        }

        $current = $user->currentAccessToken();
        if ($current !== null && method_exists($current, 'delete')) {
            $current->delete();
        } else {
            // Stateful guard fallback.
            Auth::guard('web')->logout();
            $request->session()?->invalidate();
            $request->session()?->regenerateToken();
        }
    }

    /**
     * Revoke every token owned by the user (force logout from all devices).
     */
    public function logoutEverywhere(User $user): int
    {
        return $user->tokens()->delete();
    }

    /**
     * Send a password-reset email. To avoid account enumeration we always
     * return a generic success response upstream; the boolean here just
     * tells the caller whether an email was actually dispatched.
     */
    public function sendResetLink(string $email): bool
    {
        $status = Password::broker()->sendResetLink(['email' => $email]);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * Complete password reset using a token previously emailed to the user.
     *
     * @throws ValidationException on invalid/expired token
     */
    public function resetPassword(array $credentials): User
    {
        $resetUser = null;

        $status = Password::broker()->reset(
            $credentials,
            function (User $user, string $password) use (&$resetUser) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Invalidate all existing tokens — credentials changed.
                $user->tokens()->delete();

                event(new PasswordReset($user));
                $resetUser = $user;
            }
        );

        if ($status !== Password::PASSWORD_RESET || $resetUser === null) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $resetUser;
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->forceFill(['password' => Hash::make($newPassword)])->save();

        // Revoke other tokens but keep the current session/token alive.
        $current = request()->user()?->currentAccessToken();
        $user->tokens()
            ->when($current?->id, fn ($q) => $q->where('id', '!=', $current->id))
            ->delete();
    }
}
