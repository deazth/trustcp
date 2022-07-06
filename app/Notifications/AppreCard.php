<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppreCard extends Notification  implements ShouldQueue
{
    use Queueable;
    private $card;
    public $tries = 1;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($card)
    {
      $this->card = $card;
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
      // $pdf = \PDF::loadView('staff.appcards', [
      //   'card' => $this->card
      // ]);

        return (new MailMessage)
          ->line('Dear ' . $this->card->recipient->name . ',')
          ->line($this->card->sender->name . ' has sent you an appreciation card.')
          ->action('View the card', route('notify.read', ['id' => $this->id]))
          ->line('Have a good and productive day!')
          // ->attachData($pdf->output(), 'postcard.pdf')
          ;
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
        'id' => $this->card->id,
        'route_name' => 'appreciatecard.preview',
        'param' => ['cid' => $this->card->id],
        'text' => 'You received a card from ' . $this->card->sender->name,
        'icon' => 'lar la-envelope',
        'type' => 'card_received'
      ];
    }
}
