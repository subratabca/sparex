<?php

namespace App\Notifications\MealOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderAcceptedByRestaurantNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $deliveryLedger;
    private $mealOrder;
    private $mealType;
    private $client;
    private $mealOrderItems;
    private $shippingAddress;

    public function __construct($deliveryLedger, $mealOrder, $mealType, $client, $mealOrderItems, $shippingAddress)
    {
        $this->deliveryLedger = $deliveryLedger;
        $this->mealOrder = $mealOrder;
        $this->mealType = $mealType;
        $this->client = $client;
        $this->mealOrderItems = $mealOrderItems;
        $this->shippingAddress = $shippingAddress;
    }

    public function via(object $notifiable): array
    {
        return ['database']; 
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'data' => 'Order Accepted by Restaurant',
            'meal_order_id' => $this->deliveryLedger->meal_order_id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'data' => 'Order Accepted by Restaurant',
            'meal_order_id' => $this->deliveryLedger->meal_order_id,
        ];
    }
}