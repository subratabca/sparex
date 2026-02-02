<?php

namespace App\Notifications\MealOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MealOrder;
use App\Models\ClientMealOrder;
use App\Models\DeliveryChargeLedger;

class NewMealOrderNotification extends Notification
{
    use Queueable;

    private $notificationData;
    private $type; // 'meal_order', 'client_meal_order', or 'array'
    private $deliveryLedgerLookup = [];

    public function __construct($data)
    {
        if ($data instanceof MealOrder) {
            $this->type = 'meal_order';
            $this->notificationData = [
                'meal_order' => $data->load([
                    'customer',
                    'items.client',
                    'items.product', 
                    'items.mealType',
                    'clientMealOrders.client',
                    'mealShippingAddress',
                    'deliveryChargeLedgers'
                ])
            ];
            
            // Create lookup for delivery charge ledgers
            $this->prepareDeliveryLedgerLookup($data->deliveryChargeLedgers ?? collect());
        } elseif ($data instanceof ClientMealOrder) {
            $this->type = 'client_meal_order';
            $this->notificationData = [
                'client_meal_order' => $data->load([
                    'client',
                    'mealOrder.customer',
                    'mealOrder.mealShippingAddress',
                    'mealOrder.deliveryChargeLedgers' => function($query) use ($data) {
                        $query->where('client_id', $data->client_id);
                    }
                ])
            ];
            
            // Load meal order items separately without the non-existent relationship
            $mealOrderItems = $data->mealOrder->items()
                ->where('client_id', $data->client_id)
                ->with(['product', 'mealType', 'client'])
                ->get();
                
            $this->notificationData['client_meal_order']->mealOrder->setRelation('items', $mealOrderItems);
            
            // Create lookup for delivery charge ledgers
            $this->prepareDeliveryLedgerLookup($data->mealOrder->deliveryChargeLedgers ?? collect());
        } elseif (is_array($data)) {
            $this->type = 'array';
            $this->notificationData = $data;
            
            // Load relationships if objects are provided
            if (isset($this->notificationData['meal_order']) && $this->notificationData['meal_order'] instanceof MealOrder) {
                if (!$this->notificationData['meal_order']->relationLoaded('customer')) {
                    $this->notificationData['meal_order']->load([
                        'customer',
                        'mealShippingAddress',
                        'deliveryChargeLedgers'
                    ]);
                }
                $this->prepareDeliveryLedgerLookup($this->notificationData['meal_order']->deliveryChargeLedgers ?? collect());
            }
            
            if (isset($this->notificationData['client_meal_order']) && $this->notificationData['client_meal_order'] instanceof ClientMealOrder) {
                if (!$this->notificationData['client_meal_order']->relationLoaded('client')) {
                    $this->notificationData['client_meal_order']->load(['client', 'mealOrder']);
                }
                if ($this->notificationData['client_meal_order']->mealOrder && 
                    !$this->notificationData['client_meal_order']->mealOrder->relationLoaded('deliveryChargeLedgers')) {
                    $this->notificationData['client_meal_order']->mealOrder->load(['deliveryChargeLedgers' => function($query) {
                        $query->where('client_id', $this->notificationData['client_meal_order']->client_id);
                    }]);
                }
                $this->prepareDeliveryLedgerLookup($this->notificationData['client_meal_order']->mealOrder->deliveryChargeLedgers ?? collect());
            }
        }
    }

