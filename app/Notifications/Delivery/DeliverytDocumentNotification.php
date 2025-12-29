<?php

namespace App\Notifications\Delivery;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliverytDocumentNotification extends Notification
{
    use Queueable;

    private $deliveryUser;

    public function __construct($deliveryUser)
    {
        $this->deliveryUser = $deliveryUser;
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deliveryUser = $this->deliveryUser;
        return (new MailMessage)
                ->from('support@sparex.com')->view('email.notification.delivery.delivery-document',compact('deliveryUser'))
                ->subject('New Delivery Document');
    }

    public function toArray(object $notifiable): array
    {
        $deliveryUser = $this->deliveryUser;
        return [
            'data' => 'New Delivery Document',
            'client_id' => $deliveryUser['id'], 
        ];
    }
}
