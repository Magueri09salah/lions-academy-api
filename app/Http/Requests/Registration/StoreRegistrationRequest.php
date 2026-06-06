<?php

declare(strict_types=1);

namespace App\Http\Requests\Registration;

use App\Models\Formation;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates payloads submitted by the public inscription form
 * (lion-s-roar-academy/src/routes/inscription.tsx).
 *
 * The frontend's HTML field names — name / phone / level / formation —
 * are accepted verbatim so the form works without changes. They are
 * mapped to canonical snake_case columns in toRegistrationData().
 *
 * The privacy checkbox needs `name="privacy_accepted"` (or accept_privacy
 * — both supported) when the form is wired to fetch. This is the only
 * non-existing HTML attribute the form is missing today, and adding a
 * name attribute to an existing input does not change the payload
 * "structure" — it just makes the existing field submittable.
 */
class StoreRegistrationRequest extends FormRequest
{
    /** Public endpoint — anyone can submit. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Accept both the frontend's primary field names and snake_case
     * aliases. Aliases let the API stay usable from cURL/Postman scripts.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Primary frontend names → canonical
            'full_name' => $this->input('full_name', $this->input('name')),
            'whatsapp_phone' => $this->normalisePhone(
                $this->input('whatsapp_phone', $this->input('phone'))
            ),
            'education_level' => $this->input('education_level', $this->input('level')),
            'formation' => $this->input('formation', $this->input('formation_title')),
            // Accept either privacy_accepted (preferred) or accept_privacy / accept
            'privacy_accepted' => $this->boolean(
                $this->has('privacy_accepted') ? 'privacy_accepted'
                    : ($this->has('accept_privacy') ? 'accept_privacy' : 'accept')
            ),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $docMaxKb = (int) config('lions.uploads.document.max_kb');
        $docMimes = implode(',', (array) config('lions.uploads.document.mimes'));
        $docMimeTypes = implode(',', (array) config('lions.uploads.document.mime_types'));

        return [
            'full_name' => ['required', 'string', 'min:2', 'max:150'],
            'whatsapp_phone' => [
                'required', 'string', 'min:8', 'max:32',
                'regex:/^\+?[0-9\s\-().]{8,}$/',
            ],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:200'],
            'city' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'education_level' => ['required', 'string', 'max:64'],
            'profession' => ['nullable', 'string', 'max:120'],
            'formation' => ['required', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:5000'],
            'privacy_accepted' => ['required', 'accepted'],

            // Optional Turnstile token. Verified in the controller via
            // App\Services\Security\TurnstileVerifier when configured.
            'turnstile_token' => ['nullable', 'string', 'max:2048'],

            // Files (frontend posts as documents[])
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => [
                'file',
                'max:'.$docMaxKb,
                'mimes:'.$docMimes,
                'mimetypes:'.$docMimeTypes,
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'full_name' => 'nom complet',
            'whatsapp_phone' => 'téléphone WhatsApp',
            'email' => 'adresse email',
            'city' => 'ville',
            'address' => 'adresse précise',
            'education_level' => "niveau d'étude",
            'profession' => 'profession',
            'formation' => 'formation',
            'message' => 'message',
            'privacy_accepted' => 'politique de confidentialité',
            'documents.*' => 'document',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'privacy_accepted.accepted' => 'Vous devez accepter la politique de confidentialité.',
            'whatsapp_phone.regex' => 'Le numéro WhatsApp est invalide.',
        ];
    }

    /**
     * Resolve the formation reference into a Formation model so the
     * controller / service receives a guaranteed FK on success.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $ref = (string) $this->input('formation');
            $formation = Formation::findByReference($ref);

            if ($formation === null) {
                $v->errors()->add('formation', "Cette formation n'est pas disponible.");

                return;
            }

            if (! $formation->is_active) {
                $v->errors()->add('formation', "Les inscriptions à cette formation sont closes.");

                return;
            }

            // Expose the resolved model to the controller via the request.
            $this->attributes->set('resolved_formation', $formation);
        });
    }

    public function resolvedFormation(): Formation
    {
        return $this->attributes->get('resolved_formation');
    }

    /**
     * Build the canonical payload the service layer expects.
     *
     * @return array{
     *     full_name:string, whatsapp_phone:string, email:string, city:string,
     *     address:string, education_level:string, profession:?string,
     *     formation_id:int, formation_title:string, message:?string,
     *     privacy_accepted:bool
     * }
     */
    public function toRegistrationData(): array
    {
        $formation = $this->resolvedFormation();

        return [
            'full_name' => (string) $this->validated('full_name'),
            'whatsapp_phone' => (string) $this->validated('whatsapp_phone'),
            'email' => (string) $this->validated('email'),
            'city' => (string) $this->validated('city'),
            'address' => (string) $this->validated('address'),
            'education_level' => (string) $this->validated('education_level'),
            'profession' => $this->validated('profession'),
            'formation_id' => $formation->id,
            'formation_title' => $formation->title,
            'message' => $this->validated('message'),
            'privacy_accepted' => (bool) $this->validated('privacy_accepted'),
        ];
    }

    /**
     * Strip whitespace and unify "00" prefix → "+" for E.164-ish numbers.
     * Stored as-is for human readability; we just lightly normalize.
     */
    private function normalisePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if (str_starts_with($trimmed, '00')) {
            $trimmed = '+'.substr($trimmed, 2);
        }

        return preg_replace('/[ \-\.()]/', '', $trimmed);
    }
}
