<?php

declare(strict_types=1);

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\MediaAsset::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('lions.uploads.video.max_kb');
        $mimes = implode(',', (array) config('lions.uploads.video.mimes'));
        $mimeTypes = implode(',', (array) config('lions.uploads.video.mime_types'));

        return [
            'file' => [
                'required', 'file',
                'max:'.$maxKb,
                'mimes:'.$mimes,
                'mimetypes:'.$mimeTypes,
            ],
            'folder' => ['sometimes', 'string', 'max:120', 'regex:/^[a-z0-9\-_\/]+$/i'],
            'alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'visibility' => ['sometimes', Rule::in(['public'])],
        ];
    }

    public function folder(): string
    {
        return (string) ($this->validated('folder') ?? 'videos');
    }
}
