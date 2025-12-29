<?php

namespace App\Notifications\Delivery;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryRegistrationNotification extends Notification
{
    use Queueable;

    private $delivery;

    public function __construct($delivery)
    {
        $this->delivery = $delivery;
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $delivery = $this->delivery;
        return (new MailMessage)
                ->from('support@sparex.com')->view('email.notification.delivery.delivery-registration',compact('delivery'))
                ->subject('New Delivery Registration');
    }

    public function toArray(object $notifiable): array
    {
        $delivery = $this->delivery;
        return [
            'data' => 'New Delivery Registration',
            'delivery_id' => $delivery['id'], 
        ];
    }
}
