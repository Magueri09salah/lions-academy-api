<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConcoursSettings\UpdateConcoursSettingsRequest;
use App\Http\Resources\ConcoursSettingsResource;
use App\Models\ConcoursSettings;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ConcoursSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        return ApiResponse::success(new ConcoursSettingsResource(ConcoursSettings::current()));
    }

    public function update(UpdateConcoursSettingsRequest $request): JsonResponse
    {
        $settings = ConcoursSettings::current();
        $settings->update($request->validated());

        return ApiResponse::success(new ConcoursSettingsResource($settings->fresh()));
    }
}
