<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pedagogical principle / engagement displayed on the homepage and
 * the /academie page. Shape mirrors data.ts:PRINCIPLES (title + desc).
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $display_order
 * @property bool $is_active
 */
class Principle extends Model
{
    /** @use HasFactory<\Database\Factories\PrincipleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
