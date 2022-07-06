<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\common\PushNotiHelper;

class ExpoChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
      if(isset($notifiable->pushnoti_id)){
        $info = $notification->toArray($notifiable);

        $sresp = PushNotiHelper::SendPushNoti(
          $notifiable->pushnoti_id,
          $info['title'],
          $info['text'],
          gethostname(),
          $notifiable->id,
          $info['type']
        );
      }
    }
}
