<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * GET /api/v1/projects
     * Optional `?category=Plans 2D` filter (matches the chip-filter on /realisations).
     */
    public function index(Request $request): JsonResponse
    {
        $category = trim((string) $request->query('category', ''));

        $items = Project::query()
            ->published()
            ->when(
                $category !== '' && strcasecmp($category, 'Tous') !== 0,
                fn ($q) => $q->where('category', $category),
            )
            ->orderBy('display_order')
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success(ProjectResource::collection($items));
    }

    /**
     * GET /api/v1/projects/{project}
     */
    public function show(Project $project): JsonResponse
    {
        abort_unless($project->is_active, 404);

        return ApiResponse::success(new ProjectResource($project));
    }
}
