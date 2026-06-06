<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Trainer / formateur card on the public /formateurs page.
 * Shape mirrors data.ts:TRAINERS items.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $role
 * @property string $specialty
 * @property string|null $bio
 * @property string $experience
 * @property string $initials
 * @property string|null $photo_url
 * @property array<int,string>|null $modules
 * @property array<int,string>|null $software
 * @property string|null $instagram_url
 * @property string|null $linkedin_url
 * @property int $display_order
 * @property bool $is_active
 */
class Trainer extends Model
{
    /** @use HasFactory<\Database\Factories\TrainerFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'role',
        'specialty',
        'bio',
        'experience',
        'initials',
        'photo_url',
        'modules',
        'software',
        'instagram_url',
        'linkedin_url',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'software' => 'array',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
