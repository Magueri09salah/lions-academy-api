<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend consumes a flat `url` field — covers, gallery items, photos
 * are all just strings in the existing frontend (`src/lib/data.ts`).
 *
 * @mixin MediaAsset
 */
class MediaAssetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'mime' => $this->mime,
            'size_bytes' => $this->size_bytes,
            'width' => $this->width,
            'height' => $this->height,
            'alt_text' => $this->alt_text,
            'original_name' => $this->original_name,
            'visibility' => $this->visibility,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
