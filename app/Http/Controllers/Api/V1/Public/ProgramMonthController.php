<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramMonthResource;
use App\Models\ProgramMonth;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProgramMonthController extends Controller
{
    /**
     * GET /api/v1/programme
     * Returns the 6 months in order.
     */
    public function index(): JsonResponse
    {
        $months = ProgramMonth::query()
            ->published()
            ->orderBy('position')
            ->get();

        return ApiResponse::success(ProgramMonthResource::collection($months));
    }
}
