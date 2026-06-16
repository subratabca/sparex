<?php
namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\Delivery\NewDeliveryAvailableNotification;
use App\Notifications\Client\DeliveryAcceptedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use App\Models\MealShippingAddress;
use App\Models\User;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\MealType;
use Carbon\Carbon;
use Exception;

class DeliveryMealOrderController extends Controller
{
    public function index()
    {
        return view('delivery.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $delivery_person_id = $request->header('id');
            $ledgers = DeliveryChargeLedger::with([
                    'mealOrder.customer',
                    'mealType', 
                    'client',
                ])
                ->whereNotNull('delivery_person_id')
                ->where('delivery_person_id', $delivery_person_id)
                ->latest()
                ->get();

            $data = [];

            foreach ($ledgers as $index => $ledger) {
                $mealTypeName = $ledger->mealType->name ?? 'N/A';

                $data[] = [
                    'sl' => $index + 1,
                    'delivery_charge_ledger_id' => $ledger->id,
                    'order_number' => $ledger->mealOrder->order_number ?? 'ORD-' . str_pad($ledger->mealOrder->id, 6, '0', STR_PAD_LEFT),
                    'meal_types' => [$mealTypeName],
                    'formatted_meal_types' => $mealTypeName,
                    'meal_date' => $ledger->delivery_date,
                    'delivery_charge' => number_format($ledger->delivery_charge, 2),
                    'payment_status' => $ledger->payment_status,
                    'delivery_status' => $ledger->delivery_status,
                    'delivery_person_id' => $ledger->delivery_person_id,
                    'customer_name' => trim(($ledger->mealOrder->customer->firstName ?? '') . ' ' . ($ledger->mealOrder->customer->lastName ?? '')),
                    'client_name' => trim(($ledger->client->firstName ?? '') . ' ' . ($ledger->client->lastName ?? '')),
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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

        return view('delivery.pages.meal-order.view');
    }

    public function getMealOrderDetails($delivery_charge_ledger_id)
    {
        try {
            $deliveryLedger = DeliveryChargeLedger::with([
                'mealOrder.customer',
                'client',
                'deliveryPerson',
                'mealType',
                'statusHistories' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])->find($delivery_charge_ledger_id);

            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            $mealOrder = MealOrder::find($deliveryLedger->meal_order_id);
            
            if (!$mealOrder) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.'
                ], 404);
            }

            $shippingAddress = MealShippingAddress::with(['city', 'county', 'country'])
                ->where('meal_order_id', $deliveryLedger->meal_order_id)
                ->first();

            $orderItems = MealOrderItem::with(['product', 'mealType'])
                ->where('meal_order_id', $deliveryLedger->meal_order_id)
                ->where('client_id', $deliveryLedger->client_id)
                ->where('meal_type_id', $deliveryLedger->meal_type_id)
                ->whereDate('meal_date', $deliveryLedger->delivery_date)
                ->get();

            $subtotal = $orderItems->sum('total_price');
            $taxRate = (float) config('services.tax_rate', 0.10);
            $tax = $subtotal * $taxRate;

            $deliveryFee = $deliveryLedger->delivery_charge;
            $total = $subtotal + $tax + $deliveryFee;

            $client   = User::with(['city', 'county', 'country'])->find($deliveryLedger->client_id);
            $customer = User::find($mealOrder->customer_id);
            $mealType = MealType::find($deliveryLedger->meal_type_id);

            $deliveryStatuses = DeliveryChargeLedger::STATUS_LABELS;

            $data = [
                'delivery_info' => [
                    'id' => $deliveryLedger->id,
                    'tracking_number' => $deliveryLedger->order_tracking,
                    'delivery_date' => $deliveryLedger->delivery_date,
                    'delivery_status' => $deliveryLedger->delivery_status,
                    'delivery_status_label' => $deliveryStatuses[$deliveryLedger->delivery_status] ?? 'Unknown',
                    'delivery_charge' => number_format($deliveryLedger->delivery_charge, 2),
                    'distance_km' => $deliveryLedger->distance_km,
                    'distance_category' => $deliveryLedger->distance_category,
                    'payment_status' => $deliveryLedger->payment_status,
                    'payment_date' => $deliveryLedger->payment_date,
                    'payment_notes' => $deliveryLedger->payment_notes,
                    'charge_key' => $deliveryLedger->charge_key,
                    'created_at' => $deliveryLedger->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $deliveryLedger->updated_at->format('Y-m-d H:i:s'),
                ],
                'restaurant_details' => $client ? [
                    'id'        => $client->id,
                    'name'      => $client->firstName . ' ' . $client->lastName,
                    'email'     => $client->email,
                    'mobile'    => $client->mobile,
                    'address1'  => $client->address1,
                    'address2'  => $client->address2,
                    'city'      => $client->city->name   ?? null,
                    'county'    => $client->county->name ?? null,
                    'country'   => $client->country->name ?? null,
                    'zip_code'  => $client->zip_code,
                    'latitude'  => $client->latitude,
                    'longitude' => $client->longitude,
                    'image'     => $client->image,
                ] : null,
                'customer_details' => $customer ? [
                    'id'             => $customer->id,
                    'name'           => $customer->firstName . ' ' . $customer->lastName,
                    'email'          => $customer->email,
                    'mobile'         => $customer->mobile,
                    'image'          => $customer->image,
                    'address1'       => $shippingAddress->address1 ?? null,
                    'address2'       => $shippingAddress->address2 ?? null,
                    'city'           => $shippingAddress->city->name   ?? null,
                    'county'         => $shippingAddress->county->name ?? null,
                    'country'        => $shippingAddress->country->name ?? null,
                    'zip_code'       => $shippingAddress->zip_code ?? null,
                    'latitude'       => $shippingAddress->latitude  ?? null,
                    'longitude'      => $shippingAddress->longitude ?? null,
                    'shipping_name'  => $shippingAddress->name  ?? null,
                    'shipping_phone' => $shippingAddress->phone ?? null,
                    'shipping_email' => $shippingAddress->email ?? null,
                ] : null,
                                'order_summary' => [
                    'order_number' => $mealOrder->order_number,
                    'invoice_no' => $mealOrder->invoice_no,
                    'subtotal' => number_format($subtotal, 2),
                    'tax' => number_format($tax, 2),
                    'delivery_fee' => number_format($deliveryFee, 2),
                    'total' => number_format($total, 2),
                    'meal_type' => $mealType->name ?? 'N/A',
                    'order_date' => $mealOrder->created_at->format('Y-m-d H:i:s'),
                    'delivery_type' => $mealOrder->delivery_type,
                ],
                'order_items' => $orderItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? 'Unknown Product',
                        'product_image' => $item->product->image ?? null,
                        'quantity' => $item->quantity,
                        'unit_price' => number_format($item->unit_price, 2),
                        'total_price' => number_format($item->total_price, 2),
                        'meal_date' => $item->meal_date,
                        'meal_time' => $item->meal_time,
                        'meal_type' => $item->mealType->name ?? 'N/A',
                    ];
                }),
                'status_history' => $deliveryLedger->statusHistories->map(function($history) use ($deliveryStatuses) {
                    return [
                        'id' => $history->id,
                        'delivery_status' => $history->delivery_status,
                        'status_label' => $deliveryStatuses[$history->delivery_status] ?? 'Unknown',
                        'notes' => $history->notes,
                        'updated_by_type' => $history->updated_by_type,
                        'updated_by_label' => $history->updated_by_label,
                        'pick_up_at' => $history->pick_up_at ? $history->pick_up_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $history->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'delivery_person' => $deliveryLedger->deliveryPerson ? [
                    'id' => $deliveryLedger->deliveryPerson->id,
                    'name' => $deliveryLedger->deliveryPerson->firstName . ' ' . $deliveryLedger->deliveryPerson->lastName,
                    'email' => $deliveryLedger->deliveryPerson->email,
                    'mobile' => $deliveryLedger->deliveryPerson->mobile,
                ] : null,
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching order details.'
            ], 500);
        }
    }

    public function checkDeliveryAvailability(Request $request)
    {
        try {
            $email = $request->header('email');
            
            if (!$email) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized! Need to login.'
                ], 400);
            }

            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found.'
                ], 404);
            }

            $request->validate([
                'notification_id' => 'required|exists:notifications,id',
            ]);

            $notification = $user->notifications()->where('id', $request->notification_id)->first();
            
            if (!$notification) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Notification not found.'
                ], 404);
            }

            $notificationData = $notification->data;
            $nestedData = $notificationData['data'] ?? [];
            
            if (!isset($nestedData['delivery_charge_ledger_id'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid notification data - missing delivery charge ledger ID.'
                ], 400);
            }

            $deliveryLedger = DeliveryChargeLedger::find($nestedData['delivery_charge_ledger_id']);
            
            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            $isAvailable = is_null($deliveryLedger->delivery_person_id);
            
            return response()->json([
                'status' => 'success',
                'message' => $isAvailable ? 'Delivery is available' : 'Delivery is already accepted',
                'data' => [
                    'is_available' => $isAvailable,
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'delivery_person_id' => $deliveryLedger->delivery_person_id,
                    'delivery_status' => $deliveryLedger->delivery_status,
                    'order_tracking' => $deliveryLedger->order_tracking,
                    'notification_data' => $nestedData // Return extracted data for frontend
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while checking delivery availability.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function acceptDelivery(Request $request)
    {
        try {
            $email = $request->header('email');
            
            if (!$email) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized! Need to login.'
                ], 400);
            }

            $user = User::where('email', $email)->first();
            
            if (!$user || !$user->isDelivery()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Only delivery persons can accept deliveries.'
                ], 403);
            }

            $request->validate([
                'delivery_charge_ledger_id' => 'required|exists:delivery_charge_ledgers,id'
            ]);

            $deliveryLedger = DeliveryChargeLedger::find($request->delivery_charge_ledger_id);
            
            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            if ($deliveryLedger->delivery_person_id && $deliveryLedger->delivery_person_id != $user->id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This delivery has already been accepted by another delivery person.'
                ], 400);
            }

            if ($deliveryLedger->delivery_person_id == $user->id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have already accepted this delivery.'
                ], 400);
            }

            // Update delivery charge ledger
            $deliveryLedger->delivery_person_id = $user->id;
            $deliveryLedger->delivery_status = 'accept_delivery';
            $deliveryLedger->save();

            // Create status history
            MealDeliveryStatusHistory::create([
                'delivery_charge_ledger_id' => $deliveryLedger->id,
                'delivery_status' => 'accept_delivery',
                'notes' => 'Delivery accepted by ' . $user->firstName . ' ' . $user->lastName,
                'updated_by_id' => $user->id,
                'updated_by_type' => 'delivery_person'
            ]);

            $this->deleteOtherNotifications($deliveryLedger->id, $user->id);

            $user->unreadNotifications()
                ->whereJsonContains('data->data->delivery_charge_ledger_id', (int) $deliveryLedger->id)
                ->get()
                ->each->markAsRead();

            // Notify the client (restaurant/seller) that a rider accepted their
            // delivery. Sent synchronously so the client dashboard popup can pick
            // it up on its next poll without waiting on a queue worker.
            $client = User::find($deliveryLedger->client_id);
            if ($client) {
                $mealOrder = MealOrder::find($deliveryLedger->meal_order_id);
                $client->notify(new DeliveryAcceptedNotification([
                    'meal_order_id'             => $deliveryLedger->meal_order_id,
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'order_tracking'            => $deliveryLedger->order_tracking,
                    'order_number'              => $mealOrder->order_number ?? null,
                    'rider' => [
                        'name'   => trim($user->firstName . ' ' . $user->lastName),
                        'mobile' => $user->mobile,
                        'image'  => $user->image,
                    ],
                ]));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery accepted successfully!',
                'data' => [
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'delivery_person_id' => $user->id,
                    'status' => 'accept_delivery'
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while accepting delivery.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function deleteOtherNotifications($deliveryChargeLedgerId, $currentUserId)
    {
        try {
            $deliveryUsers = User::where('role', 'rider')->get();
            
            foreach ($deliveryUsers as $deliveryUser) {
                // Skip the current user (who accepted the delivery)
                if ($deliveryUser->id == $currentUserId) {
                    continue;
                }
                
                // Get all notifications for this delivery user
                    $notifications = $deliveryUser->notifications()
                        ->where('type', NewDeliveryAvailableNotification::class)
                        ->get();
                
                foreach ($notifications as $notification) {
                    $notificationData = $notification->data;
                    
                    // Check if notification data has delivery_charge_ledger_id
                    if (isset($notificationData['data']['delivery_charge_ledger_id']) && 
                        $notificationData['data']['delivery_charge_ledger_id'] == $deliveryChargeLedgerId) {
                        
                        // Delete this notification
                        $notification->delete();
                    }
                }
            }
            
        } catch (Exception $e) {
            // Log the error but don't throw exception
            \Log::error('Error deleting other notifications: ' . $e->getMessage());
        }
    }

    public function checkDeliveryStatus(Request $request, $deliveryChargeLedgerId)
    {
        try {
            $deliveryPersonId = $request->header('id');
            $deliveryPerson = User::where('id', $deliveryPersonId)->first();
            if (!$deliveryPerson) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized access. Delivery person not found.'
                ], 403);
            }

            $deliveryLedger = DeliveryChargeLedger::find($deliveryChargeLedgerId);
            
            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            if ($deliveryLedger->delivery_person_id != $deliveryPersonId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You are not assigned to this delivery.'
                ], 403);
            }

            $nextStatus = $this->getNextDeliveryStatus($deliveryLedger->delivery_status);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery status retrieved successfully.',
                'data' => [
                    'delivery_status' => $deliveryLedger->delivery_status,
                    'delivery_status_label' => DeliveryChargeLedger::STATUS_LABELS[$deliveryLedger->delivery_status] ?? $deliveryLedger->delivery_status,
                    'next_status' => $nextStatus,
                    'next_status_label' => DeliveryChargeLedger::STATUS_LABELS[$nextStatus] ?? $nextStatus,
                    'order_tracking' => $deliveryLedger->order_tracking,
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'meal_type' => $deliveryLedger->mealType->name ?? null,
                    'delivery_date' => $deliveryLedger->delivery_date,
                    'is_updatable' => in_array($deliveryLedger->delivery_status, [
                        DeliveryChargeLedger::STATUS_READY_FOR_PICKUP,
                        DeliveryChargeLedger::STATUS_PICKED_UP,
                        DeliveryChargeLedger::STATUS_ON_THE_WAY,
                        DeliveryChargeLedger::STATUS_ARRIVED
                    ])
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Error checking delivery status: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while checking delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateDeliveryStatus(Request $request, $deliveryChargeLedgerId)
    {
        try {
            $deliveryPersonId = $request->header('id');
            
            $deliveryPerson = User::where('id', $deliveryPersonId)->first();
            if (!$deliveryPerson) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized access. Delivery person not found.'
                ], 403);
            }

            $request->validate([
                'delivery_status' => 'required|in:picked_up,on_the_way,arrived,delivered',
                'notes' => 'nullable|string|max:500'
            ]);

            $newStatus = $request->delivery_status;
            $notes = $request->notes;

            $deliveryLedger = DeliveryChargeLedger::find($deliveryChargeLedgerId);
            
            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            if ($deliveryLedger->delivery_person_id != $deliveryPersonId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You are not assigned to this delivery.'
                ], 403);
            }

            $validDeliveryTransitions = [
                DeliveryChargeLedger::STATUS_READY_FOR_PICKUP => [DeliveryChargeLedger::STATUS_PICKED_UP],
                DeliveryChargeLedger::STATUS_PICKED_UP => [DeliveryChargeLedger::STATUS_ON_THE_WAY],
                DeliveryChargeLedger::STATUS_ON_THE_WAY => [DeliveryChargeLedger::STATUS_ARRIVED],
                DeliveryChargeLedger::STATUS_ARRIVED => [DeliveryChargeLedger::STATUS_DELIVERED],
            ];

            $currentStatus = $deliveryLedger->delivery_status;
            
            if (!isset($validDeliveryTransitions[$currentStatus]) || 
                !in_array($newStatus, $validDeliveryTransitions[$currentStatus])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Cannot change status from {$currentStatus} to {$newStatus}. " .
                                "Valid next status: " . implode(', ', $validDeliveryTransitions[$currentStatus] ?? [])
                ], 400);
            }

            DB::beginTransaction();

            try {
                $oldStatus = $deliveryLedger->delivery_status;
                $deliveryLedger->delivery_status = $newStatus;
                $now = Carbon::now();
                
                // Check for pickup time validation
                // if ($newStatus === DeliveryChargeLedger::STATUS_PICKED_UP) {
                //     // Get pickup time from status history if available
                //     $pickupHistory = MealDeliveryStatusHistory::where('delivery_charge_ledger_id', $deliveryLedger->id)
                //         ->where('delivery_status', DeliveryChargeLedger::STATUS_READY_FOR_PICKUP)
                //         ->whereNotNull('pick_up_at')
                //         ->latest()
                //         ->first();
                    
                //     if ($pickupHistory && $pickupHistory->pick_up_at) {
                //         // If scheduled pickup time is in the future, wait for it
                //         if ($pickupHistory->pick_up_at->isFuture()) {
                //             return response()->json([
                //                 'status' => 'failed',
                //                 'message' => "Cannot pick up before scheduled time: " . 
                //                             $pickupHistory->pick_up_at->format('Y-m-d H:i:s')
                //             ], 400);
                //         }
                //     }
                // }
                
                if ($newStatus === DeliveryChargeLedger::STATUS_DELIVERED) {
                    $deliveryLedger->payment_status = DeliveryChargeLedger::PAYMENT_DUE;
                }
                
                $deliveryLedger->save();
                $statusHistoryData = [
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'delivery_status' => $newStatus,
                    'notes' => $notes,
                    'updated_by_id' => $deliveryPersonId,
                    'updated_by_type' => MealDeliveryStatusHistory::UPDATED_BY_DELIVERY_PERSON
                ];

                $statusHistory = MealDeliveryStatusHistory::create($statusHistoryData);
                $mealOrder = $deliveryLedger->mealOrder;

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Delivery status updated successfully.',
                    'data' => [
                        'delivery_ledger_id' => $deliveryLedger->id,
                        'order_tracking' => $deliveryLedger->order_tracking,
                        'old_status' => $oldStatus,
                        'old_status_label' => DeliveryChargeLedger::STATUS_LABELS[$oldStatus] ?? $oldStatus,
                        'new_status' => $newStatus,
                        'new_status_label' => DeliveryChargeLedger::STATUS_LABELS[$newStatus] ?? $newStatus,
                        'status_history_id' => $statusHistory->id,
                        'client_id' => $deliveryLedger->client_id,
                        'customer_id' => $deliveryLedger->customer_id,
                        'delivery_person_id' => $deliveryLedger->delivery_person_id,
                        'meal_type' => $deliveryLedger->mealType->name ?? null,
                        'delivery_date' => $deliveryLedger->delivery_date,
                        'order_number' => $mealOrder->order_number ?? null
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
            Log::error('Error updating delivery status (Delivery): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function getNextDeliveryStatus($currentStatus)
    {
        $statusFlow = [
            DeliveryChargeLedger::STATUS_READY_FOR_PICKUP => DeliveryChargeLedger::STATUS_PICKED_UP,
            DeliveryChargeLedger::STATUS_PICKED_UP => DeliveryChargeLedger::STATUS_ON_THE_WAY,
            DeliveryChargeLedger::STATUS_ON_THE_WAY => DeliveryChargeLedger::STATUS_ARRIVED,
            DeliveryChargeLedger::STATUS_ARRIVED => DeliveryChargeLedger::STATUS_DELIVERED,
        ];

        return $statusFlow[$currentStatus] ?? null;
    }


}