<?php

declare(strict_types=1);

namespace App\Http\Requests\Trainer;

use App\Models\Trainer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Trainer|null $trainer */
        $trainer = $this->route('trainer');

        return $this->user()?->can('update', $trainer ?? Trainer::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Trainer|null $trainer */
        $trainer = $this->route('trainer');

        return [
            'slug' => [
                'sometimes', 'required', 'string', 'min:2', 'max:60',
                'regex:/^[a-z0-9][a-z0-9\-]*$/i',
                Rule::unique('trainers', 'slug')->ignore($trainer?->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'role' => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'specialty' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'experience' => ['sometimes', 'required', 'string', 'min:1', 'max:30'],
            'initials' => ['sometimes', 'required', 'string', 'min:1', 'max:8'],
            'photo_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'modules' => ['sometimes', 'array', 'max:30'],
            'modules.*' => ['string', 'min:1', 'max:200'],
            'software' => ['sometimes', 'array', 'max:30'],
            'software.*' => ['string', 'min:1', 'max:100'],
            'instagram_url' => ['sometimes', 'nullable', 'string', 'max:300', 'url'],
            'linkedin_url' => ['sometimes', 'nullable', 'string', 'max:300', 'url'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
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

        foreach (['photo_url', 'instagram_url', 'linkedin_url', 'bio'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }
}
