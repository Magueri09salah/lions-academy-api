<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramMonth\StoreProgramMonthRequest;
use App\Http\Requests\ProgramMonth\UpdateProgramMonthRequest;
use App\Http\Resources\ProgramMonthAdminResource;
use App\Models\ProgramMonth;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramMonthController extends Controller
{
    /**
     * GET /api/v1/admin/programme
     *
     * Returns ALL months (including inactive). Ordered by position so the
     * admin sees them in the same order as the public site.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProgramMonth::class);

        $items = ProgramMonth::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(ProgramMonthAdminResource::collection($items));
    }

    public function show(Request $request, ProgramMonth $month): JsonResponse
    {
        $this->authorize('view', $month);

        return ApiResponse::success(new ProgramMonthAdminResource($month));
    }

    public function store(StoreProgramMonthRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['items'] = $data['items'] ?? [];
        $data['is_active'] = $data['is_active'] ?? true;

        $month = ProgramMonth::query()->create($data);

        return ApiResponse::created(new ProgramMonthAdminResource($month));
    }

    public function update(UpdateProgramMonthRequest $request, ProgramMonth $month): JsonResponse
    {
        $month->update($request->validated());

        return ApiResponse::success(new ProgramMonthAdminResource($month->fresh()));
    }

    public function destroy(Request $request, ProgramMonth $month): JsonResponse
    {
        $this->authorize('delete', $month);

        $month->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
