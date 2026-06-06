<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ConcoursSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Settings projection consumed by:
 *   - GET /api/v1/concours-settings        (public landing page)
 *   - GET /api/v1/admin/concours-settings  (admin editor)
 *
 * Same shape for both — there's nothing sensitive to hide.
 *
 * @mixin ConcoursSettings
 */
class ConcoursSettingsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'hero_video_url' => $this->hero_video_url,
            'hero_video_poster_url' => $this->hero_video_poster_url,
            'explainer_video_url' => $this->explainer_video_url,
            'explainer_video_poster_url' => $this->explainer_video_poster_url,
            'explainer_title' => $this->explainer_title,
            'testimonial_1_url' => $this->testimonial_1_url,
            'testimonial_1_poster_url' => $this->testimonial_1_poster_url,
            'testimonial_1_label' => $this->testimonial_1_label,
            'testimonial_2_url' => $this->testimonial_2_url,
            'testimonial_2_poster_url' => $this->testimonial_2_poster_url,
            'testimonial_2_label' => $this->testimonial_2_label,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
