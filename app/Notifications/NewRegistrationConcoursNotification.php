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
 * Sent to LIONS_NOTIFY_EMAIL when a new ENA lead lands.
 * Queued so a slow SMTP host never blocks the public POST.
 */
class NewRegistrationConcoursNotification extends Notification implements ShouldQueue
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
        $adminUrl = rtrim((string) config('lions.admin_url'), '/');
        $detailUrl = $adminUrl !== '' ? $adminUrl.'/concours/'.$l->id : null;
        $waApplicant = app(WhatsAppLinkBuilder::class)->forApplicant($l->whatsapp_phone);

        $mail = (new MailMessage)
            ->subject(sprintf('Nouveau lead ENA [%s] — %s', strtoupper($l->priority?->value ?? 'n/a'), $l->full_name))
            ->greeting('Bonjour,')
            ->line('Un nouveau lead pour la préparation au concours ENA vient d\'être enregistré.')
            ->line('**Candidat·e :** '.$l->full_name)
            ->line('**Email :** '.$l->email)
            ->line('**WhatsApp :** '.$l->whatsapp_phone)
            ->line('**Ville :** '.$l->city)
            ->line('**Filière :** '.($l->filiere?->label() ?? '—'))
            ->line('**Note régionale :** '.($l->regional_grade?->label() ?? '—'))
            ->line('**Format souhaité :** '.($l->preferred_format?->label() ?? '—'))
            ->line('**A déjà passé le concours ENA :** '.($l->passed_ena_before ? 'Oui' : 'Non'))
            ->line('**Priorité commerciale :** '.($l->priority?->label() ?? '—'));

        if ($detailUrl !== null) {
            $mail->action('Voir dans le back-office', $detailUrl);
        }
        if ($waApplicant !== null) {
            $mail->line('Lien WhatsApp direct : '.$waApplicant);
        }

        return $mail->line('— Lions Academy');
    }

    /** @return array<string, mixed> */
    public function toArray(mixed $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'priority' => $this->lead->priority?->value,
        ];
    }
}
