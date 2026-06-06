<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationConcours\StoreRegistrationConcoursRequest;
use App\Http\Resources\RegistrationConcoursResource;
use App\Services\RegistrationConcours\RegistrationConcoursService;
use App\Services\Security\TurnstileVerifier;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class RegistrationConcoursController extends Controller
{
    public function __construct(
        private readonly RegistrationConcoursService $service,
        private readonly TurnstileVerifier $turnstile,
    ) {}

    /**
     * POST /api/v1/registrations-concours
     *
     * Public submission from the /concours-ena landing page.
     */
    public function store(StoreRegistrationConcoursRequest $request): JsonResponse
    {
        if (! $this->turnstile->isHuman($request->input('turnstile_token'), $request->ip())) {
            return ApiResponse::error(
                message: 'La vérification anti-spam a échoué.',
                status: 422,
                code: 'turnstile_failed',
            );
        }

        $lead = $this->service->create(
            data: $request->toLeadData(),
            request: $request,
        );

        return ApiResponse::created(new RegistrationConcoursResource($lead));
    }
}
