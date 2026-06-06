<?php

declare(strict_types=1);

namespace App\Http\Requests\Principle;

use App\Models\Principle;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrincipleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Principle|null $principle */
        $principle = $this->route('principle');

        return $this->user()?->can('update', $principle ?? Principle::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'description' => ['sometimes', 'required', 'string', 'min:5', 'max:5000'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
