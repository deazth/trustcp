<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\ExpoChannel;

class SeatBookExpired extends Notification implements ShouldQueue
{
    use Queueable;
    private $booking_id;
    private $seat_lbl;
    private $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($bookobj)
    {
      $this->booking_id = $bookobj->id;
      $this->seat_lbl = $bookobj->Seat->label;
      $this->status = $bookobj->status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', ExpoChannel::Class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
          'id' => $this->booking_id,
          'route_name' => 'reserveseat.index',
          'param' => ['id' => $this->booking_id],
          'text' => 'Seat reservation for ' . $this->seat_lbl . ' : ' . $this->status,
          'icon' => 'la la-heart-broken',
          'type' => 'seat_expire',
          'title' => 'Seat Reservation Alert'
        ];
    }

}
