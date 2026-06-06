<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use App\Support\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate /api/v1/admin/* behind staff accounts.
 *
 * Usage:
 *   Route::middleware(['auth:sanctum', 'admin'])->group(...);
 *   Route::middleware(['auth:sanctum', 'admin:admin'])->group(...);  // admin only
 *
 * - Without arg: any active backoffice account (admin OR editor).
 * - With arg "admin": admin role only (user management, settings).
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error('Authentification requise.', 401, 'unauthenticated');
        }

        if (! $user->isActive()) {
            return ApiResponse::error('Compte désactivé.', 403, 'account_disabled');
        }

        if ($requiredRole === UserRole::Admin->value && ! $user->isAdmin()) {
            return ApiResponse::error('Accès réservé aux administrateurs.', 403, 'forbidden');
        }

        return $next($request);
    }
}
