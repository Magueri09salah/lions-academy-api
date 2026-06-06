<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per month of the 6-month programme.
 *
 * Frontend shape (lion-s-roar-academy/src/lib/data.ts:PROGRAM):
 *   { month, title, axis, objective, deliverable, items: string[] }
 *
 * @property int $id
 * @property int $position
 * @property string $month_label
 * @property string $title
 * @property string $axis
 * @property string $objective
 * @property string $deliverable
 * @property array<int, string>|null $items
 * @property bool $is_active
 */
class ProgramMonth extends Model
{
    /** @use HasFactory<\Database\Factories\ProgramMonthFactory> */
    use HasFactory;

    protected $fillable = [
        'position',
        'month_label',
        'title',
        'axis',
        'objective',
        'deliverable',
        'items',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'items' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
