<?php

declare(strict_types=1);

namespace App\Http\Requests\ContactMessage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates submissions from the public contact form
 * (lion-s-roar-academy/src/routes/contact.tsx).
 *
 * Accepts the frontend's HTML field names verbatim — `name`, `email`,
 * `phone`, `subject`, `message`. No mapping needed since both sides
 * already agree on these names.
 */
class StoreContactMessageRequest extends FormRequest
{
    /** Public endpoint — anyone can submit. */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Trim whitespace consistently; null out empty optional fields.
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => trim((string) $this->input('email')),
            'phone' => $this->input('phone') ? trim((string) $this->input('phone')) : null,
            'subject' => trim((string) $this->input('subject')),
            'message' => (string) $this->input('message'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'string', 'email:rfc', 'max:200'],
            'phone' => [
                'nullable', 'string', 'max:32',
                'regex:/^\+?[0-9\s\-().]{6,}$/',
            ],
            'subject' => ['required', 'string', 'min:2', 'max:200'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],

            // Optional Turnstile token (no-op until configured).
            'turnstile_token' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse email',
            'phone' => 'téléphone',
            'subject' => 'sujet',
            'message' => 'message',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Le numéro de téléphone est invalide.',
        ];
    }

    /**
     * Canonical payload for the service layer.
     *
     * @return array{name:string, email:string, phone:?string, subject:string, message:string}
     */
    public function toMessageData(): array
    {
        return [
            'name' => (string) $this->validated('name'),
            'email' => (string) $this->validated('email'),
            'phone' => $this->validated('phone'),
            'subject' => (string) $this->validated('subject'),
            'message' => (string) $this->validated('message'),
        ];
    }
}
