<?php

declare(strict_types=1);

namespace App\Services\RegistrationConcours;

use App\Models\RegistrationConcours;
use App\Models\User;
use App\Notifications\NewRegistrationConcoursNotification;
use App\Notifications\RegistrationConcoursAcknowledgementNotification;
use App\Support\Enums\ArchitectureConcours;
use App\Support\Enums\EnaFiliere;
use App\Support\Enums\EnaRegionalGrade;
use App\Support\Enums\RegistrationConcoursStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Single entry point for ENA lead mutations.
 *
 * Centralises:
 *   - priority computation on create + on update if the qualifying
 *     fields ever change (currently they don't, but easy to extend)
 *   - dual notifications (admin alert + lead acknowledgement)
 *   - status-change audit fields
 */
final class RegistrationConcoursService
{
    /**
     * @param array{
     *   full_name:string, whatsapp_phone:string, email:string,
     *   filiere:EnaFiliere, regional_grade:EnaRegionalGrade,
     *   city:string, concours_vise:array<int, string>,
     *   preferred_format:\App\Support\Enums\EnaPreferredFormat,
     *   message:?string, passed_ena_before:bool
     * } $data
     */
    public function create(array $data, Request $request): RegistrationConcours
    {
        $lead = RegistrationConcours::query()->create([
            ...$data,
            'status' => RegistrationConcoursStatus::New,
            'priority' => RegistrationConcours::computePriority($data['filiere'], $data['regional_grade']),
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255) ?: null,
        ]);

        $this->dispatchNotifications($lead);

        return $lead->fresh();
    }

    /**
     * @param array{status?: string|RegistrationConcoursStatus, admin_notes?: ?string} $changes
     */
    public function update(RegistrationConcours $lead, array $changes, ?User $actor = null): RegistrationConcours
    {
        if (array_key_exists('status', $changes) && $changes['status'] !== null) {
            $new = $changes['status'] instanceof RegistrationConcoursStatus
                ? $changes['status']
                : RegistrationConcoursStatus::from((string) $changes['status']);

            if ($new !== $lead->status) {
                $lead->status = $new;
                $lead->status_changed_at = now();
                $lead->status_changed_by = $actor?->id;
                Log::info('Concours lead status changed', [
                    'lead_id' => $lead->id,
                    'new_status' => $new->value,
                    'by' => $actor?->id,
                ]);
            }
        }

        if (array_key_exists('admin_notes', $changes)) {
            $lead->admin_notes = $changes['admin_notes'];
        }

        $lead->save();

        return $lead->fresh(['statusChangedBy']);
    }

    public function delete(RegistrationConcours $lead): void
    {
        $lead->delete();
    }

    private function dispatchNotifications(RegistrationConcours $lead): void
    {
        try {
            $adminEmail = (string) config('lions.notify_email');
            if ($adminEmail !== '') {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewRegistrationConcoursNotification($lead));
            }
            $lead->notify(new RegistrationConcoursAcknowledgementNotification($lead));
        } catch (\Throwable $e) {
            Log::warning('Concours lead notification dispatch failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
