<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\RegistrationConcours;
use App\Services\Registration\WhatsAppLinkBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Votre demande a bien été reçue" — confirms reception to the lead.
 */
class RegistrationConcoursAcknowledgementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RegistrationConcours $lead) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $l = $this->lead;
        $waAcademy = app(WhatsAppLinkBuilder::class)->forAcademy();

        $mail = (new MailMessage)
            ->subject('Votre demande a bien été reçue — Préparation Concours ENA')
            ->greeting('Bonjour '.$l->full_name.',')
            ->line('Votre demande pour la préparation au concours ENA a bien été reçue.')
            ->line('Notre équipe vous recontactera prochainement pour finaliser votre inscription et répondre à vos questions.');

        if ($waAcademy !== null) {
            $mail->action('Discuter sur WhatsApp', $waAcademy);
        }

        return $mail
            ->line('À très bientôt,')
            ->line('L\'équipe Lions Academy.');
    }

    /** @return array<string, mixed> */
    public function toArray(mixed $notifiable): array
    {
        return ['lead_id' => $this->lead->id];
    }
}
