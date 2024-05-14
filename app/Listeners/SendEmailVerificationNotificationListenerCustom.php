<?php

namespace App\Listeners;

use App\Events\RegistationEvent;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerificationNotificationListenerCustom implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RegistationEvent $event)
    {
        $user = $event->user;

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
