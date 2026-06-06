<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principle\StorePrincipleRequest;
use App\Http\Requests\Principle\UpdatePrincipleRequest;
use App\Http\Resources\PrincipleAdminResource;
use App\Models\Principle;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrincipleController extends Controller
{
    /**
     * GET /api/v1/admin/principles
     *
     * Returns ALL principles (including inactive ones) so admins can
     * toggle publish state. Ordered by display_order for a stable view.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Principle::class);

        $items = Principle::query()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(PrincipleAdminResource::collection($items));
    }

    public function show(Request $request, Principle $principle): JsonResponse
    {
        $this->authorize('view', $principle);

        return ApiResponse::success(new PrincipleAdminResource($principle));
    }

    /**
     * POST /api/v1/admin/principles
     */
    public function store(StorePrincipleRequest $request): JsonResponse
    {
        $principle = Principle::query()->create($request->validated() + [
            'display_order' => $request->validated('display_order')
                ?? ((int) Principle::query()->max('display_order') + 1),
            'is_active' => $request->validated('is_active') ?? true,
        ]);

        return ApiResponse::created(new PrincipleAdminResource($principle));
    }

    /**
     * PATCH /api/v1/admin/principles/{principle}
     */
    public function update(UpdatePrincipleRequest $request, Principle $principle): JsonResponse
    {
        $principle->update($request->validated());

        return ApiResponse::success(new PrincipleAdminResource($principle->fresh()));
    }

    /**
     * DELETE /api/v1/admin/principles/{principle}   (admin only)
     */
    public function destroy(Request $request, Principle $principle): JsonResponse
    {
        $this->authorize('delete', $principle);

        $principle->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
