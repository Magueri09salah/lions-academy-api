<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Services\Registration\RegistrationService;
use App\Services\Security\TurnstileVerifier;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationService $service,
        private readonly TurnstileVerifier $turnstile,
    ) {}

    /**
     * POST /api/v1/registrations
     *
     * Multipart/form-data submission from the public inscription form
     * (lion-s-roar-academy/src/routes/inscription.tsx).
     */
    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        // Optional Turnstile gate. No-op when TURNSTILE_SECRET_KEY is empty.
        if (! $this->turnstile->isHuman($request->input('turnstile_token'), $request->ip())) {
            return ApiResponse::error(
                message: 'La vérification anti-spam a échoué.',
                status: 422,
                code: 'turnstile_failed',
            );
        }

        $registration = $this->service->create(
            data: $request->toRegistrationData(),
            documents: $request->file('documents') ?: null,
            request: $request,
        );

        return ApiResponse::created(new RegistrationResource($registration));
    }
}
