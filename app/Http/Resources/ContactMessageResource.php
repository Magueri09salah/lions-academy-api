<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full contact-message projection. Used by:
 *   - POST /api/v1/contact-messages          (public, no admin fields)
 *   - GET  /api/v1/admin/contact-messages/{id}
 *
 * The reply_mailto field is built server-side so the admin UI can offer a
 * one-click "Répondre par email" with a prefilled "Re: <subject>" header.
 *
 * @mixin ContactMessage
 */
class ContactMessageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isAdminContext = $request->user() !== null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'tone' => $this->status?->tone(),
            ],

            // Admin-only fields
            'admin_notes' => $this->when($isAdminContext, $this->admin_notes),
            'ip_address' => $this->when($isAdminContext, $this->ip_address),
            'user_agent' => $this->when($isAdminContext, $this->user_agent),
            'read_at' => $this->when($isAdminContext, $this->read_at?->toIso8601String()),
            'replied_at' => $this->when($isAdminContext, $this->replied_at?->toIso8601String()),
            'handled_by' => $this->when(
                $isAdminContext,
                fn () => $this->whenLoaded('handler', fn () => [
                    'id' => $this->handler?->id,
                    'name' => $this->handler?->name,
                ]),
            ),

            // Convenience: prefilled mailto link for "Répondre par email".
            'reply_mailto' => $this->when(
                $isAdminContext,
                fn () => sprintf(
                    'mailto:%s?subject=%s',
                    rawurlencode($this->email),
                    rawurlencode('Re: '.$this->subject),
                ),
            ),

            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
