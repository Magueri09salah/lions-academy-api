<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Principle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Matches data.ts:PRINCIPLES — { title, desc }. Frontend reads `desc`,
 * not `description`, so we mirror that property name on the API.
 *
 * @mixin Principle
 */
class PrincipleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'desc' => $this->description,
        ];
    }
}