    /**
     * Create a lookup array for delivery charge ledgers by meal_type_id and delivery_date
     */
    private function prepareDeliveryLedgerLookup($deliveryChargeLedgers)
    {
        $this->deliveryLedgerLookup = [];
        foreach ($deliveryChargeLedgers as $ledger) {
            $key = $ledger->meal_type_id . '_' . $ledger->delivery_date;
            $this->deliveryLedgerLookup[$key] = [
                'id' => $ledger->id,
                'delivery_status' => $ledger->delivery_status,
                'delivery_charge' => $ledger->delivery_charge,
            ];
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getSubject($notifiable);
        
        // Prepare data for email template
        $viewData = $this->prepareViewData($notifiable);
        
        return (new MailMessage)
            ->from('support@sparex.com')
            ->view('email.notification.meal-order.new_meal_order', $viewData)
            ->subject($subject);
    }

    public function toArray(object $notifiable): array
    {
        if ($this->type === 'meal_order') {
            $mealOrder = $this->notificationData['meal_order'];
            return [
                'data' => 'New Meal Order Received',
                'meal_order_id' => $mealOrder->id,
                'order_number' => $mealOrder->order_number,
                'type' => 'meal_order',
                'user_role' => $notifiable->role
            ];
        } elseif ($this->type === 'client_meal_order') {
            $clientMealOrder = $this->notificationData['client_meal_order'];
            return [
                'data' => 'New Meal Order',
                'meal_order_id' => $clientMealOrder->meal_order_id,
                'client_order_id' => $clientMealOrder->id,
                'client_id' => $clientMealOrder->client_id,
                'type' => 'client_meal_order',
                'user_role' => 'client'
            ];
        } elseif ($this->type === 'array') {
            $mealOrder = $this->notificationData['meal_order'] ?? null;
            $clientMealOrder = $this->notificationData['client_meal_order'] ?? null;
            
            return [
                'data' => 'New Meal Order',
                'meal_order_id' => $mealOrder ? $mealOrder->id : ($clientMealOrder ? $clientMealOrder->meal_order_id : null),
                'client_order_id' => $clientMealOrder ? $clientMealOrder->id : null,
                'client_id' => $clientMealOrder ? $clientMealOrder->client_id : $notifiable->id,
                'type' => 'client_array',
                'user_role' => 'client'
            ];
        }
        
        return [
            'data' => 'New Meal Order Notification',
            'type' => 'unknown'
        ];
    }

    private function getSubject($notifiable): string
    {
        if ($this->type === 'meal_order') {
            $mealOrder = $this->notificationData['meal_order'];
            if ($notifiable->role === 'admin') {
                return 'New Meal Order #' . $mealOrder->order_number . ' - Requires Attention';
            } else {
                return 'Your Meal Order #' . $mealOrder->order_number . ' Has Been Placed';
            }
        } elseif ($this->type === 'client_meal_order') {
            $clientMealOrder = $this->notificationData['client_meal_order'];
            return 'New Meal Order #' . $clientMealOrder->mealOrder->order_number . ' - Your Products';
        } elseif ($this->type === 'array') {
            $mealOrder = $this->notificationData['meal_order'] ?? null;
            $clientMealOrder = $this->notificationData['client_meal_order'] ?? null;
            
            if ($clientMealOrder && $clientMealOrder->mealOrder) {
                return 'New Meal Order #' . $clientMealOrder->mealOrder->order_number . ' - Your Products';
            } elseif ($mealOrder) {
                return 'New Meal Order #' . $mealOrder->order_number . ' - Your Products';
            }
        }
        
        return 'New Meal Order Notification';
    }

    private function prepareViewData($notifiable): array
    {
        if ($this->type === 'meal_order') {
            $mealOrder = $this->notificationData['meal_order'];
            
            // For admin: show all items
            // For customer: show all their items
            if ($notifiable->role === 'admin') {
                $items = $mealOrder->items;
                $clientMealOrder = null;
            } else {
                // For customer, show all items
                $items = $mealOrder->items;
                $clientMealOrder = null;
            }
            
            // Attach delivery ledger info to each item using the lookup
            $items->each(function($item) {
                $lookupKey = $item->meal_type_id . '_' . $item->meal_date;
                $ledgerInfo = $this->deliveryLedgerLookup[$lookupKey] ?? null;
                $item->delivery_status = $ledgerInfo['delivery_status'] ?? 'pending';
            });
            
            return [
                'mealOrder' => $mealOrder,
                'clientMealOrder' => $clientMealOrder,
                'items' => $items,
                'deliveryLedgerLookup' => $this->deliveryLedgerLookup,
                'notifiable' => $notifiable
            ];
            
        } elseif ($this->type === 'client_meal_order') {
            $clientMealOrder = $this->notificationData['client_meal_order'];
            $mealOrder = $clientMealOrder->mealOrder;
            
            // For client: show only their items
            $items = $mealOrder->items->where('client_id', $notifiable->id);
            
            // Attach delivery ledger info to each item using the lookup
            $items->each(function($item) {
                $lookupKey = $item->meal_type_id . '_' . $item->meal_date;
                $ledgerInfo = $this->deliveryLedgerLookup[$lookupKey] ?? null;
                $item->delivery_status = $ledgerInfo['delivery_status'] ?? 'pending';
            });
            
            return [
                'mealOrder' => $mealOrder,
                'clientMealOrder' => $clientMealOrder,
                'items' => $items,
                'deliveryLedgerLookup' => $this->deliveryLedgerLookup,
                'notifiable' => $notifiable
            ];
            
        } elseif ($this->type === 'array') {
            $mealOrder = $this->notificationData['meal_order'] ?? null;
            $clientMealOrder = $this->notificationData['client_meal_order'] ?? null;
            
            // Prepare items based on available data
            if ($clientMealOrder && $clientMealOrder->mealOrder) {
                $mealOrder = $clientMealOrder->mealOrder;
                $items = $mealOrder->items->where('client_id', $notifiable->id);
            } elseif ($mealOrder) {
                // If only mealOrder is provided, get items for this client
                $items = $mealOrder->items->where('client_id', $notifiable->id);
            } else {
                $items = collect([]);
            }
            
            // Attach delivery ledger info to each item using the lookup
            $items->each(function($item) {
                $lookupKey = $item->meal_type_id . '_' . $item->meal_date;
                $ledgerInfo = $this->deliveryLedgerLookup[$lookupKey] ?? null;
                $item->delivery_status = $ledgerInfo['delivery_status'] ?? 'pending';
            });
            
            return [
                'mealOrder' => $mealOrder,
                'clientMealOrder' => $clientMealOrder,
                'items' => $items,
                'deliveryLedgerLookup' => $this->deliveryLedgerLookup,
                'notifiable' => $notifiable
            ];
        }
        
        return [
            'notifiable' => $notifiable,
            'items' => collect([])
        ];
    }
}