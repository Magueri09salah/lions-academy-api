<?php

declare(strict_types=1);

namespace App\Http\Requests\RegistrationConcours;

use App\Models\RegistrationConcours;
use App\Support\Enums\EnaFiliere;
use App\Support\Enums\EnaPreferredFormat;
use App\Support\Enums\EnaRegionalGrade;
use App\Support\Enums\RegistrationConcoursPriority;
use App\Support\Enums\RegistrationConcoursStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin list/search/filter for ENA leads.
 *
 *   ?q=
 *   ?status=new,contacted          (CSV) or repeated ?status[]=
 *   ?priority=high,medium          (CSV) or repeated
 *   ?filiere=sciences_math_a
 *   ?city=Marrakech
 *   ?regional_grade=14_to_16
 *   ?preferred_format=online
 *   ?date_from=…&date_to=…
 *   ?sort=-priority|-submitted_at|...
 *   ?per_page=1..100 (default 20)
 *   ?page=1
 */
class IndexRegistrationConcoursRequest extends FormRequest
{
    private const SORTABLE = [
        'submitted_at',
        'status',
        'priority',
        'full_name',
        'email',
        'city',
        'created_at',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', RegistrationConcours::class) === true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['status', 'priority'] as $key) {
            $value = $this->input($key);
            if (is_string($value) && str_contains($value, ',')) {
                $this->merge([$key => array_values(array_filter(array_map('trim', explode(',', $value))))]);
            } elseif (is_string($value) && $value !== '') {
                $this->merge([$key => [$value]]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'array'],
            'status.*' => [Rule::enum(RegistrationConcoursStatus::class)],
            'priority' => ['sometimes', 'array'],
            'priority.*' => [Rule::enum(RegistrationConcoursPriority::class)],
            'filiere' => ['sometimes', 'nullable', Rule::enum(EnaFiliere::class)],
            'regional_grade' => ['sometimes', 'nullable', Rule::enum(EnaRegionalGrade::class)],
            'preferred_format' => ['sometimes', 'nullable', Rule::enum(EnaPreferredFormat::class)],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['sometimes', 'string', Rule::in($this->allowedSortValues())],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 20);
    }

    public function sortField(): string
    {
        $sort = (string) ($this->validated('sort') ?? '-submitted_at');
        return ltrim($sort, '-');
    }

    public function sortDirection(): string
    {
        $sort = (string) ($this->validated('sort') ?? '-submitted_at');
        return str_starts_with($sort, '-') ? 'desc' : 'asc';
    }

    /** @return array<int, RegistrationConcoursStatus> */
    public function statusFilter(): array
    {
        return array_values(array_filter(array_map(
            fn ($v) => RegistrationConcoursStatus::tryFrom((string) $v),
            (array) ($this->validated('status') ?? []),
        )));
    }

    /** @return array<int, RegistrationConcoursPriority> */
    public function priorityFilter(): array
    {
        return array_values(array_filter(array_map(
            fn ($v) => RegistrationConcoursPriority::tryFrom((string) $v),
            (array) ($this->validated('priority') ?? []),
        )));
    }

    /** @return array<int, string> */
    private function allowedSortValues(): array
    {
        $values = [];
        foreach (self::SORTABLE as $col) {
            $values[] = $col;
            $values[] = '-'.$col;
        }
        return $values;
    }
}
