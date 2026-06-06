<?php

declare(strict_types=1);

namespace App\Http\Requests\Principle;

use App\Models\Principle;
use Illuminate\Foundation\Http\FormRequest;

class StorePrincipleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Principle::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:200'],
            'description' => ['required', 'string', 'min:5', 'max:5000'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'description' => 'description',
            'display_order' => 'ordre',
            'is_active' => 'actif',
        ];
    }
}
