<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Polymorphic-friendly media record. One row per uploaded file (cover image,
 * gallery item, trainer photo, inscription document, etc.).
 *
 * The actual binary lives on a Laravel filesystem disk; this row stores
 * the disk name + path so the URL can be rebuilt at any time (and a
 * future migration to S3/R2 is just an env switch).
 *
 * @property int $id
 * @property string $disk
 * @property string $path
 * @property string $mime
 * @property int $size_bytes
 * @property string|null $original_name
 * @property int|null $width
 * @property int|null $height
 * @property string|null $alt_text
 * @property string|null $checksum
 * @property string $visibility   "public" | "private"
 * @property int|null $uploaded_by
 */
class MediaAsset extends Model
{
    /** @use HasFactory<\Database\Factories\MediaAssetFactory> */
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'mime',
        'size_bytes',
        'original_name',
        'width',
        'height',
        'alt_text',
        'checksum',
        'visibility',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Resolved URL: public assets get a permanent URL, private ones get
     * a short-lived signed URL through the secure download route.
     */
    protected function url(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->visibility === 'private') {
                return URL::temporarySignedRoute(
                    'media.download',
                    now()->addMinutes(15),
                    ['media' => $this->id],
                );
            }

            return Storage::disk($this->disk)->url($this->path);
        });
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }
}
