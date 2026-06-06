<?php

declare(strict_types=1);

namespace App\Http\Requests\Formation;

use App\Models\Formation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Formation|null $formation */
        $formation = $this->route('formation');

        return $this->user()?->can('update', $formation ?? Formation::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Formation|null $formation */
        $formation = $this->route('formation');

        return [
            'slug' => [
                'sometimes', 'required', 'string', 'min:2', 'max:120',
                'regex:/^[a-z0-9][a-z0-9\-]*$/i',
                Rule::unique('formations', 'slug')->ignore($formation?->id),
            ],
            'title' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'duration' => ['sometimes', 'nullable', 'string', 'max:60'],
            'format' => ['sometimes', 'nullable', 'string', 'max:60'],
            'level' => ['sometimes', 'nullable', 'string', 'max:60'],
            'cover_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'audience' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'method' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'certification' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'objectives' => ['sometimes', 'array', 'max:30'],
            'objectives.*' => ['string', 'min:1', 'max:300'],
            'categories' => ['sometimes', 'array', 'max:20'],
            'categories.*' => ['array'],
            'categories.*.title' => ['required_with:categories.*', 'string', 'min:2', 'max:200'],
            'categories.*.items' => ['sometimes', 'array', 'max:30'],
            'categories.*.items.*' => ['string', 'min:1', 'max:200'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $this->merge(['slug' => mb_strtolower(trim((string) $this->input('slug')))]);
        }

        $objectives = $this->input('objectives');
        if (is_array($objectives)) {
            $this->merge([
                'objectives' => array_values(array_filter(
                    array_map(fn ($v) => is_string($v) ? trim($v) : $v, $objectives),
                    fn ($v) => $v !== '' && $v !== null,
                )),
            ]);
        }

        $categories = $this->input('categories');
        if (is_array($categories)) {
            $clean = [];
            foreach ($categories as $cat) {
                if (! is_array($cat)) continue;
                $title = isset($cat['title']) && is_string($cat['title']) ? trim($cat['title']) : '';
                $items = isset($cat['items']) && is_array($cat['items']) ? $cat['items'] : [];
                $items = array_values(array_filter(
                    array_map(fn ($v) => is_string($v) ? trim($v) : $v, $items),
                    fn ($v) => $v !== '' && $v !== null,
                ));
                if ($title === '' && $items === []) continue;
                $clean[] = ['title' => $title, 'items' => $items];
            }
            $this->merge(['categories' => $clean]);
        }

        foreach (['cover_url', 'summary', 'audience', 'method', 'certification', 'duration', 'format', 'level'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }
}
