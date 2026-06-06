<?php

declare(strict_types=1);

namespace App\Http\Requests\Registration;

use App\Models\Registration;
use App\Support\Enums\RegistrationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin update — only mutable fields are status + admin_notes.
 * Identity / contact fields are intentionally immutable; if they are
 * wrong, the admin should reject the row rather than rewrite it.
 */
class UpdateRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Registration|null $registration */
        $registration = $this->route('registration');

        return $this->user()?->can('update', $registration ?? Registration::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(RegistrationStatus::class)],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
