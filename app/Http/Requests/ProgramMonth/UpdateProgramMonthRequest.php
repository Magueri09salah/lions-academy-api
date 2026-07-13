<?php

declare(strict_types=1);

namespace App\Http\Requests\ProgramMonth;

use App\Models\ProgramMonth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ProgramMonth|null $month */
        $month = $this->route('month');

        return $this->user()?->can('update', $month ?? ProgramMonth::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'formation_id' => ['sometimes', 'required', 'integer', 'exists:formations,id'],
            'position' => ['sometimes', 'required', 'integer', 'min:1', 'max:255'],
            'month_label' => ['sometimes', 'required', 'string', 'min:1', 'max:32'],
            'title' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'axis' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'objective' => ['sometimes', 'required', 'string', 'min:2', 'max:5000'],
            'deliverable' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'items' => ['sometimes', 'array', 'max:30'],
            'items.*' => ['string', 'min:1', 'max:200'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
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
