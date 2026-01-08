<?php

namespace App\Notifications\Delivery;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DeliveryChargeLedger;
use App\Models\MealOrder;
use App\Models\User;

class DeliveryAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;
    private $deliveryLedger;
    private $mealOrder;
    private $client;

    public function __construct($data)
    {
        $this->data = $data;
        
        // Load related models if IDs are provided
        if (isset($data['delivery_ledger_id'])) {
            $this->deliveryLedger = DeliveryChargeLedger::with([
                'mealOrder',
                'client',
                'customer',
                'mealType'
            ])->find($data['delivery_ledger_id']);
        }
        
        if (isset($data['order_id'])) {
            $this->mealOrder = MealOrder::with([
                'customer',
                'mealShippingAddress',
                'items' => function($query) use ($data) {
                    $query->where('client_id', $data['client_id'] ?? null)
                        ->where('meal_date', $data['meal_date'] ?? null)
                        ->with(['product', 'mealType']);
                }
            ])->find($data['order_id']);
        }
        
        if (isset($data['client_id'])) {
            $this->client = User::find($data['client_id']);
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getSubject();
        
        return (new MailMessage)
            ->from('support@sparex.com')
            ->view('email.notification.delivery.delivery_assignment', [
                'data' => $this->data,
                'deliveryLedger' => $this->deliveryLedger,
                'mealOrder' => $this->mealOrder,
                'client' => $this->client,
                'deliveryPerson' => $notifiable
            ])
            ->subject($subject);
    }

    public function toArray(object $notifiable): array
    {
        
        return [
            'data' => 'New Delivery Assignment',
            'delivery_ledger_id' => $this->data['delivery_ledger_id'] ?? null,
        ];
    }

    private function getSubject(): string
    {
        $orderNumber = $this->mealOrder->order_number ?? $this->data['order_number'] ?? 'N/A';
        return "New Delivery Assignment - Order #{$orderNumber}";
    }
}