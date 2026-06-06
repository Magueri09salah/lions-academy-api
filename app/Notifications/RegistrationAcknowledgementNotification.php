<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Registration;
use App\Services\Registration\WhatsAppLinkBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms reception of the inscription to the applicant. Matches the
 * UX text shown on the form's success screen (cf. CPS §6.8) so the
 * applicant gets the same message both in-app and by email.
 */
class RegistrationAcknowledgementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Registration $registration) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $r = $this->registration;
        $whatsappAcademy = app(WhatsAppLinkBuilder::class)->forAcademy();

        $mail = (new MailMessage)
            ->subject('Demande d\'inscription reçue — Lions Academy')
            ->greeting('Bonjour '.$r->full_name.',')
            ->line('Votre demande d\'inscription à Lions Academy a bien été enregistrée.')
            ->line('Formation choisie : **'.($r->formation_title ?? 'Lions Academy').'**')
            ->line('Notre équipe vous contactera prochainement pour finaliser votre inscription.');

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
            'registration_id' => $this->registration->id,
        ];
    }
}
