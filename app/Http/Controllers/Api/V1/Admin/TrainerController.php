<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trainer\StoreTrainerRequest;
use App\Http\Requests\Trainer\UpdateTrainerRequest;
use App\Http\Resources\TrainerAdminResource;
use App\Models\Trainer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Trainer::class);

        $items = Trainer::query()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success(TrainerAdminResource::collection($items));
    }

    public function show(Request $request, Trainer $trainer): JsonResponse
    {
        $this->authorize('view', $trainer);

        return ApiResponse::success(new TrainerAdminResource($trainer));
    }

    public function store(StoreTrainerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['modules'] = $data['modules'] ?? [];
        $data['software'] = $data['software'] ?? [];
        $data['is_active'] = $data['is_active'] ?? true;
        $data['display_order'] = $data['display_order']
            ?? ((int) Trainer::query()->max('display_order') + 1);

        $trainer = Trainer::query()->create($data);

        return ApiResponse::created(new TrainerAdminResource($trainer));
    }

    public function update(UpdateTrainerRequest $request, Trainer $trainer): JsonResponse
    {
        $trainer->update($request->validated());

        return ApiResponse::success(new TrainerAdminResource($trainer->fresh()));
    }

    public function destroy(Request $request, Trainer $trainer): JsonResponse
    {
        $this->authorize('delete', $trainer);

        $trainer->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
