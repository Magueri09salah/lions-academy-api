<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin projection — keeps snake_case `cover_url` so the form payload
 * round-trips cleanly. The public FormationResource still maps that
 * field to `cover` for the data.ts-compatible shape.
 *
 * @mixin Formation
 */
class FormationAdminResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'duration' => $this->duration,
            'format' => $this->format,
            'level' => $this->level,
            'cover_url' => $this->cover_url,
            'summary' => $this->summary,
            'audience' => $this->audience,
            'method' => $this->method,
            'certification' => $this->certification,
            'objectives' => $this->objectives ?? [],
            'categories' => $this->categories ?? [],
            'display_order' => $this->display_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
