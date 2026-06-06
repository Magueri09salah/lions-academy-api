<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * POST /api/v1/auth/reset-password
     * Body: { token, email, password, password_confirmation }
     */
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->auth->resetPassword($request->validated());

        return ApiResponse::success([
            'user' => new UserResource($user),
            'message' => 'Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.',
        ]);
    }
}
