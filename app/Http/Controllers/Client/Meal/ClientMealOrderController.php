<?php
namespace App\Http\Controllers\Client\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\Delivery\NewDeliveryAvailableNotification;
use App\Notifications\Delivery\OrderReadyForPickupNotification;
use App\Notifications\MealOrder\OrderAcceptedByRestaurantNotification;
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
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use Exception;
use Carbon\Carbon;


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
            $clientOrders = ClientMealOrder::with([
                'mealOrder:id,invoice_no,created_at,customer_id',
                'mealOrder.items:id,meal_order_id,meal_date' // Load meal items to get meal_date
            ])
            ->where('client_id', $client_id)
            ->latest()
            ->get();

            // Format data for response
            $data = $clientOrders->map(function ($item) {
                $mealOrder = $item->mealOrder;
                $client = $item->client;
                
                // Get customer name from meal order
                $customerName = '';
                if ($mealOrder && $mealOrder->customer) {
                    $customerName = trim($mealOrder->customer->firstName . ' ' . ($mealOrder->customer->lastName ?? ''));
                }

                // Get the latest meal_date from meal order items
                $latestMealDate = '-';
                if ($mealOrder && $mealOrder->items && $mealOrder->items->isNotEmpty()) {
                    // Get the maximum (latest) meal_date
                    $latestMealDate = $mealOrder->items->max('meal_date');
                    
                    // Format the date if it's not null
                    if ($latestMealDate) {
                        try {
                            $latestMealDate = Carbon::parse($latestMealDate)->format('d M Y');
                        } catch (\Exception $e) {
                            $latestMealDate = '-';
                        }
                    } else {
                        $latestMealDate = '-';
                    }
                }

                return [
                    'id' => $item->id,
                    'order_id' => $item->meal_order_id,
                    'order_date' => $mealOrder ? $mealOrder->created_at->format('d M Y') : '-',
                    'invoice_no' => $mealOrder ? $mealOrder->invoice_no : '-',
                    'customer_name' => $customerName,
                    'meal_date' => $latestMealDate, // Add latest meal_date
                    'payable_amount' => isset($item->payable_amount) ? '£' . number_format($item->payable_amount, 2) : '£0.00',
                    'payment_status' => $item->payment_status ? ucfirst($item->payment_status) : 'Unknown'
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving client payments.',
                'error' => $e->getMessage()
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

            $client = User::find($client_id);
            if (!$client) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Client not found.'
                ], 404);
            }

            $order = MealOrder::with(['customer:id,firstName,lastName,email,mobile,image'])
                ->find($order_id);
            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found.'
                ], 404);
            }

            $clientMealOrder = ClientMealOrder::with([
                'mealOrder:id,order_number,invoice_no,status,created_at,delivery_fee,subtotal,tax,payable_amount,payment_status',
                'client:id,firstName,lastName,email,mobile,image',
            ])
            ->where('client_id',     $client_id)
            ->where('meal_order_id', $order_id)
            ->first();

            // ← Simply return 404 — no virtual object needed
            if (!$clientMealOrder) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Payment record not found for this client and order.'
                ], 404);
            }

            $mealOrderItems = MealOrderItem::with([
                'mealType:id,name',
                'product:id,name,image,price',
                'client:id,firstName,lastName',
            ])
            ->where('client_id',     $client_id)
            ->where('meal_order_id', $order_id)
            ->get();

            if ($mealOrderItems->isEmpty()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'No items found for this client in the specified order.'
                ], 404);
            }

            $clientMealOrder->mealOrderItems = $mealOrderItems;

            // Get total paid from payment history
            $totalPaidFromHistory = \App\Models\ClientMealPaymentHistory::where(
                'client_meal_order_id', $clientMealOrder->id
            )->sum('paid_amount');

            // Delivery ledgers
            $deliveryStatuses      = DeliveryChargeLedger::STATUS_LABELS;
            $deliveryChargeLedgers = DeliveryChargeLedger::with([
                'mealType:id,name',
                'deliveryPerson:id,firstName,lastName',
                'statusHistories' => fn($q) => $q->latest()->take(5),
            ])
            ->where('meal_order_id', $order_id)
            ->where('client_id',     $client_id)
            ->get();

            // Build lookup: meal_type_id + delivery_date → ledger
            $deliveryLedgerLookup = [];
            foreach ($deliveryChargeLedgers as $ledger) {
                $key = $ledger->meal_type_id . '_' . Carbon::parse($ledger->delivery_date)->format('Y-m-d');
                $deliveryLedgerLookup[$key] = $ledger;
            }

            // Group items by meal_date → meal_type
            $groupedItems = [];

            foreach ($mealOrderItems as $item) {
                $mealDate     = $item->meal_date;
                $mealTypeName = $item->mealType ? $item->mealType->name : 'Other';
                $mealTypeId   = $item->meal_type_id;
                $lookupKey    = $mealTypeId . '_' . Carbon::parse($mealDate)->format('Y-m-d');
                $ledger       = $deliveryLedgerLookup[$lookupKey] ?? null;

                $deliveryStatus      = $ledger ? $ledger->delivery_status : 'pending';
                $deliveryStatusLabel = $deliveryStatuses[$deliveryStatus] ?? ucfirst($deliveryStatus);
                $deliveryPersonName  = $ledger?->deliveryPerson
                    ? $ledger->deliveryPerson->firstName . ' ' . $ledger->deliveryPerson->lastName
                    : 'Not Assigned';

                if (!isset($groupedItems[$mealDate][$mealTypeName])) {
                    $groupedItems[$mealDate][$mealTypeName] = [
                        'items'                     => [],
                        'meal_type_id'              => $mealTypeId,
                        'client_id'                 => $item->client_id,
                        'meal_date'                 => $mealDate,
                        'delivery_status'           => $deliveryStatus,
                        'delivery_status_label'     => $deliveryStatusLabel,
                        'delivery_person_name'      => $deliveryPersonName,
                        'delivery_charge_ledger_id' => $ledger?->id,
                        'total_price'               => 0,
                    ];
                }

                $groupedItems[$mealDate][$mealTypeName]['items'][] = [
                    'id'                        => $item->id,
                    'product_name'              => $item->product ? $item->product->name : 'N/A',
                    'product_image'             => $item->product ? $item->product->image : null,
                    'unit_price'                => $item->unit_price,
                    'quantity'                  => $item->quantity,
                    'total_price'               => $item->total_price,
                    'meal_type_id'              => $mealTypeId,
                    'meal_type_name'            => $mealTypeName,
                    'client_name'               => $item->client
                        ? $item->client->firstName . ' ' . $item->client->lastName
                        : 'N/A',
                    'meal_time'                 => $item->meal_time,
                    'delivery_status'           => $deliveryStatus,
                    'delivery_status_label'     => $deliveryStatusLabel,
                    'delivery_person_name'      => $deliveryPersonName,
                    'delivery_charge_ledger_id' => $ledger?->id,
                    'has_delivery_record'       => !is_null($ledger),
                ];

                $groupedItems[$mealDate][$mealTypeName]['total_price'] += $item->total_price;
            }

            $mealTypeIds = $mealOrderItems->pluck('meal_type_id')->filter()->unique()->values();
            $mealTypes   = MealType::whereIn('id', $mealTypeIds)->get(['id', 'name']);

            $payableAmount = (float) ($clientMealOrder->payable_amount ?? 0);
            $platformFee   = (float) ($clientMealOrder->platform_fee   ?? 0);
            $subtotal      = (float) ($clientMealOrder->subtotal        ?? 0);
            $tax           = (float) ($clientMealOrder->tax             ?? 0);
            $balanceAmount = $payableAmount - $totalPaidFromHistory;

            // Calculate rates for display
            $taxRate         = $subtotal > 0 ? round(($tax / $subtotal) * 100, 0)         : 0;
            $platformFeeRate = $subtotal > 0 ? round(($platformFee / $subtotal) * 100, 0) : 0;

            $data = [
                'client_meal_order' => [
                    'id'             => $clientMealOrder->id,
                    'subtotal'       => $subtotal,
                    'tax'            => $tax,
                    'platform_fee'   => $platformFee,
                    'delivery_fee'   => (float) ($clientMealOrder->delivery_fee ?? 0),
                    'payable_amount' => $payableAmount,
                    'paid_amount'    => $totalPaidFromHistory,
                    'payment_status' => $clientMealOrder->payment_status,
                    'created_at'     => $clientMealOrder->created_at->format('d M Y H:i:s'),
                    'updated_at'     => $clientMealOrder->updated_at->format('d M Y H:i:s'),
                ],
                'order' => [
                    'order_number'   => $order->order_number,
                    'invoice_no'     => $order->invoice_no,
                    'status'         => $order->status,
                    'created_at'     => $order->created_at->format('d M Y H:i:s'),
                    'payment_status' => $order->payment_status,
                ],
                'client' => [
                    'id'     => $client->id,
                    'name'   => trim($client->firstName . ' ' . $client->lastName),
                    'email'  => $client->email,
                    'mobile' => $client->mobile,
                    'image'  => $client->image,
                ],
                'customer' => [
                    'id'     => $order->customer->id    ?? null,
                    'name'   => $order->customer
                        ? ucwords(strtolower(trim($order->customer->firstName . ' ' . $order->customer->lastName)))
                        : null,
                    'email'  => $order->customer->email  ?? null,
                    'mobile' => $order->customer->mobile ?? null,
                    'image'  => $order->customer->image  ?? null,
                ],
                'items'                   => $groupedItems,
                'delivery_charge_ledgers' => $deliveryChargeLedgers->map(fn($ledger) => [
                    'id'                  => $ledger->id,
                    'meal_type_id'        => $ledger->meal_type_id,
                    'meal_type_name'      => $ledger->mealType->name ?? null,
                    'delivery_charge'     => $ledger->delivery_charge,
                    'delivery_person_id'  => $ledger->delivery_person_id,
                    'delivery_person_name'=> $ledger->deliveryPerson
                        ? $ledger->deliveryPerson->firstName . ' ' . $ledger->deliveryPerson->lastName
                        : null,
                    'delivery_status'      => $ledger->delivery_status,
                    'delivery_status_label'=> $deliveryStatuses[$ledger->delivery_status] ?? 'Unknown',
                    'payment_status'       => $ledger->payment_status,
                    'delivery_date'        => $ledger->delivery_date,
                    'order_tracking'       => $ledger->order_tracking,
                    'status_histories'     => $ledger->statusHistories->map(fn($h) => [
                        'delivery_status'  => $h->delivery_status,
                        'notes'            => $h->notes,
                        'updated_by_label' => $h->updated_by_label,
                        'created_at'       => $h->created_at->format('Y-m-d H:i:s'),
                    ]),
                ]),
                'delivery_statuses' => $deliveryStatuses,
                'summary' => [
                    'total_items'      => $mealOrderItems->count(),
                    'total_dates'      => count($groupedItems),
                    'total_meal_types' => $mealTypes->count(),
                    'subtotal'         => $subtotal,
                    'tax'              => $tax,
                    'tax_rate'         => $taxRate,
                    'platform_fee'     => $platformFee,
                    'platform_fee_rate'=> $platformFeeRate,
                    'delivery_fee'     => (float) ($clientMealOrder->delivery_fee ?? 0),
                    'total_amount'     => $payableAmount,
                    'paid_amount'      => $totalPaidFromHistory,
                    'balance_amount'   => $balanceAmount < 0 ? 0 : $balanceAmount,
                    'total_groups'     => array_reduce($groupedItems, fn($carry, $d) => $carry + count($d), 0),
                ],
                'meal_types'    => $mealTypes,
                'dates'         => array_keys($groupedItems),
                'items_with_time' => $mealOrderItems->map(fn($item) => [
                    'id'             => $item->id,
                    'meal_date'      => $item->meal_date,
                    'meal_time'      => $item->meal_time,
                    'meal_type_id'   => $item->meal_type_id,
                    'meal_type_name' => $item->mealType ? $item->mealType->name : 'Other',
                ])->toArray(),
            ];

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (Exception $e) {
            Log::error('getMealOrderDetails: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while retrieving order details.',
            ], 500);
        }
    }



    public function updateDeliveryStatus(Request $request, $orderId)
    {
        try {
            $client_id = $request->header('id');
            
            // Validate client exists
            $client = User::where('id', $client_id)->first();
            if (!$client) {
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
                'meal_date' => 'required|date',
                'meal_type_id' => 'required|exists:meal_types,id'
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
            $mealDate = $request->meal_date;
            $mealTypeId = $request->meal_type_id;

            // Find the DeliveryChargeLedger for this client, meal_type, and delivery date
            $deliveryLedger = DeliveryChargeLedger::where('meal_order_id', $orderId)
                ->where('client_id', $client_id)
                ->where('meal_type_id', $mealTypeId)
                ->whereDate('delivery_date', $mealDate)
                ->first();

            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found for the specified date and meal type.'
                ], 404);
            }

            // Get meal order and customer details
            $mealOrder = MealOrder::find($orderId);
            $customer = User::find($mealOrder->customer_id);
            $mealType = MealType::find($mealTypeId);
            $mealShippingAddress = MealShippingAddress::where('meal_order_id', $orderId)->first();

            // Define client's valid status transitions
            $validClientTransitions = [
                'pending' => ['accept_order', 'cancelled'],
                'accept_order' => ['preparing', 'cancelled'],
                'preparing' => ['ready_for_pickup', 'cancelled'],
            ];

            $currentStatus = $deliveryLedger->delivery_status;
            
            // Check if this is a valid transition for client
            if (!isset($validClientTransitions[$currentStatus]) || 
                !in_array($newStatus, $validClientTransitions[$currentStatus])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Cannot change status from {$currentStatus} to {$newStatus}."
                ], 400);
            }

            // Special validation for accept_order -> preparing transition
            if ($currentStatus === 'accept_order' && $newStatus === 'preparing') {
                if (!$deliveryLedger->delivery_person_id) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Cannot change to preparing. No delivery person has accepted the order yet.'
                    ], 400);
                }
            }

            // Special validation for preparing -> ready_for_pickup transition
            if ($currentStatus === 'preparing' && $newStatus === 'ready_for_pickup') {
                if (!$deliveryLedger->delivery_person_id) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Cannot change to ready_for_pickup. No delivery person has accepted the order yet.'
                    ], 400);
                }
            }

            DB::beginTransaction();

            try {
                // Store old status for notification logic
                $oldStatus = $deliveryLedger->delivery_status;
                
                // Update the delivery ledger status
                $deliveryLedger->delivery_status = $newStatus;
                $deliveryLedger->save();

                // Prepare status history data
                $statusHistoryData = [
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'delivery_status' => $newStatus,
                    'notes' => $notes,
                    'updated_by_id' => $client_id,
                    'updated_by_type' => 'client'
                ];

                // If changing to ready_for_pickup and pickup_time is provided, set pick_up_at (scheduled pickup time)
                if ($newStatus === 'ready_for_pickup') {
                    if ($pickupTime) {
                        // Since you imported Carbon\Carbon at the top, you can use:
                        $statusHistoryData['pick_up_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $pickupTime);
                    } else {
                        // Default to now + 1 hour if no pickup time provided
                        $statusHistoryData['pick_up_at'] = now()->addHour();
                    }
                }

                // Create status history
                MealDeliveryStatusHistory::create($statusHistoryData);

                // Send notifications based on status change
                $notificationSent = false;
                $customerNotified = false;
                
                if ($oldStatus === 'pending' && $newStatus === 'accept_order') {
                    // Send notification ONLY to customer that order is accepted (no email)
                    if ($customer) {
                        $customerNotified = $this->sendCustomerNotification($customer, $deliveryLedger, $mealType, $client, $mealOrderItems);
                    }
                    
                    // Broadcast notification to all delivery persons about new order (no email)
                    $deliveryNotificationsSent = $this->sendBroadcastNotificationToDeliveryPersons($deliveryLedger, $mealOrderItems);
                    $notificationSent = true;
                }

                if ($oldStatus === 'preparing' && $newStatus === 'ready_for_pickup') {
                    // Notify assigned delivery person that order is ready for pickup
                    if ($deliveryLedger->delivery_person_id) {
                        $notificationResult = $this->sendReadyForPickupNotification($deliveryLedger, $pickupTime,$mealOrderItems);
                        $notificationSent = $notificationResult;
                    }
                }
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Delivery status updated successfully.',
                    'data' => [
                        'delivery_ledger_id' => $deliveryLedger->id,
                        'tracking_number' => $deliveryLedger->order_tracking,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'scheduled_pickup_time' => ($newStatus === 'ready_for_pickup') ? ($pickupTime ?: now()->addHour()->format('Y-m-d H:i:s')) : null,
                        'notification_sent' => $notificationSent,
                        'customer_notified' => $customerNotified,
                        'delivery_person_assigned' => !is_null($deliveryLedger->delivery_person_id),
                        'items_updated' => $mealOrderItems->count(),
                        'item_ids' => $itemIds
                    ]
                ], 200);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            \Log::error('Error updating delivery status: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function sendCustomerNotification($customer, $deliveryLedger, $mealType, $client, $mealOrderItems)
    {
        try {
            // Create the notification using the notification class
            $notification = new \App\Notifications\MealOrder\OrderAcceptedByRestaurantNotification(
                $deliveryLedger,
                MealOrder::find($deliveryLedger->meal_order_id),
                $mealType,
                $client,
                $mealOrderItems,
                null // or fetch shipping address if needed
            );
            
            $customer->notify($notification);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function sendBroadcastNotificationToDeliveryPersons(DeliveryChargeLedger $ledger, $mealOrderItems)
    {
        try {
            $deliveryPersons = User::where('role', 'delivery')
                ->get();

            if ($deliveryPersons->isEmpty()) {
                return 0;
            }

            // Get client details
            $client = $ledger->client;
            if (!$client) {
                \Log::error('Client not found for ledger ID: ' . $ledger->id);
                return 0;
            }

            // Get customer and shipping address
            $customer = $ledger->customer;
            $mealShippingAddress = MealShippingAddress::where('meal_order_id', $ledger->meal_order_id)->first();
            $mealOrder = $ledger->mealOrder;
            $mealType = $ledger->mealType;

            // Calculate items count
            $itemsCount = $mealOrderItems->count();
            $mealTime = $mealOrderItems->first()->meal_time ?? null;

            // Prepare notification data
            $notificationData = [
                'delivery_charge_ledger_id' => $ledger->id,
                'order_tracking' => $ledger->order_tracking,
                'meal_order_id' => $ledger->meal_order_id,
                'order_number' => $mealOrder ? $mealOrder->order_number : 'N/A',
                'client_id' => $client->id,
                'client_email' => $client->email,
                'client_name' => $client->firstName . ' ' . $client->lastName,
                'client_mobile' => $client->mobile,
                'client_address' => [
                    'address1' => $client->address1,
                    'address2' => $client->address2,
                    'city' => $client->city ? $client->city->name : null,
                    'county' => $client->county ? $client->county->name : null,
                    'country' => $client->country ? $client->country->name : null,
                    'zip_code' => $client->zip_code,
                    'latitude' => $client->latitude,
                    'longitude' => $client->longitude
                ],
                'delivery_address' => $mealShippingAddress ? [
                    'name' => $mealShippingAddress->name,
                    'email' => $mealShippingAddress->email,
                    'phone' => $mealShippingAddress->phone,
                    'address1' => $mealShippingAddress->address1,
                    'address2' => $mealShippingAddress->address2,
                    'city' => $mealShippingAddress->city ? $mealShippingAddress->city->name : null,
                    'county' => $mealShippingAddress->county ? $mealShippingAddress->county->name : null,
                    'country' => $mealShippingAddress->country ? $mealShippingAddress->country->name : null,
                    'zip_code' => $mealShippingAddress->zip_code,
                    'latitude' => $mealShippingAddress->latitude,
                    'longitude' => $mealShippingAddress->longitude
                ] : null,
                'delivery_details' => [
                    'delivery_date' => $ledger->delivery_date,
                    'meal_time' => $mealTime,
                    'delivery_charge' => $ledger->delivery_charge,
                    'distance_km' => $ledger->distance_km,
                    'distance_category' => $ledger->distance_category,
                    'meal_type' => $mealType ? $mealType->name : null,
                    'meal_type_id' => $ledger->meal_type_id
                ],
                'customer_details' => $customer ? [
                    'name' => $customer->firstName . ' ' . $customer->lastName,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email
                ] : null,
                'items_count' => $itemsCount,
                'items' => $mealOrderItems->map(function($item) {
                    return [
                        'product_name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'meal_time' => $item->meal_time,
                    ];
                })->toArray(),
                'action_url' => '/api/delivery/accept/' . $ledger->id,
                'notification_type' => 'new_delivery_available',
                'created_at' => now()->toDateTimeString(),
                'accept_deadline' => now()->addHours(2)->toDateTimeString()
            ];

            // Send notification to all delivery persons (NO EMAIL)
            $sentCount = 0;
            foreach ($deliveryPersons as $deliveryPerson) {
                try {
                    // Use the notification class but only database channel (no email)
                    // The notification class has been updated to only use database
                    $deliveryPerson->notify(new NewDeliveryAvailableNotification($notificationData));
                    
                    $sentCount++;
                    
                    \Log::info('New delivery notification saved to database for delivery person ID: ' . $deliveryPerson->id);
                    
                } catch (Exception $e) {
                    \Log::error('Failed to send notification to delivery person ' . $deliveryPerson->id . ': ' . $e->getMessage());
                    continue;
                }
            }

            \Log::info('Broadcast notification sent to ' . $sentCount . ' delivery persons for ledger ID: ' . $ledger->id);
            return $sentCount;

        } catch (Exception $e) {
            \Log::error('Error in sendBroadcastNotificationToDeliveryPersons: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return 0;
        }
    }

    private function sendReadyForPickupNotification(DeliveryChargeLedger $ledger, $scheduledPickupTime = null, $mealOrderItems)
    {
        try {
            $deliveryPerson = $ledger->deliveryPerson;
            if (!$deliveryPerson) {
                \Log::warning('No delivery person assigned for ledger ID: ' . $ledger->id);
                return;
            }

            $client = $ledger->client;
            $mealShippingAddress = MealShippingAddress::where('meal_order_id', $ledger->meal_order_id)->first();
            $mealOrder = $ledger->mealOrder;
            $mealType = $ledger->mealType;
            $customer = $ledger->customer;
            $itemsCount = $mealOrderItems->count();

            // Get the latest status history to get the scheduled pickup time
            $latestStatus = MealDeliveryStatusHistory::where('delivery_charge_ledger_id', $ledger->id)
                ->where('delivery_status', 'ready_for_pickup')
                ->latest()
                ->first();

            $scheduledTime = $latestStatus ? $latestStatus->pick_up_at : $scheduledPickupTime;
            $mealTime = null;

            if ($mealOrderItems && $mealOrderItems->isNotEmpty()) {
                $mealTime = $mealOrderItems->first()->meal_time;
            } else {
                // Fallback: Fetch meal_time from meal_order_items table
                $mealOrderItem = MealOrderItem::where('meal_order_id', $ledger->meal_order_id)
                    ->where('client_id', $ledger->client_id)
                    ->where('meal_type_id', $ledger->meal_type_id)
                    ->whereDate('meal_date', $ledger->delivery_date)
                    ->first();
                $mealTime = $mealOrderItem->meal_time ?? null;
            }

            $notificationData = [
                'delivery_charge_ledger_id' => $ledger->id,
                'order_tracking' => $ledger->order_tracking,
                'meal_order_id' => $ledger->meal_order_id,
                'order_number' => $mealOrder ? $mealOrder->order_number : 'N/A',
                'client_id' => $client->id,
                'client_email' => $client->email,
                'client_name' => $client->firstName . ' ' . $client->lastName,
                'client_mobile' => $client->mobile,
                'client_address' => [
                    'address1' => $client->address1,
                    'address2' => $client->address2,
                    'city' => $client->city ? $client->city->name : null,
                    'county' => $client->county ? $client->county->name : null,
                    'country' => $client->country ? $client->country->name : null,
                    'zip_code' => $client->zip_code,
                    'latitude' => $client->latitude,
                    'longitude' => $client->longitude
                ],
                'delivery_address' => $mealShippingAddress ? [
                    'name' => $mealShippingAddress->name,
                    'phone' => $mealShippingAddress->phone,
                    'address1' => $mealShippingAddress->address1,
                    'address2' => $mealShippingAddress->address2,
                    'city' => $mealShippingAddress->city ? $mealShippingAddress->city->name : null,
                    'zip_code' => $mealShippingAddress->zip_code,
                    'latitude' => $mealShippingAddress->latitude,
                    'longitude' => $mealShippingAddress->longitude
                ] : null,
                'delivery_details' => [
                    'scheduled_pickup_time' => $scheduledTime,
                    'pick_up_at' => $scheduledTime, // For backward compatibility
                    'delivery_date' => $ledger->delivery_date,
                    'delivery_charge' => $ledger->delivery_charge,
                    'distance_km' => $ledger->distance_km,
                    'meal_type' => $mealType ? $mealType->name : null,
                    'meal_time' => $mealTime,
                ],
                'customer_details' => $customer ? [
                    'name' => $customer->firstName . ' ' . $customer->lastName,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email
                ] : null,
                'items_count' => $itemsCount,
                'items' => $mealOrderItems->map(function($item) {
                    return [
                        'product_name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'meal_time' => $item->meal_time,
                    ];
                })->toArray(),
                'action_url' => '/api/delivery/order-details/' . $ledger->id,
                'notification_type' => 'order_ready_for_pickup',
                'created_at' => now()->toDateTimeString()
            ];

            // Send notification
            $deliveryPerson->notify(new OrderReadyForPickupNotification($notificationData));
            
            \Log::info('Ready for pickup notification sent to delivery person ID: ' . $deliveryPerson->id . 
                       ' with scheduled pickup time: ' . $scheduledTime);

        } catch (Exception $e) {
            \Log::error('Error in sendReadyForPickupNotification: ' . $e->getMessage());
        }
    }



    public function checkDeliveryAcceptance(Request $request, $orderId)
    {
        try {
            $client_id = $request->header('id');
            
            // Validate client exists
            $client = User::where('id', $client_id)->first();
            if (!$client || !$client->isClient()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            // Validate request
            $request->validate([
                'meal_date' => 'required|date',
                'meal_type_id' => 'required|exists:meal_types,id'
            ]);

            $mealDate = $request->meal_date;
            $mealTypeId = $request->meal_type_id;

            // Get the delivery ledger for this client, order, meal type and date
            $deliveryLedger = DeliveryChargeLedger::where('meal_order_id', $orderId)
                ->where('client_id', $client_id)
                ->where('meal_type_id', $mealTypeId)
                ->whereDate('delivery_date', $mealDate)
                ->with('deliveryPerson:id,firstName,lastName')
                ->first();

            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            // Get all meal order items for this group to count items
            $mealOrderItems = MealOrderItem::where('meal_order_id', $orderId)
                ->where('client_id', $client_id)
                ->where('meal_type_id', $mealTypeId)
                ->whereDate('meal_date', $mealDate)
                ->get();

            // Determine allowed status transitions based on current status
            $allowedNextStatuses = $this->getAllowedNextStatuses($deliveryLedger->delivery_status);

            // Check if delivery person has accepted
            $deliveryPersonAssigned = !is_null($deliveryLedger->delivery_person_id);
            $deliveryPersonName = $deliveryPersonAssigned && $deliveryLedger->deliveryPerson ? 
                $deliveryLedger->deliveryPerson->firstName . ' ' . $deliveryLedger->deliveryPerson->lastName : 
                null;

            // Determine which status transitions are blocked due to lack of delivery person
            $blockedStatuses = [];
            $allowedStatuses = [];
            
            foreach ($allowedNextStatuses as $nextStatus) {
                $isBlocked = $this->isStatusBlockedWithoutDeliveryPerson($deliveryLedger->delivery_status, $nextStatus, $deliveryPersonAssigned);
                
                if ($isBlocked) {
                    $blockedStatuses[] = $nextStatus;
                } else {
                    $allowedStatuses[] = $nextStatus;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery acceptance status retrieved.',
                'data' => [
                    'delivery_ledger_id' => $deliveryLedger->id,
                    'delivery_status' => $deliveryLedger->delivery_status,
                    'order_tracking' => $deliveryLedger->order_tracking,
                    'delivery_person_assigned' => $deliveryPersonAssigned,
                    'delivery_person_id' => $deliveryLedger->delivery_person_id,
                    'delivery_person_name' => $deliveryPersonName,
                    'delivery_date' => $deliveryLedger->delivery_date,
                    'meal_type_id' => $deliveryLedger->meal_type_id,
                    'item_count' => $mealOrderItems->count(),
                    
                    // Frontend validation helpers
                    'can_update_to_preparing' => $deliveryLedger->delivery_status === 'accept_order' && 
                                                 $deliveryPersonAssigned,
                    'can_update_to_ready_for_pickup' => $deliveryLedger->delivery_status === 'preparing' && 
                                                       $deliveryPersonAssigned,
                    
                    // Complete status transition information
                    'current_status' => $deliveryLedger->delivery_status,
                    'allowed_next_statuses' => $allowedStatuses,
                    'blocked_statuses' => $blockedStatuses,
                    'blocked_reason' => !$deliveryPersonAssigned && !empty($blockedStatuses) ? 
                        'No delivery person has accepted this order yet.' : null,
                    
                    // For display purposes
                    'status_labels' => [
                        'pending' => 'Pending',
                        'accept_order' => 'Accept Order',
                        'preparing' => 'Preparing',
                        'ready_for_pickup' => 'Ready for Pickup',
                        'picked_up' => 'Picked Up',
                        'on_the_way' => 'On the Way',
                        'arrived' => 'Arrived',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled'
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            \Log::error('Error checking delivery acceptance: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while checking delivery acceptance.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function getAllowedNextStatuses($currentStatus)
    {
        $clientTransitions = [
            'pending' => ['accept_order', 'cancelled'],
            'accept_order' => ['preparing', 'cancelled'],
            'preparing' => ['ready_for_pickup', 'cancelled'],
            'ready_for_pickup' => [], // Delivery person takes over from here
            'picked_up' => [], // Delivery person only
            'on_the_way' => [], // Delivery person only
            'arrived' => [], // Delivery person only
            'delivered' => [], // Final status
            'cancelled' => [], // Final status
        ];

        return $clientTransitions[$currentStatus] ?? [];
    }

    private function isStatusBlockedWithoutDeliveryPerson($currentStatus, $nextStatus, $hasDeliveryPerson)
    {
        // Statuses that require delivery person assignment
        $requireDeliveryPerson = [
            'accept_order' => ['preparing', 'ready_for_pickup'],
            'preparing' => ['ready_for_pickup'],
        ];

        // Check if the transition requires delivery person
        if (isset($requireDeliveryPerson[$currentStatus]) && 
            in_array($nextStatus, $requireDeliveryPerson[$currentStatus]) &&
            !$hasDeliveryPerson) {
            return true;
        }

        return false;
    }

}