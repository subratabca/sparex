<?php

namespace App\Notifications\Delivery;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderReadyForPickupNotification extends Notification 
{
    use Queueable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $pickupTime = isset($this->data['delivery_details']['scheduled_pickup_time']) 
            ? date('h:i A', strtotime($this->data['delivery_details']['scheduled_pickup_time']))
            : 'ASAP';

        return [
            'title' => 'Order Ready for Pickup',
            'message' => "Order #{$this->data['order_tracking']} from {$this->data['client_name']} is ready for pickup at {$pickupTime}",
            'data' => $this->data,
            'type' => 'order_ready_for_pickup',
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
            'pickup_time' => $this->data['delivery_details']['scheduled_pickup_time'] ?? null,
            'meal_time' => $this->data['delivery_details']['meal_time'] ?? null,
            'notification_type' => 'order_ready_for_pickup',
        ];
    }
}