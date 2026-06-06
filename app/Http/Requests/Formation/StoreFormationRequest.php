<?php

declare(strict_types=1);

namespace App\Http\Requests\Formation;

use App\Models\Formation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Formation::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'slug' => [
                'required', 'string', 'min:2', 'max:120',
                'regex:/^[a-z0-9][a-z0-9\-]*$/i',
                Rule::unique('formations', 'slug'),
            ],
            'title' => ['required', 'string', 'min:2', 'max:200'],
            'duration' => ['nullable', 'string', 'max:60'],
            'format' => ['nullable', 'string', 'max:60'],
            'level' => ['nullable', 'string', 'max:60'],
            'cover_url' => ['nullable', 'string', 'max:500', 'url'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'audience' => ['nullable', 'string', 'max:5000'],
            'method' => ['nullable', 'string', 'max:5000'],
            'certification' => ['nullable', 'string', 'max:5000'],

            // Flat list of strings.
            'objectives' => ['sometimes', 'array', 'max:30'],
            'objectives.*' => ['string', 'min:1', 'max:300'],

            // Nested: array of { title, items: string[] }.
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

        // Trim & drop empty objectives.
        $objectives = $this->input('objectives');
        if (is_array($objectives)) {
            $this->merge([
                'objectives' => array_values(array_filter(
                    array_map(fn ($v) => is_string($v) ? trim($v) : $v, $objectives),
                    fn ($v) => $v !== '' && $v !== null,
                )),
            ]);
        }

        // For each category, trim title + drop empty items. Drop the whole
        // category if it has no title AND no items.
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

        // Empty-string URL/text → null.
        foreach (['cover_url', 'summary', 'audience', 'method', 'certification', 'duration', 'format', 'level'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'slug' => 'identifiant',
            'title' => 'titre',
            'duration' => 'durée',
            'format' => 'format',
            'level' => 'niveau',
            'cover_url' => 'image de couverture',
            'summary' => 'résumé',
            'audience' => 'public visé',
            'method' => 'méthode',
            'certification' => 'certification',
            'objectives' => 'objectifs',
            'categories' => 'catégories de cours',
            'categories.*.title' => 'titre de catégorie',
            'categories.*.items.*' => 'élément de catégorie',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => "L'identifiant ne peut contenir que des lettres minuscules, chiffres et tirets.",
            'slug.unique' => "Cet identifiant est déjà utilisé.",
        ];
    }
}
