<?php

declare(strict_types=1);

namespace App\Services\ContactMessage;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageAcknowledgementNotification;
use App\Notifications\NewContactMessageNotification;
use App\Support\Enums\ContactMessageStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Single entry point for contact-message mutations.
 *
 * Mirrors RegistrationService — create wraps the DB row + outbound
 * notifications, update sets audit fields in lock-step with the status
 * transition, delete is straightforward (no related uploads to clean up).
 */
final class ContactMessageService
{
    public function create(array $data, Request $request): ContactMessage
    {
        $message = ContactMessage::query()->create(array_merge($data, [
            'status' => ContactMessageStatus::New,
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255) ?: null,
        ]));

        $this->dispatchNotifications($message);

        return $message->fresh();
    }

    /**
     * @param  array{status?: string|ContactMessageStatus, admin_notes?: ?string}  $changes
     */
    public function update(ContactMessage $message, array $changes, ?User $actor = null): ContactMessage
    {
        if (array_key_exists('status', $changes) && $changes['status'] !== null) {
            $new = $changes['status'] instanceof ContactMessageStatus
                ? $changes['status']
                : ContactMessageStatus::from((string) $changes['status']);

            if ($new !== $message->status) {
                $message->status = $new;
                $message->handled_by = $actor?->id;

                // Audit timestamps tied to specific transitions.
                if ($new === ContactMessageStatus::Read && $message->read_at === null) {
                    $message->read_at = now();
                }
                if ($new === ContactMessageStatus::Replied) {
                    $message->replied_at = now();
                    if ($message->read_at === null) {
                        $message->read_at = now();
                    }
                }
            }
        }

        if (array_key_exists('admin_notes', $changes)) {
            $message->admin_notes = $changes['admin_notes'];
        }

        $message->save();

        return $message->fresh(['handler']);
    }

    /**
     * Convenience used when the admin opens a "new" message's detail page —
     * implicitly moves it to "read" without requiring a separate click.
     */
    public function markRead(ContactMessage $message, ?User $actor = null): ContactMessage
    {
        if ($message->status === ContactMessageStatus::New) {
            return $this->update(
                $message,
                ['status' => ContactMessageStatus::Read],
                $actor,
            );
        }

        return $message;
    }

    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }

    private function dispatchNotifications(ContactMessage $message): void
    {
        try {
            $adminEmail = (string) config('lions.notify_email');
            if ($adminEmail !== '') {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewContactMessageNotification($message));
            }
            $message->notify(new ContactMessageAcknowledgementNotification($message));
        } catch (\Throwable $e) {
            // Never let a failing mail driver block submission.
            Log::warning('Contact message notification dispatch failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
