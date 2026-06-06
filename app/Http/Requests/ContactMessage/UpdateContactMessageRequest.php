<?php

declare(strict_types=1);

namespace App\Http\Requests\ContactMessage;

use App\Models\ContactMessage;
use App\Support\Enums\ContactMessageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin update — only status + admin_notes are mutable. Identity / message
 * body are immutable so the audit trail of "what was originally sent"
 * stays intact.
 */
class UpdateContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ContactMessage|null $message */
        $message = $this->route('message');

        return $this->user()?->can('update', $message ?? ContactMessage::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(ContactMessageStatus::class)],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
