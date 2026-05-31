<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class TestPushNotification extends Notification
{
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
      	$url = url('/app/vl-tech/reservations/45');
        return (new WebPushMessage)
            ->title('Keyfleet Test!')
            ->icon('/icons/icon.png')
            ->body('Gumagana na ang push notifications mo, bro!!!!')
            ->action('View App', $url)
          	->data(['url' => $url]);
    }
}