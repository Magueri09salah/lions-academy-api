<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * POST /api/v1/auth/forgot-password
     *
     * Always returns 200 (regardless of whether the email exists) so an
     * attacker cannot enumerate accounts. The actual email dispatch is
     * silent on failure.
     */
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $this->auth->sendResetLink((string) $request->validated('email'));

        return ApiResponse::success([
            'message' => 'Si un compte existe pour cette adresse, un email de réinitialisation a été envoyé.',
        ]);
    }
}
