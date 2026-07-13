<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramMonthResource;
use App\Models\ProgramMonth;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramMonthController extends Controller
{
    /**
     * GET /api/v1/programme?formation=<slug|id>
     *
     * Returns published months in order, each with its formation reference
     * so the public page can group/filter per formation. Months whose
     * formation was unpublished are excluded — a hidden formation should
     * not leak its programme.
     */
    public function index(Request $request): JsonResponse
    {
        $months = ProgramMonth::query()
            ->published()
            ->with('formation:id,title,slug')
            ->whereHas('formation', fn ($q) => $q->where('is_active', true))
            ->forFormation($request->query('formation'))
            ->orderBy('formation_id')
            ->orderBy('position')
            ->get();

        return ApiResponse::success(ProgramMonthResource::collection($months));
    }
}
