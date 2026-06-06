<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProgramMonth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Matches data.ts:PROGRAM items so the frontend's
 * `typeof PROGRAM[number]` type stays valid.
 *
 * Returns `month` (not `month_label`) to mirror the existing property name.
 *
 * @mixin ProgramMonth
 */
class ProgramMonthResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'month' => $this->month_label,
            'title' => $this->title,
            'axis' => $this->axis,
            'objective' => $this->objective,
            'deliverable' => $this->deliverable,
            'items' => $this->items ?? [],
        ];
    }
}
