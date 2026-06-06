<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Formation\StoreFormationRequest;
use App\Http\Requests\Formation\UpdateFormationRequest;
use App\Http\Resources\FormationAdminResource;
use App\Models\Formation;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Formation::class);

        $items = Formation::query()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(FormationAdminResource::collection($items));
    }

    public function show(Request $request, Formation $formation): JsonResponse
    {
        $this->authorize('view', $formation);

        return ApiResponse::success(new FormationAdminResource($formation));
    }

    public function store(StoreFormationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['objectives'] = $data['objectives'] ?? [];
        $data['categories'] = $data['categories'] ?? [];
        $data['is_active'] = $data['is_active'] ?? true;
        $data['display_order'] = $data['display_order']
            ?? ((int) Formation::query()->max('display_order') + 1);

        $formation = Formation::query()->create($data);

        return ApiResponse::created(new FormationAdminResource($formation));
    }

    public function update(UpdateFormationRequest $request, Formation $formation): JsonResponse
    {
        $formation->update($request->validated());

        return ApiResponse::success(new FormationAdminResource($formation->fresh()));
    }

    public function destroy(Request $request, Formation $formation): JsonResponse
    {
        $this->authorize('delete', $formation);

        // Registrations reference formation_id with nullOnDelete, so old
        // submissions stay in the DB but lose the FK (they retain the
        // formation_title snapshot).
        $formation->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
