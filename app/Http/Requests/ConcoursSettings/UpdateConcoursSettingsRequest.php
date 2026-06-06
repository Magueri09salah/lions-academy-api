<?php

declare(strict_types=1);

namespace App\Http\Requests\ConcoursSettings;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Admin update for the landing-page video config.
 * Every field is optional — admin can clear any slot to hide that section.
 */
class UpdateConcoursSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'hero_video_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'hero_video_poster_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'explainer_video_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'explainer_video_poster_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'explainer_title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'testimonial_1_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'testimonial_1_poster_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'testimonial_1_label' => ['sometimes', 'nullable', 'string', 'max:150'],
            'testimonial_2_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'testimonial_2_poster_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'testimonial_2_label' => ['sometimes', 'nullable', 'string', 'max:150'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Treat empty strings as nulls so the URL validator doesn't reject blanks.
        $empty = array_filter(
            $this->all(),
            static fn ($v) => $v === '',
        );
        if ($empty !== []) {
            $this->merge(array_fill_keys(array_keys($empty), null));
        }
    }
}
