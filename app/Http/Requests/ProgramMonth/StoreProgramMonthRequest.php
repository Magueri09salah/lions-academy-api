<?php

declare(strict_types=1);

namespace App\Http\Requests\ProgramMonth;

use App\Models\ProgramMonth;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgramMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProgramMonth::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'formation_id' => ['required', 'integer', 'exists:formations,id'],
            'position' => ['required', 'integer', 'min:1', 'max:255'],
            'month_label' => ['required', 'string', 'min:1', 'max:32'],
            'title' => ['required', 'string', 'min:2', 'max:200'],
            'axis' => ['required', 'string', 'min:2', 'max:200'],
            'objective' => ['required', 'string', 'min:2', 'max:5000'],
            'deliverable' => ['required', 'string', 'min:2', 'max:200'],
            'items' => ['sometimes', 'array', 'max:30'],
            'items.*' => ['string', 'min:1', 'max:200'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'formation_id' => 'formation',
            'position' => 'position',
            'month_label' => 'libellé du mois',
            'title' => 'titre',
            'axis' => 'axe',
            'objective' => 'objectif',
            'deliverable' => 'rendu',
            'items' => 'éléments',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Drop empty strings from the items array so admins can leave a
        // trailing blank input in the UI without it counting as a value.
        $items = $this->input('items');
        if (is_array($items)) {
            $this->merge([
                'items' => array_values(array_filter(
                    array_map(fn ($v) => is_string($v) ? trim($v) : $v, $items),
                    fn ($v) => $v !== '' && $v !== null,
                )),
            ]);
        }
    }
}
