<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin projection — flat shape with snake_case keys (display_order,
 * is_active, instagram_url, linkedin_url) for the form payload to
 * round-trip cleanly. The public TrainerResource still maps these into
 * the data.ts-compatible `socials: {instagram, linkedin}` shape.
 *
 * @mixin Trainer
 */
class TrainerAdminResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'role' => $this->role,
            'specialty' => $this->specialty,
            'bio' => $this->bio,
            'experience' => $this->experience,
            'initials' => $this->initials,
            'photo_url' => $this->photo_url,
            'modules' => $this->modules ?? [],
            'software' => $this->software ?? [],
            'instagram_url' => $this->instagram_url,
            'linkedin_url' => $this->linkedin_url,
            'display_order' => $this->display_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
