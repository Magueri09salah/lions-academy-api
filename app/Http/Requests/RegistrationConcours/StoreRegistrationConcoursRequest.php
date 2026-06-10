<?php

declare(strict_types=1);

namespace App\Http\Requests\RegistrationConcours;

use App\Support\EnaCities;
use App\Support\Enums\ArchitectureConcours;
use App\Support\Enums\EnaFiliere;
use App\Support\Enums\EnaPreferredFormat;
use App\Support\Enums\EnaRegionalGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates submissions from the public ENA-prep landing form
 * (lion-s-roar-academy/src/routes/concours-ena.tsx).
 *
 * Accepts the form field names verbatim — no remapping needed since
 * frontend + backend agree on snake_case from the start.
 */
class StoreRegistrationConcoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'whatsapp_phone' => $this->normalisePhone($this->input('whatsapp_phone')),
            'email' => trim(mb_strtolower((string) $this->input('email'))),
            'city' => trim((string) $this->input('city')),
            'concours_vise' => $this->input('concours_vise') ?: ArchitectureConcours::Ena->value,
            'message' => is_string($this->input('message')) ? trim($this->input('message')) : null,
            'passed_ena_before' => $this->boolean('passed_ena_before'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:150'],
            'whatsapp_phone' => [
                'required', 'string', 'min:8', 'max:32',
                'regex:/^\+?[0-9\s\-().]{8,}$/',
            ],
            // Spec says "Adresse Gmail" — we still accept any valid RFC
            // email so non-Gmail leads aren't rejected at the gate.
            'email' => ['required', 'string', 'email:rfc', 'max:200'],
            'filiere' => ['required', Rule::enum(EnaFiliere::class)],
            'regional_grade' => ['required', Rule::enum(EnaRegionalGrade::class)],
            'city' => ['required', 'string', Rule::in(EnaCities::LIST)],
            'concours_vise' => ['required', Rule::enum(ArchitectureConcours::class)],
            'preferred_format' => ['required', Rule::enum(EnaPreferredFormat::class)],
            'message' => ['nullable', 'string', 'max:2000'],
            'passed_ena_before' => ['required', 'boolean'],

            // Anti-spam (verified server-side in the controller when configured).
            'turnstile_token' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'full_name' => 'nom complet',
            'whatsapp_phone' => 'numéro WhatsApp',
            'email' => 'adresse email',
            'filiere' => 'filière',
            'regional_grade' => 'note régionale',
            'city' => 'ville',
            'concours_vise' => 'concours visé',
            'preferred_format' => 'format de préparation',
            'message' => 'message',
            'passed_ena_before' => 'tentative précédente du concours ENA',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'whatsapp_phone.regex' => 'Le numéro WhatsApp est invalide.',
        ];
    }

    /**
     * Canonical payload for the service layer.
     *
     * @return array<string, mixed>
     */
    public function toLeadData(): array
    {
        return [
            'full_name' => (string) $this->validated('full_name'),
            'whatsapp_phone' => (string) $this->validated('whatsapp_phone'),
            'email' => (string) $this->validated('email'),
            'filiere' => EnaFiliere::from((string) $this->validated('filiere')),
            'regional_grade' => EnaRegionalGrade::from((string) $this->validated('regional_grade')),
            'city' => (string) $this->validated('city'),
            'concours_vise' => ArchitectureConcours::from((string) $this->validated('concours_vise')),
            'preferred_format' => EnaPreferredFormat::from((string) $this->validated('preferred_format')),
            'message' => $this->validated('message') ? (string) $this->validated('message') : null,
            'passed_ena_before' => (bool) $this->validated('passed_ena_before'),
        ];
    }

    private function normalisePhone(?string $value): ?string
    {
        if ($value === null) return null;
        $t = trim($value);
        if (str_starts_with($t, '00')) $t = '+'.substr($t, 2);
        return preg_replace('/[ \-\.()]/', '', $t);
    }
}
