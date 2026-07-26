<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte ' . config('app.name') . '.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien de réinitialisation expirera dans ' . Config::get('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) . ' minutes.')
            ->line('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune autre action n\'est requise.')
            ->salutation('Cordialement, l\'équipe ' . config('app.name'));
    }
    protected function resetUrl($notifiable): string
    {
        return rtrim(config('app.frontend_url'), '/') . '/auth/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
