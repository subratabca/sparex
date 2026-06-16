<?php

namespace App\Notifications\Client;

use Illuminate\Notifications\Notification;

/**
 * Sent to the client (restaurant/seller) when a delivery rider accepts one of
 * their meal-order deliveries. Stored synchronously (no queue) so the client's
 * dashboard can pop it in near real-time via polling.
 */
class DeliveryAcceptedNotification extends Notification
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // database only — surfaced through the client popup poll
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            // 'data' is the message text the client notification dropdown renders
            'data'                      => 'A rider has accepted your delivery',
            'type'                      => 'delivery_accepted',
            'meal_order_id'             => $this->data['meal_order_id'] ?? null,
            'delivery_charge_ledger_id' => $this->data['delivery_charge_ledger_id'] ?? null,
            'order_tracking'            => $this->data['order_tracking'] ?? null,
            'order_number'              => $this->data['order_number'] ?? null,
            'rider'                     => $this->data['rider'] ?? null,
            'notification_time'         => now()->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
