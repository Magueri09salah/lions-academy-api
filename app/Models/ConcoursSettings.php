<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton model — at most one row, holding the editable video config
 * for the /concours-ena landing page.
 *
 * @property int $id
 * @property string|null $hero_video_url
 * @property string|null $hero_video_poster_url
 * @property string|null $explainer_video_url
 * @property string|null $explainer_video_poster_url
 * @property string|null $explainer_title
 * @property string|null $testimonial_1_url
 * @property string|null $testimonial_1_poster_url
 * @property string|null $testimonial_1_label
 * @property string|null $testimonial_2_url
 * @property string|null $testimonial_2_poster_url
 * @property string|null $testimonial_2_label
 */
class ConcoursSettings extends Model
{
    protected $table = 'concours_settings';

    protected $fillable = [
        'hero_video_url',
        'hero_video_poster_url',
        'explainer_video_url',
        'explainer_video_poster_url',
        'explainer_title',
        'testimonial_1_url',
        'testimonial_1_poster_url',
        'testimonial_1_label',
        'testimonial_2_url',
        'testimonial_2_poster_url',
        'testimonial_2_label',
    ];

    /**
     * Return the single settings row, creating it on first access.
     * Wrap reads in this so callers never have to handle the null case.
     *
     * We deliberately bypass mass-assignment here (force-fill the id) so
     * the model's $fillable list doesn't need to include "id".
     */
    public static function current(): self
    {
        $existing = static::query()->find(1);
        if ($existing !== null) {
            return $existing;
        }
        $row = new self();
        $row->forceFill(['id' => 1])->save();

        return $row;
    }
}
