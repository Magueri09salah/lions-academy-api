<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Standard envelope for every JSON response served under /api/*.
 *
 * Success shape:
 *   { "success": true, "data": <payload>, "meta": <object|null> }
 *
 * Error shape:
 *   { "success": false, "error": { "message": "...", "code": "...", "details": {...} } }
 *
 * The frontend's `src/lib/api.ts` will read `data` for the typed payload.
 * Validation errors expose `details` as a field => string[] map so
 * `react-hook-form` can attach them to inputs.
 */
final class ApiResponse
{
    /**
     * Successful response with a payload.
     */
    public static function success(
        mixed $data = null,
        int $status = 200,
        ?array $meta = null,
        array $headers = [],
    ): JsonResponse {
        $payload = ['success' => true, 'data' => self::resolvePayload($data)];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        // If the payload is a paginator/resource collection, attach pagination meta
        // automatically so the frontend never has to special-case it.
        $autoMeta = self::extractPaginationMeta($data);
        if ($autoMeta !== null) {
            $payload['meta'] = array_merge($autoMeta, $payload['meta'] ?? []);
        }

        return response()->json($payload, $status, $headers);
    }

    /**
     * 201 Created shortcut.
     */
    public static function created(mixed $data = null, array $headers = []): JsonResponse
    {
        return self::success($data, 201, headers: $headers);
    }

    /**
     * 204 No Content shortcut.
     */
    public static function noContent(array $headers = []): JsonResponse
    {
        return response()->json(null, 204, $headers);
    }

    /**
     * Generic error response with an explicit status and machine-readable code.
     */
    public static function error(
        string $message,
        int $status = 400,
        string $code = 'error',
        array $details = [],
        array $headers = [],
    ): JsonResponse {
        $body = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
            ],
        ];

        if ($details !== []) {
            $body['error']['details'] = $details;
        }

        return response()->json($body, $status, $headers);
    }

    /**
     * Validation failure (HTTP 422). The `errors` arg matches Laravel's
     * `ValidationException::errors()` shape.
     */
    public static function validationError(array $errors, string $message = 'Données invalides.'): JsonResponse
    {
        return self::error(
            message: $message,
            status: 422,
            code: 'validation_failed',
            details: $errors,
        );
    }

    /**
     * Render an unhandled exception. In production we hide the message.
     * The full stack trace is always logged.
     */
    public static function fromUnhandled(Throwable $e): JsonResponse
    {
        Log::error('Unhandled API exception', [
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile().':'.$e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $debug = (bool) config('app.debug');

        return self::error(
            message: $debug ? ($e->getMessage() ?: 'Erreur serveur.') : 'Une erreur interne est survenue.',
            status: 500,
            code: 'server_error',
            details: $debug ? [
                'exception' => $e::class,
                'file' => $e->getFile().':'.$e->getLine(),
            ] : [],
        );
    }

    /**
     * Unwrap Arrayable / JsonResource / Collection so the `data` key
     * always serializes to a plain JSON value the frontend can consume.
     */
    private static function resolvePayload(mixed $data): mixed
    {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            // Let the Resource handle its own ->toArray() — Laravel will
            // call it during JSON serialization.
            return $data;
        }

        if ($data instanceof LengthAwarePaginator) {
            return $data->items();
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }

    private static function extractPaginationMeta(mixed $data): ?array
    {
        if ($data instanceof LengthAwarePaginator) {
            return [
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                ],
            ];
        }

        return null;
    }
}
