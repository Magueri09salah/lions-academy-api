<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', Password::min(8)],
            'device_name' => ['sometimes', 'string', 'max:64'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function deviceName(): string
    {
        return (string) ($this->validated('device_name')
            ?? substr($this->userAgent() ?? 'unknown', 0, 64));
    }
}
