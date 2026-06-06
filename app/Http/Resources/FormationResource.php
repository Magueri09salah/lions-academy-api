<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shape matches `lion-s-roar-academy/src/lib/data.ts` FORMATIONS items so
 * the frontend's existing types (typeof FORMATIONS[number]) keep working
 * once `src/lib/api.ts` is wired to real fetches.
 *
 * `cover` is emitted (not `cover_url`) to mirror the data.ts property name.
 *
 * @mixin Formation
 */
class FormationResource extends JsonResource
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
            'cover' => $this->cover_url,
            'summary' => $this->summary,
            'audience' => $this->audience,
            'method' => $this->method,
            'certification' => $this->certification,
            'objectives' => $this->objectives ?? [],
            'categories' => $this->categories ?? [],
            'is_active' => (bool) $this->is_active,
        ];
    }
}
