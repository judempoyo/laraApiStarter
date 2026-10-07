<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorResetByAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $reason = null)
    {
        $this->locale(app()->getLocale());
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = rtrim((string) (config('app.frontend_url') ?? config('app.url')), '/') . '/auth/login';

        $message = (new MailMessage)
            ->subject(__('notifications.2fa_reset_by_admin_subject') ?: 'Security: Two-Factor Authentication Reset by Administrator')
            ->greeting(__('notifications.greeting', ['name' => $notifiable->name]) ?: "Hello {$notifiable->name},")
            ->line(__('notifications.2fa_reset_by_admin_line1') ?: 'Two-factor authentication (2FA) on your account has been reset by an administrator.');

        if (! empty($this->reason)) {
            $message->line((__('notifications.reason') ?: 'Reason') . " : {$this->reason}");
        }

        $message
            ->line(__('notifications.2fa_reset_by_admin_line2') ?: 'For your security, all active sessions on your account have been terminated.')
            ->line(__('notifications.2fa_reset_by_admin_line3') ?: 'You may now log in using your standard credentials.')
            ->action(__('notifications.login') ?: 'Log In to My Account', $loginUrl)
            ->line(__('notifications.2fa_reset_by_admin_line4') ?: 'We strongly recommend re-enabling two-factor authentication in your security settings upon your next login.')
            ->salutation(__('notifications.salutation', ['name' => config('app.name')]) ?: 'Regards, ' . config('app.name'));

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'   => '2fa_reset_by_admin',
            'reason' => $this->reason,
        ];
    }
}
