<?php

namespace App\Notifications\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdateNotification extends Notification
{
    use Queueable;

    private $order;
    private $clientOrder; // store clientOrder instead of just status

    public function __construct($order, $clientOrder = null)
    {
        $this->order = $order;
        $this->clientOrder = $clientOrder; 
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $clientOrder = $this->clientOrder;

        return (new MailMessage)
            ->from('support@sparex.com')
            ->view('email.notification.order.order_status_update', [
                'order'       => $order,
                'clientOrder' => $clientOrder, 
            ])
            ->subject('Order Status Update');
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->order;
        return [
            'data' => 'Order Status Update',
            'order_id' => $this->order->id
        ];
    }
}


