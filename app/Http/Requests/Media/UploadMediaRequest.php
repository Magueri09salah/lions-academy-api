<?php

declare(strict_types=1);

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\MediaAsset::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('lions.uploads.image.max_kb');
        $allowedMimes = implode(',', config('lions.uploads.image.mimes'));

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxKb,
                'mimes:'.$allowedMimes,
                'mimetypes:'.implode(',', config('lions.uploads.image.mime_types')),
            ],
            'folder' => ['sometimes', 'string', 'max:120', 'regex:/^[a-z0-9\-_\/]+$/i'],
            'alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'visibility' => ['sometimes', Rule::in(['public'])],
        ];
    }

    public function folder(): string
    {
        return (string) ($this->validated('folder') ?? 'uploads');
    }
}
