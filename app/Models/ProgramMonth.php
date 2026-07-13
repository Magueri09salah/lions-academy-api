<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per month of a formation's programme.
 *
 * Each month belongs to a Formation (interior design, English, …) so the
 * academy can publish a distinct programme per formation. Position orders
 * months WITHIN their formation.
 *
 * Frontend shape (lion-s-roar-academy/src/lib/data.ts:PROGRAM):
 *   { month, title, axis, objective, deliverable, items: string[] }
 *
 * @property int $id
 * @property int|null $formation_id
 * @property int $position
 * @property string $month_label
 * @property string $title
 * @property string $axis
 * @property string $objective
 * @property string $deliverable
 * @property array<int, string>|null $items
 * @property bool $is_active
 *
 * @property-read Formation|null $formation
 */
class ProgramMonth extends Model
{
    /** @use HasFactory<\Database\Factories\ProgramMonthFactory> */
    use HasFactory;

    protected $fillable = [
        'formation_id',
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
            'formation_id' => 'integer',
            'position' => 'integer',
            'items' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForFormation(Builder $query, int|string|null $formation): Builder
    {
        if ($formation === null || $formation === '') {
            return $query;
        }

        // Accept a numeric id or a formation slug — the public page filters
        // by slug (SEO-friendly), the admin filters by id.
        if (is_numeric($formation)) {
            return $query->where('formation_id', (int) $formation);
        }

        return $query->whereHas('formation', fn (Builder $q) => $q->where('slug', $formation));
    }
}
