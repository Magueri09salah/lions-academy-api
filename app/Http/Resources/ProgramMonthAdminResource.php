<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProgramMonth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin projection — exposes is_active and timestamps for the back-office.
 * The public ProgramMonthResource stays lean and emits `month` instead of
 * `month_label` (kept for backwards-compat with the existing frontend).
 *
 * @mixin ProgramMonth
 */
class ProgramMonthAdminResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formation' => $this->formation ? [
                'id' => $this->formation->id,
                'title' => $this->formation->title,
                'slug' => $this->formation->slug,
            ] : null,
            'position' => $this->position,
            'month_label' => $this->month_label,
            'title' => $this->title,
            'axis' => $this->axis,
            'objective' => $this->objective,
            'deliverable' => $this->deliverable,
            'items' => $this->items ?? [],
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
