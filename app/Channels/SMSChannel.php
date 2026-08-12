<?php

namespace App\Channels;

use App\Mixins\Notifications\SendSMS;
use Illuminate\Notifications\Notification;

class SMSChannel
{

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSMS($notifiable); // @phpstan-ignore-line

        $sendSMS = (new SendSMS($message['to'], $message['content']));
        $sendSMS->send();
    }
}
