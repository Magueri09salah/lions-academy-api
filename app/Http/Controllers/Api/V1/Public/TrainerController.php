<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainerResource;
use App\Models\Trainer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TrainerController extends Controller
{
    /**
     * GET /api/v1/trainers
     */
    public function index(): JsonResponse
    {
        $items = Trainer::query()
            ->published()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(TrainerResource::collection($items));
    }
}
