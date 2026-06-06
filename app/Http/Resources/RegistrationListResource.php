<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact projection for the admin registrations table. Keeps the
 * payload small for paginated list views and excludes the message body,
 * admin notes, and document attachments — fetched via the detail
 * endpoint instead.
 *
 * @mixin Registration
 */
class RegistrationListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'whatsapp_phone' => $this->whatsapp_phone,
            'city' => $this->city,
            'address' => $this->address,
            'education_level' => $this->education_level,
            'formation' => [
                'id' => $this->formation_id,
                'title' => $this->formation_title,
            ],
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'tone' => $this->status?->tone(),
            ],
            'has_documents' => $this->whenLoaded(
                'documents',
                fn () => $this->documents->isNotEmpty(),
                fn () => null,
            ),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
