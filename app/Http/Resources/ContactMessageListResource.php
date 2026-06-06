<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact projection for the admin messages table.
 *
 * @mixin ContactMessage
 */
class ContactMessageListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'preview' => mb_substr($this->message, 0, 120),
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'tone' => $this->status?->tone(),
            ],
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
