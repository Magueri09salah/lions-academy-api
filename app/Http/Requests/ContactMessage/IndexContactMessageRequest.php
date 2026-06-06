<?php

declare(strict_types=1);

namespace App\Http\Requests\ContactMessage;

use App\Models\ContactMessage;
use App\Support\Enums\ContactMessageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin list/search/filter for contact messages.
 *
 * Accepts:
 *   ?q=<term>
 *   ?status=new,read              (CSV) or repeated ?status[]=
 *   ?date_from=YYYY-MM-DD&date_to=...
 *   ?sort=-submitted_at|submitted_at|status|name|email
 *   ?per_page=20  (1..100, default 20)
 *   ?page=1
 */
class IndexContactMessageRequest extends FormRequest
{
    private const SORTABLE = ['submitted_at', 'status', 'name', 'email', 'subject', 'created_at'];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', ContactMessage::class) === true;
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        if (is_string($status) && str_contains($status, ',')) {
            $this->merge([
                'status' => array_values(array_filter(array_map('trim', explode(',', $status)))),
            ]);
        } elseif (is_string($status) && $status !== '') {
            $this->merge(['status' => [$status]]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'array'],
            'status.*' => [Rule::enum(ContactMessageStatus::class)],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
            'sort' => [
                'sometimes', 'string',
                Rule::in($this->allowedSortValues()),
            ],
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
        return str_starts_with((string) ($this->validated('sort') ?? '-submitted_at'), '-')
            ? 'desc'
            : 'asc';
    }

    /** @return array<int, ContactMessageStatus> */
    public function statusFilter(): array
    {
        $values = (array) ($this->validated('status') ?? []);

        return array_values(array_filter(array_map(
            fn ($v) => ContactMessageStatus::tryFrom((string) $v),
            $values,
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
