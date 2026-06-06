<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Full Formation content as consumed by the public site.
 *
 * objectives is a flat `string[]`; categories is `{title, items: string[]}[]`.
 * Both are stored as JSON columns so the entire detail view loads in a
 * single SELECT — admin editing happens as one form, no need for normalized
 * child tables.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property bool $is_active
 * @property int $display_order
 * @property string|null $cover_url
 * @property string|null $duration       e.g. "6 mois"
 * @property string|null $format         e.g. "À distance"
 * @property string|null $level          e.g. "Débutant accepté"
 * @property string|null $summary
 * @property string|null $audience
 * @property string|null $method
 * @property string|null $certification
 * @property array<int, string>|null $objectives
 * @property array<int, array{title:string, items: array<int,string>}>|null $categories
 */
class Formation extends Model
{
    /** @use HasFactory<\Database\Factories\FormationFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'is_active',
        'display_order',
        'cover_url',
        'duration',
        'format',
        'level',
        'summary',
        'audience',
        'method',
        'certification',
        'objectives',
        'categories',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'objectives' => 'array',
            'categories' => 'array',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Look up a Formation by either slug or title (case-insensitive).
     * Used when the public form posts a `formation` string the admin chose
     * from a dropdown — robust to either identifier.
     */
    public static function findByReference(?string $reference): ?self
    {
        if ($reference === null || trim($reference) === '') {
            return null;
        }

        $needle = trim($reference);

        return static::query()
            ->where('slug', $needle)
            ->orWhereRaw('LOWER(title) = ?', [mb_strtolower($needle)])
            ->first();
    }
}
