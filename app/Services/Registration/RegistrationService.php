<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Models\MediaAsset;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\NewRegistrationNotification;
use App\Notifications\RegistrationAcknowledgementNotification;
use App\Services\Media\MediaService;
use App\Support\Enums\RegistrationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Single entry point for registration mutations.
 *
 * Why a service rather than controller logic:
 *   - the create path involves multipart files + DB rows in two tables;
 *     wrapping it in a transaction here keeps controllers thin.
 *   - notifications (admin + applicant) need to fire only AFTER the
 *     transaction commits so we never email about a roll-backed row.
 *   - the status-change path needs audit fields (changed_at / changed_by)
 *     set in lock-step.
 */
final class RegistrationService
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * Persist a new registration with its optional documents.
     *
     * @param  array<string, mixed>  $data         from StoreRegistrationRequest::toRegistrationData()
     * @param  array<int, UploadedFile>|null  $documents
     */
    public function create(array $data, ?array $documents, Request $request): Registration
    {
        $registration = DB::transaction(function () use ($data, $documents, $request): Registration {
            $reg = Registration::query()->create(array_merge($data, [
                'status' => RegistrationStatus::New,
                'submitted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255) ?: null,
            ]));

            if ($documents) {
                $this->attachDocuments($reg, $documents);
            }

            return $reg;
        });

        $this->dispatchNotifications($registration);

        return $registration->fresh(['formation', 'documents']);
    }

    /**
     * @param  array{status?: string|RegistrationStatus, admin_notes?: ?string}  $changes
     */
    public function update(Registration $registration, array $changes, ?User $actor = null): Registration
    {
        $statusChanged = false;

        if (array_key_exists('status', $changes) && $changes['status'] !== null) {
            $new = $changes['status'] instanceof RegistrationStatus
                ? $changes['status']
                : RegistrationStatus::from((string) $changes['status']);

            if ($new !== $registration->status) {
                $registration->status = $new;
                $registration->status_changed_at = now();
                $registration->status_changed_by = $actor?->id;
                $statusChanged = true;
            }
        }

        if (array_key_exists('admin_notes', $changes)) {
            $registration->admin_notes = $changes['admin_notes'];
        }

        $registration->save();

        if ($statusChanged) {
            Log::info('Registration status changed', [
                'registration_id' => $registration->id,
                'new_status' => $registration->status->value,
                'by' => $actor?->id,
            ]);
        }

        return $registration->fresh(['formation', 'documents', 'statusChangedBy']);
    }

    public function delete(Registration $registration): void
    {
        DB::transaction(function () use ($registration): void {
            // Cascade is set at the SQL level for registration_documents,
            // but the underlying media files are not auto-deleted — we
            // do it here so admin "delete" really removes everything.
            foreach ($registration->documents as $media) {
                $this->media->delete($media);
            }
            $registration->delete();
        });
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function attachDocuments(Registration $registration, array $files): void
    {
        $folder = sprintf('registrations/%d', $registration->id);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $media = $this->media->storePrivateDocument(
                file: $file,
                folder: $folder,
                uploader: null,
            );

            $registration->documents()->attach($media->id, [
                'label' => $this->guessLabel($file, $media),
            ]);
        }
    }

    /**
     * Naive label heuristic — useful for the admin UI badge ("photo" / "cin" / "doc")
     * but not relied upon by any business logic.
     */
    private function guessLabel(UploadedFile $file, MediaAsset $media): string
    {
        $name = strtolower((string) $file->getClientOriginalName());

        if (str_contains($name, 'cin') || str_contains($name, 'cni')) {
            return 'cin';
        }

        if (str_contains($name, 'photo') || str_contains((string) $media->mime, 'image/')) {
            return 'photo';
        }

        return 'document';
    }

    private function dispatchNotifications(Registration $registration): void
    {
        try {
            // Admin notification — routed to LIONS_NOTIFY_EMAIL.
            $adminEmail = (string) config('lions.notify_email');
            if ($adminEmail !== '') {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewRegistrationNotification($registration));
            }

            // Applicant acknowledgement — routed via the model's
            // `routeNotificationForMail()` method.
            $registration->notify(new RegistrationAcknowledgementNotification($registration));
        } catch (\Throwable $e) {
            // Never let a failing mail driver block a registration.
            Log::warning('Registration notification dispatch failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
