<?php

namespace App\Notifications\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Models\ClientOrder;

class NewOrderNotification extends Notification
{
    use Queueable;

    private $order;
    private $clientOrder;

    public function __construct($order)
    {
        if($order instanceof Order) {
            $this->order = $order->load([
                'customer',
                'orderItems.client',
                'orderItems.product', 
                'orderItems.variant',
                'orderItems.product.category',
                'orderItems.product.brand'
            ]);
        } else if($order instanceof ClientOrder) {
            $this->clientOrder = $order->load([
                'client',
                'order.customer',
                'order.orderItems' => function($query) use ($order) {
                    $query->where('client_id', $order->client_id)
                        ->with(['product', 'variant', 'product.category', 'product.brand']);
                }
            ]);
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from('support@sparex.com')
            ->view('email.notification.order.new_order', [
                'order' => $this->order,
                'clientOrder' => $this->clientOrder
            ])
            ->subject('New Order Notification');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'data' => 'New Order Received',
            'order_id' => $this->order ? $this->order->id : $this->clientOrder->order_id
        ];
    }
}