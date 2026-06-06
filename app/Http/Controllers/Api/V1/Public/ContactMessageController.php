<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessage\StoreContactMessageRequest;
use App\Http\Resources\ContactMessageResource;
use App\Services\ContactMessage\ContactMessageService;
use App\Services\Security\TurnstileVerifier;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $service,
        private readonly TurnstileVerifier $turnstile,
    ) {}

    /**
     * POST /api/v1/contact-messages
     * Body: JSON or multipart with { name, email, phone?, subject, message }.
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        if (! $this->turnstile->isHuman($request->input('turnstile_token'), $request->ip())) {
            return ApiResponse::error(
                message: 'La vérification anti-spam a échoué.',
                status: 422,
                code: 'turnstile_failed',
            );
        }

        $message = $this->service->create(
            data: $request->toMessageData(),
            request: $request,
        );

        return ApiResponse::created(new ContactMessageResource($message));
    }
}
