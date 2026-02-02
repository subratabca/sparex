<?php

namespace App\Notifications\Delivery;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewDeliveryAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Only database, no email
    }

    public function toDatabase(object $notifiable): array
    {
        // Get meal_time from the data array
        $mealTime = $this->data['delivery_details']['meal_time'] ?? null;
        $formattedMealTime = $mealTime ? date('h:i A', strtotime($mealTime)) : 'N/A';

        return [
            'title' => 'New Delivery Assignment Available',
            'message' => "New delivery order #{$this->data['order_tracking']} from {$this->data['client_name']}",
            'data' => $this->data,
            'type' => 'new_delivery_available',
            'action_url' => $this->data['action_url'] ?? null,
            'notification_time' => now()->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_charge_ledger_id' => $this->data['delivery_charge_ledger_id'] ?? null,
            'order_tracking' => $this->data['order_tracking'] ?? null,
            'client_name' => $this->data['client_name'] ?? null,
            'meal_time' => $this->data['delivery_details']['meal_time'] ?? null,
            'notification_type' => 'new_delivery_available',
        ];
    }
}