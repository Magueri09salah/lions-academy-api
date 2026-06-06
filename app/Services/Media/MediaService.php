<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Single entry point for uploading files into the system.
 *
 * Responsibilities:
 *   - choose a target disk based on requested visibility
 *   - validate MIME at upload time via PHP's `finfo` (defense in depth on
 *     top of the FormRequest mimes:* rule)
 *   - compute checksum (used to dedupe + detect replay)
 *   - read intrinsic image dimensions for downstream layout
 *   - persist a MediaAsset row pointing to the stored binary
 *
 * Image transforms (thumbs, webp conversion) are NOT performed here —
 * that's a follow-up concern (queued job or on-the-fly via intervention/image).
 */
final class MediaService
{
    /** @var array<int, string> */
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/avif',
    ];

    /** @var array<int, string> */
    private const ALLOWED_DOCUMENT_MIMES = [
        'image/jpeg', 'image/png', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** @var array<int, string> */
    private const ALLOWED_VIDEO_MIMES = [
        'video/mp4',
        'video/webm',
        'video/quicktime',
    ];

    public function storeVideo(
        UploadedFile $file,
        string $folder,
        ?User $uploader = null,
        ?string $alt = null,
    ): MediaAsset {
        return $this->store(
            file: $file,
            folder: $folder,
            disk: 'public',
            visibility: 'public',
            allowedMimes: self::ALLOWED_VIDEO_MIMES,
            uploader: $uploader,
            alt: $alt,
        );
    }

    public function storeImage(
        UploadedFile $file,
        string $folder,
        ?User $uploader = null,
        ?string $alt = null,
    ): MediaAsset {
        return $this->store(
            file: $file,
            folder: $folder,
            disk: 'public',
            visibility: 'public',
            allowedMimes: self::ALLOWED_IMAGE_MIMES,
            uploader: $uploader,
            alt: $alt,
        );
    }

    public function storePrivateDocument(
        UploadedFile $file,
        string $folder,
        ?User $uploader = null,
    ): MediaAsset {
        return $this->store(
            file: $file,
            folder: $folder,
            disk: 'private',
            visibility: 'private',
            allowedMimes: self::ALLOWED_DOCUMENT_MIMES,
            uploader: $uploader,
        );
    }

    public function delete(MediaAsset $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    /**
     * @param  array<int, string>  $allowedMimes
     */
    private function store(
        UploadedFile $file,
        string $folder,
        string $disk,
        string $visibility,
        array $allowedMimes,
        ?User $uploader,
        ?string $alt = null,
    ): MediaAsset {
        if (! $file->isValid()) {
            throw new RuntimeException('Fichier invalide.');
        }

        // Re-verify MIME server-side; do not trust client-supplied content-type.
        $detectedMime = $file->getMimeType() ?: 'application/octet-stream';
        if (! in_array($detectedMime, $allowedMimes, true)) {
            throw new RuntimeException(sprintf('Type de fichier non autorisé (%s).', $detectedMime));
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $filename = sprintf('%s_%s.%s', now()->format('YmdHis'), Str::random(12), $extension);
        $path = trim($folder, '/').'/'.$filename;

        $checksum = hash_file('sha256', $file->getRealPath()) ?: null;

        Storage::disk($disk)->putFileAs(
            path: dirname($path),
            file: $file,
            name: basename($path),
            options: ['visibility' => $visibility],
        );

        [$width, $height] = $this->readImageDimensions($file);

        return MediaAsset::query()->create([
            'disk' => $disk,
            'path' => $path,
            'mime' => $detectedMime,
            'size_bytes' => $file->getSize() ?: 0,
            'original_name' => Str::limit((string) $file->getClientOriginalName(), 200, ''),
            'width' => $width,
            'height' => $height,
            'alt_text' => $alt,
            'checksum' => $checksum,
            'visibility' => $visibility,
            'uploaded_by' => $uploader?->id,
        ]);
    }

    /** @return array{0:?int,1:?int} */
    private function readImageDimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return [null, null];
        }

        $size = @getimagesize($file->getRealPath());
        if (! is_array($size)) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }
}
