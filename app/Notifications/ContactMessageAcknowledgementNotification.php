<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ContactMessage;
use App\Services\Registration\WhatsAppLinkBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms message reception to the sender. Mirrors the inscription
 * acknowledgement — same tone, same WhatsApp CTA so applicants who
 * just contacted us know they can also continue the conversation there.
 */
class ContactMessageAcknowledgementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $message) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $m = $this->message;
        $whatsappAcademy = app(WhatsAppLinkBuilder::class)->forAcademy();

        $mail = (new MailMessage)
            ->subject('Votre message a bien été reçu — Lions Academy')
            ->greeting('Bonjour '.$m->name.',')
            ->line('Nous avons bien reçu votre message concernant : **'.$m->subject.'**.')
            ->line('Notre équipe vous répond généralement sous 48 heures ouvrées.');

        if ($whatsappAcademy !== null) {
            $mail->action('Discuter sur WhatsApp', $whatsappAcademy);
        }

        return $mail
            ->line('À très bientôt,')
            ->line('L\'équipe Lions Academy.');
    }

    /** @return array<string, mixed> */
    public function toArray(mixed $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
        ];
    }
}
