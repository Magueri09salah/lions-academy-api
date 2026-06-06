<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\HoneypotMiddleware;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // NOTE: we deliberately do NOT call $middleware->statefulApi() here.
        // The admin SPA authenticates with Sanctum personal access tokens
        // (Authorization: Bearer ...), not cookies. Enabling statefulApi()
        // forces CSRF on POSTs from any "stateful domain" (incl. localhost:5173)
        // and would reject our login request with HTTP 419.
        //
        // If you later want cookie-based auth for a same-origin admin, enable
        // it here AND have the SPA call /sanctum/csrf-cookie before POSTs.

        // All /api/* responses are JSON.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        // Route-level middleware aliases.
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'honeypot' => HoneypotMiddleware::class,
        ]);

        // Trust proxies for Cloudflare / load-balancers (configurable in production).
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return match (true) {
                $e instanceof ValidationException => ApiResponse::validationError(
                    $e->errors(),
                    $e->getMessage(),
                ),
                $e instanceof AuthenticationException => ApiResponse::error(
                    'Authentification requise.',
                    401,
                    code: 'unauthenticated',
                ),
                $e instanceof AuthorizationException => ApiResponse::error(
                    $e->getMessage() ?: 'Action non autorisée.',
                    403,
                    code: 'forbidden',
                ),
                $e instanceof ModelNotFoundException => ApiResponse::error(
                    'Ressource introuvable.',
                    404,
                    code: 'not_found',
                ),
                $e instanceof NotFoundHttpException => ApiResponse::error(
                    'Route introuvable.',
                    404,
                    code: 'route_not_found',
                ),
                $e instanceof TokenMismatchException => ApiResponse::error(
                    'Jeton CSRF invalide.',
                    419,
                    code: 'csrf_mismatch',
                ),
                $e instanceof TooManyRequestsHttpException => ApiResponse::error(
                    'Trop de requêtes. Veuillez réessayer plus tard.',
                    429,
                    code: 'rate_limited',
                    headers: $e->getHeaders(),
                ),
                $e instanceof HttpExceptionInterface => ApiResponse::error(
                    $e->getMessage() ?: 'Erreur HTTP.',
                    $e->getStatusCode(),
                    code: 'http_error',
                    headers: $e->getHeaders(),
                ),
                default => ApiResponse::fromUnhandled($e),
            };
        });
    })
    ->create();
