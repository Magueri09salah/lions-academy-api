<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Principle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin projection of a Principle — exposes all columns including
 * display_order, is_active, and timestamps so the back-office can show
 * publication state and ordering.
 *
 * The public PrincipleResource still returns the lean { id, title, desc }
 * shape consumed by the homepage / academie page.
 *
 * @mixin Principle
 */
class PrincipleAdminResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'display_order' => $this->display_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
