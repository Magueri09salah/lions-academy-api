<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot between a Registration and the MediaAssets it uploaded.
 *
 * Carries a free-form `label` so the same registration can hold multiple
 * documents of different types (photo, CIN, diplôme, etc.) without us
 * needing a separate column per kind.
 *
 * @property int $id
 * @property int $registration_id
 * @property int $media_asset_id
 * @property string|null $label
 */
class RegistrationDocument extends Pivot
{
    protected $table = 'registration_documents';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'registration_id',
        'media_asset_id',
        'label',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}
