<?php

namespace App\Notifications\MealOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MealOrder;
use App\Models\ClientMealOrder;

class NewMealOrderNotification extends Notification
{
    use Queueable;

    private $mealOrder;
    private $clientMealOrder;

    public function __construct($order)
    {
        if($order instanceof MealOrder) {
            $this->mealOrder = $order->load([
                'customer',
                'items.client',
                'items.product', 
                'items.mealType',
                'clientMealOrders',
                'mealShippingAddress'
            ]);
        } else if($order instanceof ClientMealOrder) {
            $this->clientMealOrder = $order->load([
                'client',
                'mealOrder.customer',
                'mealOrder.items' => function($query) use ($order) {
                    $query->where('client_id', $order->client_id)
                        ->with(['product', 'mealType']);
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
        $subject = $this->getSubject();
        
        return (new MailMessage)
            ->from('support@sparex.com')
            ->view('email.notification.meal-order.new_meal_order', [
                'mealOrder' => $this->mealOrder,
                'clientMealOrder' => $this->clientMealOrder
            ])
            ->subject($subject);
    }

    public function toArray(object $notifiable): array
    {
        if ($this->mealOrder) {
            return [
                'data' => 'New Meal Order Received',
                'meal_order_id' => $this->mealOrder->id,
                'order_number' => $this->mealOrder->order_number,
                'type' => 'main_order'
            ];
        } else {
            return [
                'data' => 'New Meal Order',
                'meal_order_id' => $this->clientMealOrder->meal_order_id,
                'client_order_id' => $this->clientMealOrder->id,
                'type' => 'client_order'
            ];
        }
    }

    private function getSubject(): string
    {
        if ($this->mealOrder) {
            return 'New Meal Order #' . $this->mealOrder->order_number;
        } else {
            return 'New Meal Order #' . $this->clientMealOrder->mealOrder->order_number;
        }
    }
}