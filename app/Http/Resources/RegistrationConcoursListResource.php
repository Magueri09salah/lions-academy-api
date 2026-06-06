<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RegistrationConcours;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact projection for the admin leads table.
 *
 * @mixin RegistrationConcours
 */
class RegistrationConcoursListResource extends JsonResource
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
            'filiere' => [
                'value' => $this->filiere?->value,
                'label' => $this->filiere?->label(),
            ],
            'regional_grade' => [
                'value' => $this->regional_grade?->value,
                'label' => $this->regional_grade?->label(),
            ],
            'preferred_format' => [
                'value' => $this->preferred_format?->value,
                'label' => $this->preferred_format?->label(),
            ],
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'tone' => $this->status?->tone(),
            ],
            'priority' => [
                'value' => $this->priority?->value,
                'label' => $this->priority?->label(),
                'tone' => $this->priority?->tone(),
            ],
            'passed_ena_before' => (bool) $this->passed_ena_before,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
