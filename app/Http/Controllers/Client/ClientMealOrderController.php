<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\Delivery\DeliveryAssignmentNotification;
use App\Notifications\Delivery\PickupNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Product;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Models\MealDelivery;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use Exception;


class ClientMealOrderController extends Controller
{
    public function index()
    {
        return view('client.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $client_id = $request->header('id');

            // Get meal orders where the client has order items
            $mealOrders = MealOrder::whereHas('items', function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->with([
                    'customer',
                    'items' => function($query) use ($client_id) {
                        $query->where('client_id', $client_id)
                              ->with(['client', 'mealType', 'product']);
                    }
                ])
                ->latest()
                ->get();

            $data = [];
            $orderGroups = [];
            $groupIndex = 0;
            
            foreach ($mealOrders as $order) {
                // Filter items to only include this client's items
                $clientItems = $order->items->filter(function($item) use ($client_id) {
                    return $item->client_id == $client_id;
                });
                
                if ($clientItems->isEmpty()) {
                    continue; // Skip if no items for this client
                }
                
                // Group items by meal_date
                $groupedItems = $clientItems->groupBy('meal_date');
                
                $rowspan = count($groupedItems);
                $firstRow = true;
                
                foreach ($groupedItems as $mealDate => $items) {
                    // Calculate billed amount for this specific date
                    $billedAmountForDate = $items->sum('total_price');
                    
                    // Get meal types for this specific date
                    $mealTypesForDate = $items->map(function ($item) {
                        return $item->mealType->name ?? null;
                    })->filter()->unique()->values()->toArray();
                    
                    $data[] = [
                        'group_index' => $groupIndex,
                        'rowspan' => $rowspan,
                        'is_first_row' => $firstRow,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                        'meal_date' => $mealDate,
                        'meal_types' => $mealTypesForDate,
                        'formatted_meal_types' => implode(', ', $mealTypesForDate),
                        'billed_amount' => number_format($billedAmountForDate, 2),
                        'billed_amount_raw' => $billedAmountForDate,
                        'payment_status' => $order->payment_status ?? 'pending',
                        'delivery_status' => $order->delivery_status ?? 'pending',
                        'payment_status_badge' => $this->getPaymentStatusBadge($order->payment_status),
                        'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')),
                        'customer_email' => $order->customer->email ?? '',
                        'total_items' => $items->count(),
                        'order_created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'client_id' => $client_id
                    ];
                    
                    $firstRow = false;
                }
                
                $groupIndex++;
            }

            // Sort by meal date descending within each order group
            $sortedData = [];
            $currentGroup = -1;
            $groupItems = [];
            
            foreach ($data as $item) {
                if ($item['group_index'] != $currentGroup) {
                    if (!empty($groupItems)) {
                        // Sort current group by date descending
                        usort($groupItems, function ($a, $b) {
                            return strcmp($b['meal_date'], $a['meal_date']);
                        });
                        $sortedData = array_merge($sortedData, $groupItems);
                    }
                    $currentGroup = $item['group_index'];
                    $groupItems = [$item];
                } else {
                    $groupItems[] = $item;
                }
            }
            
            // Sort last group
            if (!empty($groupItems)) {
                usort($groupItems, function ($a, $b) {
                    return strcmp($b['meal_date'], $a['meal_date']);
                });
                $sortedData = array_merge($sortedData, $groupItems);
            }

            // Add sequential SL numbers
            foreach ($sortedData as $index => &$item) {
                $item['sl'] = $index + 1;
            }

            return response()->json([
                'status' => 'success',
                'data' => $sortedData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getPaymentStatusBadge($status)
    {
        $badges = [
            'paid' => '<span class="badge bg-success">Paid</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'refunded' => '<span class="badge bg-info">Refunded</span>',
            'partially_paid' => '<span class="badge bg-primary">Partially Paid</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    public function view(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', $email)->first();

        $notification_id = $request->query('notification_id');
        if ($notification_id) {
            $notification = $user->notifications()->where('id', $notification_id)->first();

            if ($notification && is_null($notification->read_at)) {
                $notification->markAsRead();
            }
        }

        return view('client.pages.meal-order.view');
    }

    public function getMealOrderDetails(Request $request, $order_id)
    {
        try {
            $client_id = $request->header('id');
            $client = User::where('id', $client_id)->first();
            
            if (!$client) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client not found.'
                ], 404);
            }

            // Verify the order exists
            $order = MealOrder::with(['customer:id,firstName,lastName,email,mobile,image'])
                ->find($order_id);
                
            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found.'
                ], 404);
            }

            // Get the specific client meal order for this client and order
            $clientMealOrder = ClientMealOrder::with([
                'mealOrder:id,order_number,invoice_no,status,created_at,delivery_fee,subtotal,tax,payable_amount,payment_status',
                'client:id,firstName,lastName,email,mobile,image',
            ])
            ->where('client_id', $client_id)
            ->where('meal_order_id', $order_id)
            ->first();

            // Get meal order items for this client and order
            $mealOrderItems = MealOrderItem::with([
                'mealType:id,name',
                'product:id,name,image,price',
                'client:id,firstName,lastName',
                'mealDelivery' => function($query) {
                    $query->select('id', 'meal_order_item_id', 'delivery_person_id', 
                                  'estimated_delivery_time', 'actual_delivery_time', 'tracking_code',
                                  'pickup_time', 'handover_time');
                },
                'mealDelivery.deliveryPerson:id,firstName,lastName,mobile',
                'deliveryPerson:id,firstName,lastName,mobile'
            ])
            ->where('client_id', $client_id)
            ->where('meal_order_id', $order_id)
            ->get();

            if ($mealOrderItems->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No items found for this client in the specified order.'
                ], 404);
            }

            // Get DeliveryChargeLedger for this order and client
            $deliveryChargeLedgers = DeliveryChargeLedger::with([
                'mealType:id,name',
                'deliveryPerson:id,firstName,lastName',
            ])
            ->where('meal_order_id', $order_id)
            ->where('client_id', $client_id)
            ->get();

            // Get all delivery statuses from MealOrderItem model
            $deliveryStatuses = [
                MealOrderItem::STATUS_PENDING => 'Pending',
                MealOrderItem::STATUS_ACCEPT_ORDER => 'Accept Order',
                MealOrderItem::STATUS_PREPARING => 'Preparing',
                MealOrderItem::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
                MealOrderItem::STATUS_PICKED_UP => 'Picked Up',
                MealOrderItem::STATUS_ON_THE_WAY => 'On the Way',
                MealOrderItem::STATUS_ARRIVED => 'Arrived',
                MealOrderItem::STATUS_DELIVERED => 'Delivered',
                MealOrderItem::STATUS_CANCELLED => 'Cancelled',
            ];

            // If no ClientMealOrder exists, create a virtual one from the items
            if (!$clientMealOrder) {
                // Calculate totals from items
                $subtotal = $mealOrderItems->sum('total_price');
                $deliveryFee = 0;
                $tax = 0;
                $payableAmount = $subtotal + $deliveryFee + $tax;

                $clientMealOrder = (object) [
                    'id' => null,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $payableAmount,
                    'paid_amount' => 0,
                    'payment_status' => 'pending',
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'mealOrder' => $order,
                    'client' => $client,
                    'mealOrderItems' => $mealOrderItems
                ];
            } else {
                // Load items for existing ClientMealOrder
                $clientMealOrder->mealOrderItems = $mealOrderItems;
            }

            // Group items by meal_date and meal_type
            $groupedItems = [];
            $totalAmount = 0;

            foreach ($mealOrderItems as $item) {
                $mealDate = $item->meal_date;
                $mealTypeName = $item->mealType ? $item->mealType->name : 'Other';
                $mealTypeId = $item->meal_type_id;
                
                // Create a unique key for grouping: date + meal_type + client
                $groupKey = $mealDate . '_' . $mealTypeId . '_' . $item->client_id;
                
                if (!isset($groupedItems[$mealDate])) {
                    $groupedItems[$mealDate] = [];
                }
                
                if (!isset($groupedItems[$mealDate][$mealTypeName])) {
                    $groupedItems[$mealDate][$mealTypeName] = [
                        'items' => [],
                        'meal_type_id' => $mealTypeId,
                        'client_id' => $item->client_id,
                        'meal_date' => $mealDate,
                        'delivery_status' => $item->delivery_status,
                        'delivery_status_label' => $deliveryStatuses[$item->delivery_status] ?? ucfirst($item->delivery_status),
                        'delivery_person_name' => null,
                        'meal_delivery_ids' => [],
                        'total_price' => 0
                    ];
                }
                
                // Get delivery status for this individual item
                $deliveryStatus = $item->delivery_status ?? null;
                $deliveryStatusLabel = $deliveryStatuses[$deliveryStatus] ?? ucfirst($deliveryStatus);
                $deliveryPersonName = null;
                $mealDeliveryId = null;
                
                if ($item->mealDelivery) {
                    if ($item->mealDelivery->deliveryPerson) {
                        $deliveryPersonName = $item->mealDelivery->deliveryPerson->firstName . ' ' . $item->mealDelivery->deliveryPerson->lastName;
                    }
                    $mealDeliveryId = $item->mealDelivery->id;
                } elseif ($item->deliveryPerson) {
                    $deliveryPersonName = $item->deliveryPerson->firstName . ' ' . $item->deliveryPerson->lastName;
                }
                
                // Add item to the group
                $groupedItems[$mealDate][$mealTypeName]['items'][] = [
                    'id' => $item->id,
                    'product_name' => $item->product ? $item->product->name : 'N/A',
                    'product_image' => $item->product ? $item->product->image : null,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'meal_type_id' => $item->meal_type_id,
                    'meal_type_name' => $mealTypeName,
                    'client_name' => $item->client ? $item->client->firstName . ' ' . $item->client->lastName : 'N/A',
                    'meal_time' => $item->meal_time,
                    'delivery_status' => $deliveryStatus,
                    'delivery_status_label' => $deliveryStatusLabel,
                    'delivery_person_name' => $deliveryPersonName,
                    'meal_delivery_id' => $mealDeliveryId,
                    'has_delivery_record' => !is_null($mealDeliveryId)
                ];
                
                // Add to group total price
                $groupedItems[$mealDate][$mealTypeName]['total_price'] += $item->total_price;
                
                // Store meal delivery IDs for this group
                if ($mealDeliveryId) {
                    $groupedItems[$mealDate][$mealTypeName]['meal_delivery_ids'][] = $mealDeliveryId;
                }
                
                // Track delivery person for the group
                if ($deliveryPersonName && !$groupedItems[$mealDate][$mealTypeName]['delivery_person_name']) {
                    $groupedItems[$mealDate][$mealTypeName]['delivery_person_name'] = $deliveryPersonName;
                }
                
                $totalAmount += $item->total_price;
            }

            // Get all meal types for this client's items
            $mealTypeIds = $mealOrderItems->pluck('meal_type_id')->filter()->unique()->values();
            $mealTypes = MealType::whereIn('id', $mealTypeIds)->get(['id', 'name']);

            // Prepare the response data
            $data = [
                'client_meal_order' => [
                    'id' => $clientMealOrder->id,
                    'subtotal' => $clientMealOrder->subtotal,
                    'tax' => $clientMealOrder->tax,
                    'delivery_fee' => $clientMealOrder->delivery_fee,
                    'payable_amount' => $clientMealOrder->payable_amount,
                    'paid_amount' => $clientMealOrder->paid_amount,
                    'payment_status' => $clientMealOrder->payment_status,
                    'created_at' => $clientMealOrder->created_at ? $clientMealOrder->created_at->format('d M Y H:i:s') : null,
                    'updated_at' => $clientMealOrder->updated_at ? $clientMealOrder->updated_at->format('d M Y H:i:s') : null,
                ],
                'order' => [
                    'order_number' => $order->order_number,
                    'invoice_no' => $order->invoice_no,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d M Y H:i:s'),
                    'payment_status' => $order->payment_status,
                ],
                'client' => [
                    'id' => $client->id,
                    'name' => $client->firstName . ' ' . $client->lastName,
                    'email' => $client->email,
                    'mobile' => $client->mobile,
                    'image' => $client->image,
                ],
                'customer' => [
                    'id' => $order->customer->id ?? null,
                    'name' => $order->customer ? ucwords(strtolower(trim($order->customer->firstName . ' ' . $order->customer->lastName))) : null,
                    'email' => $order->customer->email ?? null,
                    'mobile' => $order->customer->mobile ?? null,
                    'image' => $order->customer->image ?? null,
                ],
                'items' => $groupedItems,
                'delivery_charge_ledgers' => $deliveryChargeLedgers->map(function($ledger) {
                    return [
                        'id' => $ledger->id,
                        'meal_type_id' => $ledger->meal_type_id,
                        'meal_type_name' => $ledger->mealType->name ?? null,
                        'delivery_charge' => $ledger->delivery_charge,
                        'delivery_person_id' => $ledger->delivery_person_id,
                        'delivery_person_name' => $ledger->deliveryPerson ? $ledger->deliveryPerson->firstName . ' ' . $ledger->deliveryPerson->lastName : null,
                        'payment_status' => $ledger->payment_status,
                        'delivery_date' => $ledger->delivery_date,
                        'is_charge_counted' => $ledger->is_charge_counted,
                    ];
                }),
                'delivery_statuses' => $deliveryStatuses,
                'summary' => [
                    'total_items' => $mealOrderItems->count(),
                    'total_dates' => count($groupedItems),
                    'total_meal_types' => $mealTypes->count(),
                    'subtotal' => $clientMealOrder->subtotal,
                    'tax' => $clientMealOrder->tax,
                    'delivery_fee' => $clientMealOrder->delivery_fee,
                    'total_amount' => $clientMealOrder->payable_amount,
                    'paid_amount' => $clientMealOrder->paid_amount,
                    'balance_amount' => $clientMealOrder->payable_amount - $clientMealOrder->paid_amount,
                    'total_groups' => array_reduce($groupedItems, function($carry, $dateItems) {
                        return $carry + count($dateItems);
                    }, 0),
                ],
                'meal_types' => $mealTypes,
                'dates' => array_keys($groupedItems),
                'items_with_time' => $mealOrderItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'meal_date' => $item->meal_date,
                        'meal_time' => $item->meal_time,
                        'meal_type_id' => $item->meal_type_id,
                        'meal_type_name' => $item->mealType ? $item->mealType->name : 'Other',
                        'delivery_status' => $item->delivery_status,
                    ];
                })->toArray()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving payment details.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateDeliveryStatus(Request $request, $orderId)
    {
        try {
            $client_id = $request->header('id');
            
            // Validate client exists
            $client = User::where('id', $client_id)->first();
            if (!$client || !$client->isClient()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized access. Client not found.'
                ], 403);
            }

            // Validate request
            $request->validate([
                'meal_order_item_id' => 'required|string',
                'delivery_status' => 'required|in:pending,accept_order,preparing,ready_for_pickup,cancelled',
                'notes' => 'nullable|string|max:500',
                'pickup_time' => 'nullable|date_format:Y-m-d\TH:i',
                'meal_date' => 'nullable|date',
                'meal_type_id' => 'nullable|exists:meal_types,id'
            ]);

            // Parse comma-separated item IDs
            $itemIds = explode(',', $request->meal_order_item_id);
            
            // Check if all items belong to this client and order
            $mealOrderItems = MealOrderItem::whereIn('id', $itemIds)
                ->where('meal_order_id', $orderId)
                ->where('client_id', $client_id)
                ->get();

            if ($mealOrderItems->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No items found or unauthorized access.'
                ], 404);
            }

            $newStatus = $request->delivery_status;
            $notes = $request->notes;
            $pickupTime = $request->pickup_time;

            // Check if status transition is valid for client
            $invalidTransitions = [];
            $validTransitions = [
                'pending' => ['accept_order', 'cancelled'],
                'accept_order' => ['preparing'],
                'preparing' => ['ready_for_pickup'],
            ];

            DB::beginTransaction();

            foreach ($mealOrderItems as $item) {
                $currentStatus = $item->delivery_status;
                
                // Check if this is a valid transition for client
                if (!isset($validTransitions[$currentStatus]) || 
                    !in_array($newStatus, $validTransitions[$currentStatus])) {
                    $invalidTransitions[] = "Item #{$item->id}: Cannot change from {$currentStatus} to {$newStatus}";
                    continue;
                }

                // Update the meal order item status
                $item->delivery_status = $newStatus;
                
                // If changing to ready_for_pickup, update handover_time
                if ($newStatus === 'ready_for_pickup') {
                    $item->handover_time = now();
                }
                
                $item->save();

                // Prepare meal delivery data
                $mealDeliveryData = [
                    'delivery_person_id' => $item->delivery_person_id,
                ];

                // Only add pickup_time when status is preparing or ready_for_pickup
                if ($newStatus === 'preparing' || $newStatus === 'ready_for_pickup') {
                    if ($pickupTime) {
                        $mealDeliveryData['pickup_time'] = $pickupTime;
                    }
                }

                // Only add handover_time when status is ready_for_pickup
                if ($newStatus === 'ready_for_pickup') {
                    $mealDeliveryData['handover_time'] = now();
                }

                // Create or update meal delivery record
                $mealDelivery = MealDelivery::updateOrCreate(
                    ['meal_order_item_id' => $item->id],
                    $mealDeliveryData
                );

                // Create status history
                MealDeliveryStatusHistory::create([
                    'meal_delivery_id' => $mealDelivery->id,
                    'delivery_status' => $newStatus,
                    'notes' => $notes,
                    'updated_by_id' => $client_id,
                    'updated_by_type' => 'client'
                ]);
            }

            DB::commit();

            if (!empty($invalidTransitions)) {
                return response()->json([
                    'status' => 'partial',
                    'message' => 'Some items could not be updated.',
                    'invalid_transitions' => $invalidTransitions,
                    'updated_items' => count($mealOrderItems) - count($invalidTransitions)
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery status updated successfully for ' . count($mealOrderItems) . ' item(s).'
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateDeliveryStatus111(Request $request, $orderId)
    {
        try {
            $client_id = $request->header('id');
            
            // Validate request
            $request->validate([
                'meal_order_item_id' => 'required|string',
                'delivery_status' => 'required|string|in:pending,preparing,ready_for_pickup,cancelled',
                'notes' => 'nullable|string|max:500',
                'meal_date' => 'required|date',
                'meal_type_id' => 'nullable|integer|exists:meal_types,id'
            ]);

            // Client can only set these statuses
            if (!in_array($request->delivery_status, ['preparing', 'ready_for_pickup', 'cancelled'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client can only set status to: preparing, ready_for_pickup, or cancelled.'
                ], 400);
            }

            // Parse comma-separated item IDs if multiple
            $itemIds = explode(',', $request->meal_order_item_id);
            $firstItemId = trim($itemIds[0]);
            
            // Start database transaction
            DB::beginTransaction();

            // Get the meal order and first item for validation
            $mealOrder = MealOrder::with(['customer', 'mealShippingAddress'])->find($orderId);
            
            if (!$mealOrder) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.'
                ], 404);
            }

            // Check first item for permission and current status
            $firstItem = MealOrderItem::where('id', $firstItemId)
                ->where('client_id', $client_id)
                ->where('meal_order_id', $orderId)
                ->where('meal_date', $request->meal_date)
                ->first();

            if (!$firstItem) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order item not found or you do not have permission to update it.'
                ], 404);
            }

            // Validate status transitions for client
            $currentStatus = $firstItem->delivery_status;
            $newStatus = $request->delivery_status;
            
            if (!$this->isValidClientStatusTransition($currentStatus, $newStatus)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => "Cannot change status from {$this->getDeliveryStatusLabel($currentStatus)} to {$this->getDeliveryStatusLabel($newStatus)}."
                ], 400);
            }

            // Store whether we need to send delivery notifications
            $sendDeliveryNotification = false;
            $notificationData = null;
            
            // Check if we need to send delivery notifications
            if (($currentStatus === 'pending' && $newStatus === 'preparing') || 
                ($currentStatus === 'preparing' && $newStatus === 'ready_for_pickup')) {
                $sendDeliveryNotification = true;
                
                // Get delivery charge info
                $deliveryLedger = DeliveryChargeLedger::where('meal_order_id', $orderId)
                    ->where('client_id', $client_id)
                    ->where('delivery_date', $request->meal_date)
                    ->when($request->meal_type_id, function($query, $mealTypeId) {
                        return $query->where('meal_type_id', $mealTypeId);
                    })
                    ->first();

                // Get client information
                $client = User::with(['country', 'county', 'city'])->find($client_id);
                
                // Build client address from address1, address2, city, county, country
                $clientAddressParts = [];
                if (!empty($client->address1)) {
                    $clientAddressParts[] = $client->address1;
                }
                if (!empty($client->address2)) {
                    $clientAddressParts[] = $client->address2;
                }
                if ($client->city && !empty($client->city->name)) {
                    $clientAddressParts[] = $client->city->name;
                }
                if ($client->county && !empty($client->county->name)) {
                    $clientAddressParts[] = $client->county->name;
                }
                if ($client->country && !empty($client->country->name)) {
                    $clientAddressParts[] = $client->country->name;
                }
                if (!empty($client->zip_code)) {
                    $clientAddressParts[] = $client->zip_code;
                }
                
                $clientAddress = !empty($clientAddressParts) ? implode(', ', $clientAddressParts) : 'Client address not available';
                
                // Get shipping address
                $shippingAddress = 'Shipping address not available';
                if ($mealOrder->mealShippingAddress) {
                    $shipping = $mealOrder->mealShippingAddress;
                    $shippingAddressParts = [];
                    
                    if (!empty($shipping->address1)) {
                        $shippingAddressParts[] = $shipping->address1;
                    }
                    if (!empty($shipping->address2)) {
                        $shippingAddressParts[] = $shipping->address2;
                    }
                    if ($shipping->city && !empty($shipping->city->name)) {
                        $shippingAddressParts[] = $shipping->city->name;
                    }
                    if ($shipping->county && !empty($shipping->county->name)) {
                        $shippingAddressParts[] = $shipping->county->name;
                    }
                    if ($shipping->country && !empty($shipping->country->name)) {
                        $shippingAddressParts[] = $shipping->country->name;
                    }
                    if (!empty($shipping->zip_code)) {
                        $shippingAddressParts[] = $shipping->zip_code;
                    }
                    
                    $shippingAddress = !empty($shippingAddressParts) ? implode(', ', $shippingAddressParts) : 'Shipping address not available';
                }

                $notificationData = [
                    'order_id' => $orderId,
                    'order_number' => $mealOrder->order_number,
                    'client_id' => $client_id,
                    'client_name' => $client ? ($client->firstName . ' ' . $client->lastName) : 'Unknown Client',
                    'client_email' => $client ? $client->email : '',
                    'client_phone' => $client ? $client->mobile : '',
                    'client_address' => $clientAddress,
                    'shipping_address' => $shippingAddress,
                    'delivery_charge' => $deliveryLedger ? $deliveryLedger->delivery_charge : 0,
                    'meal_date' => $request->meal_date,
                    'meal_type' => $firstItem->mealType ? $firstItem->mealType->name : 'Unknown',
                    'item_count' => count($itemIds),
                    'notes' => $request->notes,
                    'current_status' => $this->getDeliveryStatusLabel($currentStatus),
                    'new_status' => $this->getDeliveryStatusLabel($newStatus),
                    'updated_by' => 'client',
                    'delivery_ledger_id' => $deliveryLedger ? $deliveryLedger->id : null
                ];

                // Debug log
                \Log::debug('Preparing to send delivery notification:', [
                    'notificationData' => $notificationData,
                    'sendDeliveryNotification' => $sendDeliveryNotification,
                    'client_found' => $client ? 'Yes' : 'No',
                    'shipping_address_found' => $mealOrder->mealShippingAddress ? 'Yes' : 'No',
                    'delivery_ledger_found' => $deliveryLedger ? 'Yes' : 'No'
                ]);
            }

            // Update all items
            foreach ($itemIds as $itemId) {
                $itemId = trim($itemId);
                
                // Check if client owns this meal order item
                $mealOrderItem = MealOrderItem::where('id', $itemId)
                    ->where('client_id', $client_id)
                    ->where('meal_order_id', $orderId)
                    ->where('meal_date', $request->meal_date)
                    ->first();

                if (!$mealOrderItem) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Meal order item not found or you do not have permission to update it.'
                    ], 404);
                }

                // Update or create meal delivery record
                $mealDelivery = MealDelivery::updateOrCreate(
                    ['meal_order_item_id' => $itemId],
                    [
                        'delivery_status' => $newStatus,
                        'delivery_notes' => $request->notes,
                        'updated_at' => now()
                    ]
                );

                // Create status history record
                if ($mealDelivery) {
                    // Update the meal order item's delivery_status field
                    $mealOrderItem->update([
                        'delivery_status' => $newStatus
                    ]);
                }
            }

            // Send delivery notifications if needed
            if ($sendDeliveryNotification && $notificationData) {
                if ($newStatus === 'preparing') {
                    // Send DeliveryAssignmentNotification to ALL delivery personnel
                    $deliveryPersons = User::where('role', 'delivery')->get();
                    
                    // Debug log
                    \Log::debug('Found delivery persons:', [
                        'count' => $deliveryPersons->count(),
                        'delivery_person_ids' => $deliveryPersons->pluck('id')->toArray()
                    ]);
                    
                    if ($deliveryPersons->count() > 0) {
                        $notificationCount = 0;
                        foreach ($deliveryPersons as $deliveryPerson) {
                            try {
                                $deliveryPerson->notify(new DeliveryAssignmentNotification($notificationData));
                                $notificationCount++;
                                
                                \Log::debug('Notification sent to delivery person:', [
                                    'id' => $deliveryPerson->id,
                                    'name' => $deliveryPerson->firstName . ' ' . $deliveryPerson->lastName,
                                    'email' => $deliveryPerson->email
                                ]);
                            } catch (\Exception $e) {
                                \Log::error('Failed to send notification to delivery person ' . $deliveryPerson->id . ': ' . $e->getMessage());
                            }
                        }
                        
                        \Log::info("Delivery assignment notification sent to {$notificationCount} out of {$deliveryPersons->count()} delivery personnel for order #{$mealOrder->order_number}, status: {$newStatus}");
                    } else {
                        \Log::warning('No delivery persons found with role "delivery"');
                    }
                } elseif ($newStatus === 'ready_for_pickup') {
                    // Find the assigned delivery person for these items
                    // Get the delivery_person_id from MealDelivery records
                    $assignedDeliveryIds = [];
                    
                    foreach ($itemIds as $itemId) {
                        $itemId = trim($itemId);
                        $mealDelivery = MealDelivery::where('meal_order_item_id', $itemId)->first();
                        
                        if ($mealDelivery && $mealDelivery->delivery_person_id) {
                            $assignedDeliveryIds[] = $mealDelivery->delivery_person_id;
                        }
                    }
                    
                    // Get unique delivery person IDs
                    $assignedDeliveryIds = array_unique($assignedDeliveryIds);
                    
                    if (count($assignedDeliveryIds) === 1) {
                        // Only one delivery person assigned to all items
                        $deliveryPersonForPickup = User::where('id', $assignedDeliveryIds[0])
                            ->where('role', 'delivery')
                            ->first();
                            
                        if ($deliveryPersonForPickup) {
                            // Send PickupNotification only to the assigned delivery person
                            try {
                                $deliveryPersonForPickup->notify(new PickupNotification($notificationData));
                                \Log::info("Pickup notification sent to assigned delivery person ID: {$deliveryPersonForPickup->id} for order #{$mealOrder->order_number}");
                            } catch (\Exception $e) {
                                \Log::error('Failed to send pickup notification: ' . $e->getMessage());
                            }
                        } else {
                            \Log::warning("Assigned delivery person not found or not a delivery role for order #{$mealOrder->order_number}");
                        }
                    } elseif (count($assignedDeliveryIds) > 1) {
                        // Multiple delivery persons assigned - send to all assigned
                        $assignedDeliveryPersons = User::whereIn('id', $assignedDeliveryIds)
                            ->where('role', 'delivery')
                            ->get();
                            
                        if ($assignedDeliveryPersons->count() > 0) {
                            $pickupNotificationCount = 0;
                            foreach ($assignedDeliveryPersons as $deliveryPerson) {
                                try {
                                    $deliveryPerson->notify(new PickupNotification($notificationData));
                                    $pickupNotificationCount++;
                                } catch (\Exception $e) {
                                    \Log::error('Failed to send pickup notification to delivery person ' . $deliveryPerson->id . ': ' . $e->getMessage());
                                }
                            }
                            \Log::info("Pickup notification sent to {$pickupNotificationCount} out of {$assignedDeliveryPersons->count()} assigned delivery persons for order #{$mealOrder->order_number}");
                        }
                    } else {
                        // No delivery person assigned - log but don't send notification
                        \Log::info("No delivery person assigned for order #{$mealOrder->order_number}, skipping pickup notification");
                    }
                }
            }

            // Also update DeliveryChargeLedger if cancelled
            if ($newStatus === 'cancelled') {
                DeliveryChargeLedger::where('meal_order_id', $orderId)
                    ->where('client_id', $client_id)
                    ->where('delivery_date', $request->meal_date)
                    ->when($request->meal_type_id, function($query, $mealTypeId) {
                        return $query->where('meal_type_id', $mealTypeId);
                    })
                    ->update(['delivery_status' => 'cancelled']);
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery status updated successfully.',
                'data' => [
                    'delivery_status' => $newStatus,
                    'delivery_status_label' => $this->getDeliveryStatusLabel($newStatus),
                    'previous_status' => $currentStatus,
                    'previous_status_label' => $this->getDeliveryStatusLabel($currentStatus),
                    'updated_at' => now()->format('d M Y H:i:s'),
                    'updated_items_count' => count($itemIds),
                    'notification_sent' => $sendDeliveryNotification,
                    'notification_type' => $sendDeliveryNotification ? 
                        ($newStatus === 'preparing' ? 'assignment' : 'pickup') : null,
                    'pickup_notification_sent' => ($newStatus === 'ready_for_pickup' && isset($assignedDeliveryIds) && !empty($assignedDeliveryIds)) ? true : false,
                    'assigned_delivery_person_count' => isset($assignedDeliveryIds) ? count($assignedDeliveryIds) : 0
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Delivery status update error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function isValidClientStatusTransition111($currentStatus, $newStatus)
    {
        // Define valid transitions for client
        $clientTransitions = [
            'pending' => ['preparing', 'cancelled'],
            'preparing' => ['ready_for_pickup', 'cancelled'],
            'ready_for_pickup' => ['cancelled'], // Client can only cancel after ready_for_pickup
        ];
        
        // Final states cannot be changed
        $finalStates = ['picked_up', 'on_the_way', 'arrived', 'delivered', 'cancelled'];
        
        // Check if current status is final
        if (in_array($currentStatus, $finalStates)) {
            return false;
        }
        
        // Check if transition is allowed
        if (isset($clientTransitions[$currentStatus])) {
            return in_array($newStatus, $clientTransitions[$currentStatus]);
        }
        
        return false;
    }

    private function getDeliveryStatusLabel111($status)
    {
        $labels = [
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready_for_pickup' => 'Ready for Pickup',
            'picked_up' => 'Picked Up',
            'on_the_way' => 'On the Way',
            'arrived' => 'Arrived',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$status] ?? $status;
    }

    public function updateDeliveryStatus11(Request $request, $orderId)
    {
        try {
            $client_id = $request->header('id');
            
            // Validate request - simplified validation since meal_date already exists in all meal_order_items
            $request->validate([
                'meal_order_item_id' => 'required|string',
                'delivery_status' => 'required|string|in:pending,preparing,ready_for_pickup,picked_up,on_the_way,arrived,delivered,failed,cancelled',
                'notes' => 'nullable|string|max:500',
                'meal_date' => 'required|date', // Just validate date format, no need for exists check
                'meal_type_id' => 'nullable|integer|exists:meal_types,id'
            ]);

            // Parse comma-separated item IDs if multiple
            $itemIds = explode(',', $request->meal_order_item_id);
            
            // Start database transaction
            DB::beginTransaction();

            foreach ($itemIds as $itemId) {
                $itemId = trim($itemId);
                
                // Check if client owns this meal order item
                $mealOrderItem = MealOrderItem::where('id', $itemId)
                    ->where('client_id', $client_id)
                    ->where('meal_order_id', $orderId)
                    ->where('meal_date', $request->meal_date) // Still include this for security
                    ->first();

                if (!$mealOrderItem) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Meal order item not found or you do not have permission to update it.'
                    ], 404);
                }

                // Update or create meal delivery record
                $mealDelivery = MealDelivery::updateOrCreate(
                    ['meal_order_item_id' => $itemId],
                    [
                        'delivery_status' => $request->delivery_status,
                        'delivery_notes' => $request->notes,
                        'updated_at' => now()
                    ]
                );

                // Create status history record
                if ($mealDelivery) {
                    // If status is delivered, update actual delivery time
                    if ($request->delivery_status === 'delivered') {
                        $mealDelivery->update([
                            'actual_delivery_time' => now()
                        ]);
                        
                        // Also update the meal order item
                        $mealOrderItem->update([
                            'delivered_time' => now()
                        ]);
                    }

                    // If status is picked_up, update pickup time
                    if ($request->delivery_status === 'picked_up') {
                        $mealDelivery->update([
                            'pickup_time' => now()
                        ]);
                    }
                    
                    // Update the meal order item's delivery_status field
                    $mealOrderItem->update([
                        'delivery_status' => $request->delivery_status
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery status updated successfully.',
                'data' => [
                    'delivery_status' => $request->delivery_status,
                    'delivery_status_label' => $this->getDeliveryStatusLabel($request->delivery_status),
                    'updated_at' => now()->format('d M Y H:i:s'),
                    'updated_items_count' => count($itemIds)
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function viewOrderDetailsByDate(Request $request)
    {
        return view('client.pages.meal-order.view_order_details_by_date');
    }

    public function getMealOrderDetailsByDate(Request $request, $orderId, $date)
    {
        try {
            $client_id = $request->header('id');

            // Get the meal order with filtering for the specific client and date
            $order = MealOrder::whereHas('items', function($query) use ($client_id, $date) {
                    $query->where('client_id', $client_id)
                          ->where('meal_date', $date);
                })
                ->with([
                    'items' => function($query) use ($client_id, $date) {
                        // Only load items for this specific client and date
                        $query->where('client_id', $client_id)
                              ->where('meal_date', $date)
                              ->with(['mealType', 'product.nutrient', 'client:id,firstName,lastName,mobile,address1,address2,zip_code,city_id,county_id,country_id,latitude,longitude']);
                    },
                    'mealShippingAddress.country',
                    'mealShippingAddress.county', 
                    'mealShippingAddress.city'
                ])
                ->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            // Filter items to only include this client's items for the specific date
            $clientItems = $order->items->filter(function($item) use ($client_id, $date) {
                return $item->client_id == $client_id && $item->meal_date == $date;
            });

            if ($clientItems->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No items found for this client on the specified date.',
                ], 404);
            }

            // Group items by meal type for the specific date
            $groupedItems = $clientItems->groupBy(function($item) {
                return $item->mealType->name ?? 'Other';
            });

            // Calculate nutrition for this client on the specific date
            $totalCalories = $clientItems->sum(function ($item) {
                return ($item->product->nutrient->calories ?? 0) * $item->quantity;
            });

            $caloriesByMealType = $clientItems->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            })->map(function ($group) {
                return $group->sum(function ($item) {
                    return ($item->product->nutrient->calories ?? 0) * $item->quantity;
                });
            });

            // Calculate summary for this client on the specific date
            $dateSubtotal = $clientItems->sum('total_price');
            
            // Use tax rate from config with fallback to 20%
            $taxRate = (float) config('services.tax_rate', 0.20);
            $dateTax = $dateSubtotal * $taxRate;

            // Calculate delivery charge for this specific client and date
            $dateDeliveryCharge = $this->calculateDeliveryChargeForDate($order, $date);
            $dateTotal = $dateSubtotal + $dateTax + $dateDeliveryCharge;

            // Get client-specific order status for this date
            $clientOrder = ClientMealOrder::where('meal_order_id', $orderId)
                ->where('client_id', $client_id)
                ->first();

            $clientPaymentStatus = $clientOrder->payment_status ?? $order->payment_status;

            $summary = [
                'subtotal' => floatval($dateSubtotal),
                'tax' => floatval($dateTax),
                'tax_rate' => floatval($taxRate * 100), // Convert to percentage for display
                'delivery_charge' => floatval($dateDeliveryCharge),
                'total' => floatval($dateTotal),
                'total_items' => $clientItems->sum('quantity')
            ];

            // Get items with meal_time for the response
            $itemsWithTime = $clientItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'meal_date' => $item->meal_date,
                    'meal_time' => $item->meal_time,
                    'meal_type' => $item->mealType ? [
                        'id' => $item->mealType->id,
                        'name' => $item->mealType->name
                    ] : null,
                    'product' => $item->product,
                    'client' => $item->client,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'payment_status' => $clientPaymentStatus,
                        'created_at' => $order->created_at,
                    ],
                    'selected_date' => $date,
                    'summary' => $summary,
                    'meal_cart' => $groupedItems,
                    'nutrition' => [
                        'total_calories' => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'delivery_calculation' => [
                        'calculated_charge' => $dateDeliveryCharge,
                        'calculation_method' => 'distance_based_per_client_meal_type'
                    ],
                    'items' => $itemsWithTime // Include items with meal_time
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function calculateDeliveryChargeForDate($order, $date)
    {
        try {
            $totalDeliveryCharge = 0;
            
            // Get customer shipping address
            $shippingAddress = $order->mealShippingAddress;
            if (!$shippingAddress) {
                \Log::warning("No shipping address found for order: {$order->id}");
                return 0;
            }

            $customerShippingAddress = [
                'city_id'  => $shippingAddress->city_id,
                'address1' => $shippingAddress->address1,
                'zip_code' => $shippingAddress->zip_code,
                'latitude' => $shippingAddress->latitude,
                'longitude' => $shippingAddress->longitude,
            ];

            // Get items for this specific date
            $dateItems = $order->items->where('meal_date', $date);
            
            if ($dateItems->isEmpty()) {
                return 0;
            }

            // Group by client_id and meal_type_id to avoid duplicate charges for same client+meal_type on same date
            $clientMealTypeGroups = [];
            
            foreach ($dateItems as $item) {
                $clientId = $item->client_id;
                $mealTypeId = $item->meal_type_id;
                $key = $clientId . '_' . $mealTypeId;
                
                if (!isset($clientMealTypeGroups[$key])) {
                    $clientMealTypeGroups[$key] = [
                        'client_id' => $clientId,
                        'meal_type_id' => $mealTypeId,
                        'client' => $item->client,
                        'meal_type' => $item->mealType,
                        'items' => []
                    ];
                }
                $clientMealTypeGroups[$key]['items'][] = $item;
            }

            // Calculate delivery charge for each unique client+meal_type combination
            foreach ($clientMealTypeGroups as $group) {
                $client = $group['client'];
                $mealTypeId = $group['meal_type_id'];
                
                if (!$client) {
                    \Log::warning("Client not found for client_id: {$group['client_id']}");
                    continue;
                }

                // Ensure client has valid address
                if (!$client->city_id || !$client->address1 || !$client->zip_code) {
                    \Log::warning("Client address incomplete for client: {$client->id}");
                    continue;
                }

                $clientAddress = [
                    'city_id'  => $client->city_id,
                    'address1' => $client->address1,
                    'zip_code' => $client->zip_code,
                    'latitude' => $client->latitude,
                    'longitude' => $client->longitude,
                ];

                // Calculate distance
                $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                
                if ($distance === null) {
                    \Log::warning("Distance calculation failed for client: {$client->id}");
                    // Use default charge
                    $charge = $this->getDefaultDeliveryCharge(5); // Default to 5km distance
                    $totalDeliveryCharge += $charge;
                    continue;
                }

                // Get delivery charge configuration for this client + meal type
                $deliveryChargeConfig = MealDeliveryCharge::where('client_id', $client->id)
                    ->where('meal_type_id', $mealTypeId)
                    ->first();

                if (!$deliveryChargeConfig) {
                    \Log::info("No specific delivery charge found for client {$client->id}, meal type {$mealTypeId}, using default");
                    $charge = $this->getDefaultDeliveryCharge($distance);
                    $totalDeliveryCharge += $charge;
                    continue;
                }

                // Determine charge based on distance (same logic as storeByCash)
                if ($distance <= 2) {
                    $charge = $deliveryChargeConfig->inside_city_2km;
                } elseif ($distance <= 5) {
                    $charge = $deliveryChargeConfig->inside_city_5km;
                } elseif ($distance <= 10) {
                    $charge = $deliveryChargeConfig->inside_city_10km;
                } else {
                    $charge = $deliveryChargeConfig->inside_city_above_10km;
                }

                $totalDeliveryCharge += $charge;

                \Log::info("Delivery charge for client {$client->id}, meal type {$mealTypeId} on {$date}: \${$charge} for {$distance} km");
            }

            \Log::info("Total delivery charge for order {$order->id}, date {$date}: \${$totalDeliveryCharge}");

            return $totalDeliveryCharge;

        } catch (Exception $e) {
            \Log::error('Delivery charge calculation error for date: ' . $e->getMessage());
            return 0;
        }
    }

    private function getDistanceBetweenLocations($clientAddress, $shippingAddress)
    {
        // Google API key from config
        $apiKey = config('services.google_maps.api_key');

        if (!$apiKey) {
            \Log::error('Google Maps API key not configured');
            return null;
        }

        try {
            // Construct origin & destination with full addresses
            $origin = urlencode("{$clientAddress['address1']}, {$clientAddress['zip_code']}");
            $destination = urlencode("{$shippingAddress['address1']}, {$shippingAddress['zip_code']}");

            // Build URL for Google Distance Matrix API
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&mode=driving&units=metric&key={$apiKey}";

            $response = Http::timeout(10)->get($url);
            $data = $response->json();

            \Log::info("Distance API Response for client {$clientAddress['address1']} to customer {$shippingAddress['address1']}:", $data);

            if (isset($data['rows'][0]['elements'][0]['status']) && 
                $data['rows'][0]['elements'][0]['status'] === 'OK') {
                
                // Distance in meters
                $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
                // Convert to KM
                $distanceKm = $distanceMeters / 1000;
                
                \Log::info("Distance calculated: {$distanceKm} km");
                return round($distanceKm, 2);
            }

            \Log::warning("Distance API returned status: " . ($data['rows'][0]['elements'][0]['status'] ?? 'UNKNOWN'));
            return null;

        } catch (\Exception $e) {
            \Log::error("Distance calculation error: " . $e->getMessage());
            return null;
        }
    }

    private function getDefaultDeliveryCharge($distance)
    {
        if ($distance <= 2) {
            return 5.00;
        } elseif ($distance <= 5) {
            return 8.00;
        } elseif ($distance <= 10) {
            return 12.00;
        } else {
            return 15.00 + (ceil($distance - 10) * 2); // $2 per km after 10km
        }
    }

}