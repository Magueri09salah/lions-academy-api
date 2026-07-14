<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RegistrationConcours;
use App\Services\Registration\WhatsAppLinkBuilder;
use App\Support\Enums\ArchitectureConcours;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full ENA lead projection. Used by:
 *   - POST /api/v1/registrations-concours (public, with whatsapp_redirect_url)
 *   - GET  /api/v1/admin/registrations-concours/{id}
 *
 * @mixin RegistrationConcours
 */
class RegistrationConcoursResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user() !== null;
        $whatsapp = app(WhatsAppLinkBuilder::class);

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
            // Array of selected concours so the admin UI can render
            // one badge per choice. Each item: { value, label }.
            'concours_vise' => array_map(
                fn (string $v) => [
                    'value' => $v,
                    'label' => ArchitectureConcours::tryFrom($v)?->label() ?? $v,
                ],
                (array) ($this->concours_vise ?? []),
            ),
            'message' => $this->message,
            'passed_ena_before' => (bool) $this->passed_ena_before,
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

            'admin_notes' => $this->when($isAdmin, $this->admin_notes),
            'ip_address' => $this->when($isAdmin, $this->ip_address),
            'user_agent' => $this->when($isAdmin, $this->user_agent),
            'status_changed_at' => $this->when($isAdmin, $this->status_changed_at?->toIso8601String()),
            'status_changed_by' => $this->when(
                $isAdmin,
                fn () => $this->whenLoaded('statusChangedBy', fn () => [
                    'id' => $this->statusChangedBy?->id,
                    'name' => $this->statusChangedBy?->name,
                ]),
            ),

            // WhatsApp helpers — public response gets the academy URL, admin
            // additionally gets a deep-link to the lead's own number.
            'whatsapp_redirect_url' => $whatsapp->forAcademy($this->buildAcademyMessage()),
            'whatsapp_applicant_url' => $this->when(
                $isAdmin,
                fn () => $whatsapp->forApplicant(
                    $this->whatsapp_phone,
                    $this->buildApplicantMessage(),
                ),
            ),

            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function buildAcademyMessage(): string
    {
        return sprintf(
            'Bonjour Lions Academie, je viens de soumettre une demande de préparation au concours ENA sous le nom %s. Merci de me recontacter.',
            $this->full_name,
        );
    }

    private function buildApplicantMessage(): string
    {
        return sprintf(
            'Bonjour %s, c\'est Lions Academie. Nous revenons vers vous concernant votre demande de préparation au concours ENA.',
            $this->full_name,
        );
    }
}
