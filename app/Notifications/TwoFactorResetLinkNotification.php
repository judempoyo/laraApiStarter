<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorResetLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $resetUrl)
    {
        $this->locale(app()->getLocale());
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.2fa_reset_link_subject') ?: 'Security: Two-Factor Authentication Reset Link')
            ->greeting(__('notifications.greeting', ['name' => $notifiable->name]) ?: "Hello {$notifiable->name},")
            ->line(__('notifications.2fa_reset_link_line1') ?: 'You requested a reset of your two-factor authentication (2FA) because you no longer have access to your authenticator app or recovery codes.')
            ->action(__('notifications.2fa_reset_link_action') ?: 'Reset Two-Factor Authentication', $this->resetUrl)
            ->line(__('notifications.2fa_reset_link_expire') ?: 'This secure reset link will expire in 15 minutes.')
            ->line(__('notifications.2fa_reset_link_warning') ?: 'If you did not request this reset, please change your password immediately as someone may be attempting to access your account.')
            ->salutation(__('notifications.salutation', ['name' => config('app.name')]) ?: 'Regards, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => '2fa_reset_link_requested',
        ];
    }
}
