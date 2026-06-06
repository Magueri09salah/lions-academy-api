<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Matches data.ts:TRAINERS items. `id` is the slug (kept as string to match
 * the existing mock data), `photo` and the `socials` shape mirror the
 * existing frontend usage in `lion-s-roar-academy/src/routes/formateurs.tsx`.
 *
 * @mixin Trainer
 */
class TrainerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'role' => $this->role,
            'specialty' => $this->specialty,
            'bio' => $this->bio,
            'experience' => $this->experience,
            'modules' => $this->modules ?? [],
            'software' => $this->software ?? [],
            'initials' => $this->initials,
            'photo' => $this->photo_url,
            'socials' => [
                'instagram' => $this->instagram_url,
                'linkedin' => $this->linkedin_url,
            ],
        ];
    }
}
