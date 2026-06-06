<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectAdminResource;
use App\Models\Project;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $items = Project::query()
            ->orderBy('display_order')
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success(ProjectAdminResource::collection($items));
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return ApiResponse::success(new ProjectAdminResource($project));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['software'] = $data['software'] ?? [];
        $data['gallery_urls'] = $data['gallery_urls'] ?? [];
        $data['is_active'] = $data['is_active'] ?? true;
        $data['display_order'] = $data['display_order']
            ?? ((int) Project::query()->max('display_order') + 1);

        $project = Project::query()->create($data);

        return ApiResponse::created(new ProjectAdminResource($project));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());

        return ApiResponse::success(new ProjectAdminResource($project->fresh()));
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
