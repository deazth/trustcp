<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AreaBooking;

class AreaHijacked extends Notification implements ShouldQueue
{
    use Queueable;

    private $bookid;
    private $hijackerid;
    private $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($myid, $hijackerid, $type)
    {
      $this->bookid = $myid;
      $this->hijackerid = $hijackerid;
      $this->type = $type; // 1 = subset. 2 = fully cancelled
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

      $mine = AreaBooking::find($this->bookid);
      if($this->type == 1){
        return (new MailMessage)
                    ->line('Your area booking for ' . $mine->event_name . ' is hijacked by admin')
                    ->action('View overlapping event', route('notify.read', ['id' => $this->id]))
                    ->line('We apologize for any inconveniences');
      } else {
        return (new MailMessage)
                    ->line('Your area booking for ' . $mine->event_name . ' is cancelled by admin for another event')
                    ->action('View my booking', route('notify.read', ['id' => $this->id]))
                    ->line('We apologize for any inconveniences');
      }

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
      $mine = AreaBooking::find($this->bookid);

      if($this->type == 1){
        return [
          'id' => $this->bookid,
          'route_name' => 'inv.event.info',
          'param' => ['id' => $this->hijackerid],
          'text' => 'Your area booking for ' . $mine->event_name . ' is hijacked by admin',
          'icon' => 'la la-heart-broken',
          'type' => 'area_hijack'
        ];
      } else {
        return [
          'id' => $this->bookid,
          'route_name' => 'userareabooking.index',
          'param' => ['id' => $this->bookid],
          'text' => 'Your area booking for ' . $mine->event_name . ' is cancelled by admin for another event',
          'icon' => 'la la-heart-broken',
          'type' => 'area_hijack'
        ];
      }

    }
}
