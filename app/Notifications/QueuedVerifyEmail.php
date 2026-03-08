<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;
       protected function verificationUrl($notifiable): string
    {
        $apiSignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );

        $parsed = parse_url($apiSignedUrl);
        parse_str($parsed['query'] ?? '', $params);

        return rtrim(config('app.frontend_url'), '/') . '/auth/verify-email?' . http_build_query([
            'id'        => $notifiable->getKey(),
            'hash'      => sha1($notifiable->getEmailForVerification()),
            'expires'   => $params['expires'] ?? '',
            'signature' => $params['signature'] ?? '',
        ]);
    }
}
