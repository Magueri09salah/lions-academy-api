<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force every /api/* request to be treated as JSON.
 *
 * This makes Laravel return JSON-shaped validation/auth/exception
 * responses regardless of what the client put in `Accept`, which keeps
 * the response envelope consistent for the TanStack frontend.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
