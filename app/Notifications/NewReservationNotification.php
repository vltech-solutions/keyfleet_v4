<?php

namespace App\Notifications;

use App\Models\Reservation;
// Inalis ang Queueable at ShouldQueue
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewReservationNotification extends Notification
{
    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $slug = $this->reservation->company->slug;

        $targetUrl = url("/app/{$slug}/reservations/" . $this->reservation->id);
		// $targetUrl = url("/pwa-login");
        return (new WebPushMessage)
            ->title('New Booking: #' . $this->reservation->reservation_number)
            ->icon('/icons/icon.png')
            ->body("Customer {$this->reservation->customer->customer_name} booked a car.")
            ->data(['url' => $targetUrl])
      		->action('View App', $targetUrl);
      
    }
}