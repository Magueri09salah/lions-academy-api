<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrincipleResource;
use App\Models\Principle;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PrincipleController extends Controller
{
    /**
     * GET /api/v1/principles
     */
    public function index(): JsonResponse
    {
        $items = Principle::query()
            ->published()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(PrincipleResource::collection($items));
    }
}
