<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * GET /api/v1/auth/me — current user (used by admin SPA on bootstrap).
     */
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * PATCH /api/v1/auth/me/password — change own password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->auth->changePassword(
            user: $request->user(),
            newPassword: (string) $request->validated('password'),
        );

        return ApiResponse::success(['updated' => true]);
    }

    /**
     * POST /api/v1/auth/logout-everywhere — kill all sessions/tokens.
     */
    public function logoutEverywhere(Request $request): JsonResponse
    {
        $revoked = $this->auth->logoutEverywhere($request->user());

        return ApiResponse::success(['revoked' => $revoked]);
    }
}
