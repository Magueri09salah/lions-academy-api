<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Student project / réalisation. Shape mirrors data.ts:PROJECTS items.
 *
 * @property int $id
 * @property string $title
 * @property string $student_name
 * @property string $promotion
 * @property string $category
 * @property array<int,string>|null $software
 * @property string|null $description
 * @property string $status
 * @property string|null $cover_url
 * @property array<int,string>|null $gallery_urls
 * @property int $display_order
 * @property bool $is_active
 */
class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'student_name',
        'promotion',
        'category',
        'software',
        'description',
        'status',
        'cover_url',
        'gallery_urls',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'software' => 'array',
            'gallery_urls' => 'array',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
