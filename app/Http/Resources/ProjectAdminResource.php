<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin projection — exposes the snake_case `cover_url` / `gallery_urls`
 * column names so form payloads round-trip cleanly. The public
 * ProjectResource still maps these to `cover` and `gallery` to match
 * the data.ts shape.
 *
 * @mixin Project
 */
class ProjectAdminResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'student_name' => $this->student_name,
            'promotion' => $this->promotion,
            'category' => $this->category,
            'software' => $this->software ?? [],
            'description' => $this->description,
            'status' => $this->status,
            'cover_url' => $this->cover_url,
            'gallery_urls' => $this->gallery_urls ?? [],
            'display_order' => $this->display_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
