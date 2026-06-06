<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormationResource;
use App\Models\Formation;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class FormationController extends Controller
{
    /**
     * GET /api/v1/formations
     * Returns every published formation ordered by display_order.
     */
    public function index(): JsonResponse
    {
        $items = Formation::query()
            ->published()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(FormationResource::collection($items));
    }

    /**
     * GET /api/v1/formations/{slug}
     * Implicit binding by slug (route model binding key).
     */
    public function show(string $slug): JsonResponse
    {
        $formation = Formation::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return ApiResponse::success(new FormationResource($formation));
    }
}
