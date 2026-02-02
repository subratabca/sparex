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

    // Remove 'mail' from the via method - ONLY database notifications
    public function via(object $notifiable): array
    {
        return ['database']; // Only database, no email
    }

    // Remove the toMail method entirely since we don't want emails

    public function toDatabase(object $notifiable): array
    {
        // Get meal_time from the first meal order item
        $mealTime = $this->mealOrderItems->first()->meal_time ?? null;
        $formattedMealTime = $mealTime ? date('h:i A', strtotime($mealTime)) : 'N/A';

        return [
            'title' => 'Order Accepted by Restaurant',
            'message' => 'Your ' . $this->mealType->name . ' order has been accepted and is being prepared by ' . $this->client->firstName,
            'data' => [
                'delivery_charge_ledger_id' => $this->deliveryLedger->id,
                'order_tracking' => $this->deliveryLedger->order_tracking,
                'meal_order_id' => $this->deliveryLedger->meal_order_id,
                'client_name' => $this->client->firstName . ' ' . $this->client->lastName,
                'meal_type' => $this->mealType->name,
                'delivery_date' => $this->deliveryLedger->delivery_date,
                'meal_time' => $mealTime, // Add meal_time here
                'formatted_meal_time' => $formattedMealTime, // Add formatted time for display
                'items_count' => $this->mealOrderItems->count(),
            ],
            'type' => 'order_accepted_by_restaurant',
            'action_url' => '/orders/' . $this->deliveryLedger->meal_order_id,
            'notification_time' => now()->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        // Get meal_time from the first meal order item
        $mealTime = $this->mealOrderItems->first()->meal_time ?? null;
        
        return [
            'delivery_charge_ledger_id' => $this->deliveryLedger->id,
            'order_tracking' => $this->deliveryLedger->order_tracking,
            'client_name' => $this->client->firstName . ' ' . $this->client->lastName,
            'meal_time' => $mealTime, // Add meal_time here
            'notification_type' => 'order_accepted_by_restaurant',
        ];
    }
}