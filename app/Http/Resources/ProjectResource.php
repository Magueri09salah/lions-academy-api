<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Matches data.ts:PROJECTS items so the frontend's existing usage
 * (`p.cover`, `p.gallery`, `p.student`, `p.software`, etc.) keeps working
 * without renames.
 *
 * `id` is emitted as a STRING to match the existing data.ts mock (string ids).
 *
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'student' => $this->student_name,
            'promotion' => $this->promotion,
            'category' => $this->category,
            'software' => $this->software ?? [],
            'description' => $this->description,
            'status' => $this->status,
            'cover' => $this->cover_url,
            'gallery' => $this->gallery_urls ?? [],
        ];
    }
}
