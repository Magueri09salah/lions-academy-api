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
 * Sent to LIONS_NOTIFY_EMAIL whenever a new registration is submitted.
 *
 * Queued so a slow SMTP provider never holds up the public-facing
 * POST response (target ≤ 1s end-to-end).
 */
class NewRegistrationNotification extends Notification implements ShouldQueue
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
        $adminUrl = rtrim((string) config('lions.admin_url'), '/');
        $detailUrl = $adminUrl !== '' ? $adminUrl.'/registrations/'.$r->id : null;
        $whatsappApplicant = app(WhatsAppLinkBuilder::class)
            ->forApplicant($r->whatsapp_phone);

        $mail = (new MailMessage)
            ->subject(sprintf('Nouvelle inscription — %s', $r->full_name))
            ->greeting('Bonjour,')
            ->line('Une nouvelle demande d\'inscription a été reçue.')
            ->line('**Candidat·e :** '.$r->full_name)
            ->line('**Email :** '.$r->email)
            ->line('**WhatsApp :** '.$r->whatsapp_phone)
            ->line('**Ville :** '.$r->city)
            ->line('**Niveau :** '.$r->education_level)
            ->line('**Formation :** '.($r->formation_title ?? '—'))
            ->line('**Disponibilité :** '.$r->availability);

        if (filled($r->profession)) {
            $mail->line('**Profession :** '.$r->profession);
        }

        if (filled($r->message)) {
            $mail->line('**Message :**')->line($r->message);
        }

        if ($detailUrl !== null) {
            $mail->action('Voir dans le back-office', $detailUrl);
        }

        if ($whatsappApplicant !== null) {
            $mail->line('Lien WhatsApp direct : '.$whatsappApplicant);
        }

        return $mail->line('— Lions Academy');
    }

    /** @return array<string, mixed> */
    public function toArray(mixed $notifiable): array
    {
        return [
            'registration_id' => $this->registration->id,
            'full_name' => $this->registration->full_name,
            'email' => $this->registration->email,
        ];
    }
}
