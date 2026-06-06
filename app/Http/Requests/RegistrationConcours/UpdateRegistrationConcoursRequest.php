<?php

declare(strict_types=1);

namespace App\Http\Requests\RegistrationConcours;

use App\Models\RegistrationConcours;
use App\Support\Enums\RegistrationConcoursStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin update for an ENA lead.
 *
 * Mutable fields are intentionally limited: status, admin_notes.
 * Identity / qualification answers stay immutable so the audit trail
 * of "what the lead originally answered" survives.
 */
class UpdateRegistrationConcoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var RegistrationConcours|null $lead */
        $lead = $this->route('lead');

        return $this->user()?->can('update', $lead ?? RegistrationConcours::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(RegistrationConcoursStatus::class)],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
