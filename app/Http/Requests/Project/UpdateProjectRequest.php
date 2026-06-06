<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return $this->user()?->can('update', $project ?? Project::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'student_name' => ['sometimes', 'required', 'string', 'min:1', 'max:150'],
            'promotion' => ['sometimes', 'required', 'string', 'min:1', 'max:50'],
            'category' => ['sometimes', 'required', 'string', 'min:1', 'max:100'],
            'status' => ['sometimes', 'required', 'string', 'min:1', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'cover_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'gallery_urls' => ['sometimes', 'array', 'max:20'],
            'gallery_urls.*' => ['string', 'max:500', 'url'],
            'software' => ['sometimes', 'array', 'max:20'],
            'software.*' => ['string', 'min:1', 'max:100'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['gallery_urls', 'software'] as $field) {
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
        foreach (['cover_url', 'description'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }
}
