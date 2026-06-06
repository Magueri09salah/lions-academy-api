<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConcoursSettingsResource;
use App\Models\ConcoursSettings;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ConcoursSettingsController extends Controller
{
    /**
     * GET /api/v1/concours-settings
     *
     * Read-only settings consumed by the public landing page.
     */
    public function show(): JsonResponse
    {
        return ApiResponse::success(new ConcoursSettingsResource(ConcoursSettings::current()));
    }
}
