<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * POST /api/v1/auth/login
     *
     * Body: { email, password, device_name?, remember? }
     * Returns: { user, token, abilities }
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            email: (string) $request->validated('email'),
            password: (string) $request->validated('password'),
            deviceName: $request->deviceName(),
            request: $request,
        );

        return ApiResponse::success([
            'user' => new UserResource($result['user']),
            'token' => $result['plain_text_token'],
            'token_type' => 'Bearer',
            'abilities' => $result['token']->accessToken->abilities ?? ['*'],
        ]);
    }
}
