<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * POST /api/v1/auth/logout
     * Revokes the current Sanctum token (or session).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->auth->logout($request);

        return ApiResponse::success(['logged_out' => true]);
    }
}
