<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to LIONS_NOTIFY_EMAIL whenever a new contact message arrives.
 *
 * Queued so a slow SMTP provider never blocks the public-facing
 * POST response.
 */
class NewContactMessageNotification extends Notification implements ShouldQueue
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
        $adminUrl = rtrim((string) config('lions.admin_url'), '/');
        $detailUrl = $adminUrl !== '' ? $adminUrl.'/messages/'.$m->id : null;

        $mail = (new MailMessage)
            ->subject(sprintf('Nouveau message — %s', $m->subject))
            ->greeting('Bonjour,')
            ->line('Un nouveau message a été reçu via le formulaire de contact.')
            ->line('**De :** '.$m->name.' <'.$m->email.'>')
            ->line('**Sujet :** '.$m->subject);

        if (filled($m->phone)) {
            $mail->line('**Téléphone :** '.$m->phone);
        }

        $mail->line('**Message :**')
            ->line($m->message);

        if ($detailUrl !== null) {
            $mail->action('Voir dans le back-office', $detailUrl);
        }

        // Direct reply link.
        $mail->line('Répondre directement : mailto:'.$m->email);

        return $mail->line('— Lions Academy');
    }

    /** @return array<string, mixed> */
    public function toArray(mixed $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'name' => $this->message->name,
            'subject' => $this->message->subject,
        ];
    }
}
