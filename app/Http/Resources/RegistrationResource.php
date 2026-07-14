<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Registration;
use App\Services\Registration\WhatsAppLinkBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full registration projection. Used by:
 *   - POST /api/v1/registrations  (public, with `whatsapp_redirect_url`)
 *   - GET  /api/v1/admin/registrations/{id}
 *
 * The `whatsapp_redirect_url` is built server-side so the frontend can
 * offer "Continuer sur WhatsApp" right after a successful submission —
 * matches the existing UX of the inscription page where the WhatsApp
 * CTA is prominent next to the form.
 *
 * @mixin Registration
 */
class RegistrationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $whatsapp = app(WhatsAppLinkBuilder::class);
        $isAdminContext = $request->user() !== null;

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'whatsapp_phone' => $this->whatsapp_phone,
            'city' => $this->city,
            'address' => $this->address,
            'education_level' => $this->education_level,
            'profession' => $this->profession,
            'formation' => [
                'id' => $this->formation_id,
                'title' => $this->formation_title,
                'detail' => $this->whenLoaded('formation', fn () => new FormationResource($this->formation)),
            ],
            'message' => $this->message,
            'privacy_accepted' => (bool) $this->privacy_accepted,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'tone' => $this->status?->tone(),
            ],

            // Admin-only fields (notes, audit, IP/UA): exposed only to
            // authenticated staff. Public response strips them.
            'admin_notes' => $this->when($isAdminContext, $this->admin_notes),
            'ip_address' => $this->when($isAdminContext, $this->ip_address),
            'user_agent' => $this->when($isAdminContext, $this->user_agent),
            'status_changed_at' => $this->when($isAdminContext, $this->status_changed_at?->toIso8601String()),
            'status_changed_by' => $this->when(
                $isAdminContext,
                fn () => $this->whenLoaded('statusChangedBy', fn () => [
                    'id' => $this->statusChangedBy?->id,
                    'name' => $this->statusChangedBy?->name,
                ]),
            ),

            'documents' => $this->whenLoaded(
                'documents',
                fn () => MediaAssetResource::collection($this->documents),
                fn () => [],
            ),

            // WhatsApp helpers — the frontend uses these directly.
            'whatsapp_redirect_url' => $whatsapp->forAcademy(
                $this->buildAcademyMessage(),
            ),
            'whatsapp_applicant_url' => $this->when(
                $isAdminContext,
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

    /**
     * Pre-filled message FROM the applicant TO the academy, shown right
     * after submission so they can continue the conversation on WhatsApp.
     */
    private function buildAcademyMessage(): string
    {
        return sprintf(
            'Bonjour Lions Academie, je viens de soumettre une demande d\'inscription à "%s" sous le nom %s. Merci de me recontacter.',
            $this->formation_title ?? 'la formation',
            $this->full_name,
        );
    }

    /**
     * Pre-filled message FROM the admin TO the applicant, used inside the
     * back-office "WhatsApp" quick-action button.
     */
    private function buildApplicantMessage(): string
    {
        return sprintf(
            'Bonjour %s, c\'est Lions Academie. Nous revenons vers vous concernant votre inscription à "%s".',
            $this->full_name,
            $this->formation_title ?? 'la formation',
        );
    }
}
