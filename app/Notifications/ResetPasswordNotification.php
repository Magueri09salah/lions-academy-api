<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends a password-reset email pointing to the admin SPA route
 * (LIONS_ADMIN_URL/reset-password?token=...&email=...).
 *
 * Replaces Laravel's default notification which would point to a
 * web route on this API host.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $adminUrl = rtrim((string) config('lions.admin_url'), '/');
        $resetUrl = sprintf(
            '%s/reset-password?token=%s&email=%s',
            $adminUrl,
            urlencode($this->token),
            urlencode((string) $notifiable->getEmailForPasswordReset()),
        );

        $minutes = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Réinitialisation de mot de passe — Lions Academy')
            ->greeting('Bonjour,')
            ->line('Vous recevez cet email parce que nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.')
            ->action('Réinitialiser le mot de passe', $resetUrl)
            ->line(sprintf('Ce lien expire dans %d minutes.', $minutes))
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.");
    }
}
