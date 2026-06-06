<?php

declare(strict_types=1);

namespace App\Http\Requests\Trainer;

use App\Models\Trainer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Trainer::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'slug' => [
                'required', 'string', 'min:2', 'max:60',
                'regex:/^[a-z0-9][a-z0-9\-]*$/i',
                Rule::unique('trainers', 'slug'),
            ],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'role' => ['required', 'string', 'min:2', 'max:150'],
            'specialty' => ['required', 'string', 'min:2', 'max:200'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'experience' => ['required', 'string', 'min:1', 'max:30'],
            'initials' => ['required', 'string', 'min:1', 'max:8'],
            'photo_url' => ['nullable', 'string', 'max:500', 'url'],
            'modules' => ['sometimes', 'array', 'max:30'],
            'modules.*' => ['string', 'min:1', 'max:200'],
            'software' => ['sometimes', 'array', 'max:30'],
            'software.*' => ['string', 'min:1', 'max:100'],
            'instagram_url' => ['nullable', 'string', 'max:300', 'url'],
            'linkedin_url' => ['nullable', 'string', 'max:300', 'url'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalise slug + strip empty array entries (same logic as ProgramMonth).
        $slug = $this->input('slug');
        if (is_string($slug)) {
            $this->merge(['slug' => mb_strtolower(trim($slug))]);
        }

        foreach (['modules', 'software'] as $field) {
            $value = $this->input($field);
            if (is_array($value)) {
                $this->merge([
                    $field => array_values(array_filter(
                        array_map(fn ($v) => is_string($v) ? trim($v) : $v, $value),
                        fn ($v) => $v !== '' && $v !== null,
                    )),
                ]);
            }
        }

        // Empty-string URL fields become null so the `url` rule doesn't reject blanks.
        foreach (['photo_url', 'instagram_url', 'linkedin_url', 'bio'] as $field) {
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
            'name' => 'nom',
            'role' => 'rôle',
            'specialty' => 'spécialité',
            'bio' => 'biographie',
            'experience' => 'expérience',
            'initials' => 'initiales',
            'photo_url' => 'photo',
            'modules' => 'modules',
            'software' => 'logiciels',
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
