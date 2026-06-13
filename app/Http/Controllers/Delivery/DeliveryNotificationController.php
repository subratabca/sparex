<?php
namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\DeliveryChargeLedger;
use App\Models\MealShippingAddress;
use App\Models\MealOrderItem;
use App\Models\MealDeliveryStatusHistory;
use App\Models\MealOrder;
use Exception;
use Carbon\Carbon;

class DeliveryNotificationController extends Controller
{
    public function index()
    {
        return view('delivery.pages.notification.index');
    }

    public function limitedNotificationList(Request $request) 
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

            if ($user) {
                $unreadNotifications = $user->unreadNotifications()
                ->latest()
                ->take(4)
                ->get()
                ->map(function ($notification) {
                    if (is_string($notification->data)) {
                        $notification->data = json_decode($notification->data, true);
                    }
                    return $notification;
                });

                $unreadCount = $unreadNotifications->count();

                $readNotifications = $user->readNotifications()
                ->latest()
                ->take(4 - $unreadCount)
                ->get()
                ->map(function ($notification) {
                    if (is_string($notification->data)) {
                        $notification->data = json_decode($notification->data, true);
                    }
                    return $notification;
                });

                return response()->json([
                    'status' => 'success',
                    'message' => 'Request Successful',
                    'data' => $user,
                    'unreadNotifications' => $unreadNotifications,
                    'readNotifications' => $readNotifications,
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getNotificationList(Request $request) 
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

            if ($user) {
                $unreadNotifications = $user->unreadNotifications()->latest()->get();
                $unreadCount = $unreadNotifications->count();
                $readNotifications = $user->readNotifications()->latest()->get();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Request Successful',
                    'data' => $user,
                    'unreadNotifications' => $unreadNotifications,
                    'readNotifications' => $readNotifications,
                ], 200);
            } 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function view()
    {
        return view('delivery.pages.notification.view');
    }

    public function getNotificationDetails(Request $request, $notificationId)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized! Need to login.'
                ], 401);
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.'
                ], 404);
            }

            $notification = $user->notifications()->where('id', $notificationId)->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found.'
                ], 404);
            }

            $notification->markAsRead();
            $notificationData = $notification->data;
            $innerData = $notificationData['data'] ?? [];

            if (isset($innerData['delivery_charge_ledger_id'])) {
                $deliveryChargeLedger = DeliveryChargeLedger::find($innerData['delivery_charge_ledger_id']);
                if ($deliveryChargeLedger) {
                    $innerData['delivery_person_id'] = $deliveryChargeLedger->delivery_person_id;
                    $innerData['delivery_status'] = $deliveryChargeLedger->delivery_status;
                    $innerData['payment_status'] = $deliveryChargeLedger->payment_status;
                    $innerData['order_tracking'] = $deliveryChargeLedger->order_tracking;

                    if ($deliveryChargeLedger->delivery_person_id) {
                        $innerData['delivery_person'] = [
                            'id' => $deliveryChargeLedger->delivery_person_id,
                            'name' => $deliveryChargeLedger->deliveryPerson ?
                            $deliveryChargeLedger->deliveryPerson->firstName . ' ' . $deliveryChargeLedger->deliveryPerson->lastName : null
                        ];
                    }

                    // Enrich order items with product name + image (works for old & new notifications)
                    $orderItems = MealOrderItem::with('product')
                        ->where('meal_order_id', $deliveryChargeLedger->meal_order_id)
                        ->where('client_id', $deliveryChargeLedger->client_id)
                        ->where('meal_type_id', $deliveryChargeLedger->meal_type_id)
                        ->whereDate('meal_date', $deliveryChargeLedger->delivery_date)
                        ->get();

                    if ($orderItems->isNotEmpty()) {
                        $innerData['items'] = $orderItems->map(function ($item) {
                            return [
                                'product_name'  => $item->product->name ?? 'N/A',
                                'product_image' => $item->product->image ?? null,
                                'quantity'      => $item->quantity,
                                'meal_time'     => $item->meal_time,
                            ];
                        })->values()->all();
                    }

                    // Scheduled pickup time set by the client when marking "ready for pickup"
                    $readyStatus = MealDeliveryStatusHistory::where('delivery_charge_ledger_id', $deliveryChargeLedger->id)
                        ->where('delivery_status', 'ready_for_pickup')
                        ->latest()
                        ->first();
                    // Format the wall-clock time directly (no timezone conversion) so it
                    // matches exactly what the client selected, e.g. "06-June-2026 10:15 AM"
                    $innerData['pickup_time'] = ($readyStatus && $readyStatus->pick_up_at)
                        ? $readyStatus->pick_up_at->format('d-F-Y g:i A')
                        : null;

                    // MealOrder order_number for the top "Delivery Request" section
                    $mealOrder = MealOrder::find($deliveryChargeLedger->meal_order_id);
                    $innerData['order_number'] = $mealOrder->order_number ?? null;
                }
            }

            $responseData = [
                'title' => $notificationData['title'] ?? null,
                'message' => $notificationData['message'] ?? null,
                'type' => $notificationData['type'] ?? null,
                'action_url' => $notificationData['action_url'] ?? null,
                'notification_time' => $notificationData['notification_time'] ?? null,
                'data' => $innerData,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'notification_id' => $notification->id
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Notification details fetched successfully',
                'data' => $responseData
            ]);

        } catch (Exception $e) {
            Log::error('Error showing notification details: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while loading notification details.'
            ], 500);
        }
    }

    public function acceptNotification(Request $request, $notificationId)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized! Need to login.'
                ], 401);
            }

            $user = User::where('email', $email)->first();

            if (!$user || !$user->isDelivery()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only delivery persons can accept deliveries.'
                ], 403);
            }

            $notification = $user->notifications()->where('id', $notificationId)->first();
            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found.'
                ], 404);
            }

            $notificationData = $notification->data;
            $innerData = $notificationData['data'] ?? [];

            if (!isset($innerData['delivery_charge_ledger_id'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid notification data.'
                ], 400);
            }

            $deliveryChargeLedgerId = $innerData['delivery_charge_ledger_id'];
            $deliveryChargeLedger = DeliveryChargeLedger::find($deliveryChargeLedgerId);
            if (!$deliveryChargeLedger) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            if ($deliveryChargeLedger->delivery_person_id && $deliveryChargeLedger->delivery_person_id != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This delivery has already been accepted by another delivery person.'
                ], 400);
            }

            $deliveryChargeLedger->delivery_person_id = $user->id;
            $deliveryChargeLedger->delivery_status = 'accept_order';
            $deliveryChargeLedger->save();

            MealDeliveryStatusHistory::create([
                'delivery_charge_ledger_id' => $deliveryChargeLedgerId,
                'delivery_status' => 'accept_order',
                'notes' => 'Delivery accepted by ' . $user->firstName . ' ' . $user->lastName,
                'updated_by_id' => $user->id,
                'updated_by_type' => 'delivery_person'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery accepted successfully!',
                'data' => [
                    'delivery_charge_ledger_id' => $deliveryChargeLedgerId,
                    'delivery_person_id' => $user->id,
                    'status' => 'accept_order'
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error accepting notification: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while accepting delivery.'
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $email = $request->header('email');
            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email header is missing.'
                ], 400);
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.'
                ], 404);
            }

            $user->unreadNotifications->markAsRead();
            $unreadCount = $user->unreadNotifications->count();

            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read.',
                'unreadCount' => $unreadCount,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while marking notifications as read.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteNotification(Request $request, $notificationId)
    {
        try {
            $email = $request->header('email');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found.',
                ], 404);
            }

            $notification = $user->notifications()->find($notificationId);

            if ($notification) {
                $notification->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Notification deleted successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Notification not found.',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while deleting the notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPendingDeliveryRequests(Request $request)
    {
        try {
            $email = $request->header('email');
            if (!$email) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized! Need to login.'], 401);
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json(['status' => 'failed', 'message' => 'User not found.'], 404);
            }

            // Available deliveries = not yet claimed by any rider
            $ledgers = DeliveryChargeLedger::with([
                    'client', 'client.city', 'client.county', 'client.country',
                    'mealType:id,name',
                ])
                ->whereNull('delivery_person_id')
                ->where('delivery_status', 'accept_order')   // only client-accepted, not-yet-claimed
                ->latest()
                ->get();

            $pending = [];

            foreach ($ledgers as $ledger) {
                $client   = $ledger->client;
                $shipping = MealShippingAddress::with(['city', 'county', 'country'])
                    ->where('meal_order_id', $ledger->meal_order_id)
                    ->first();

                $pending[] = [
                    'delivery_charge_ledger_id' => $ledger->id,
                    'order_tracking'  => $ledger->order_tracking,
                    'meal_type'       => $ledger->mealType->name ?? 'N/A',
                    'delivery_date'   => $ledger->delivery_date
                        ? Carbon::parse($ledger->delivery_date)->format('d M Y') : 'N/A',
                    'delivery_charge' => (float) $ledger->delivery_charge,
                    'distance_km'     => $ledger->distance_km,
                    'restaurant' => [
                        'name'    => $client ? trim($client->firstName . ' ' . $client->lastName) : 'N/A',
                        'mobile'  => $client->mobile ?? null,
                        'latitude'  => $client->latitude  ?? null,
                        'longitude' => $client->longitude ?? null,
                        'address' => $client ? $this->formatAddressFromData([
                            'address1' => $client->address1,
                            'address2' => $client->address2,
                            'city'     => $client->city->name   ?? null,
                            'county'   => $client->county->name ?? null,
                            'country'  => $client->country->name ?? null,
                            'zip_code' => $client->zip_code,
                        ]) : '',
                    ],
                    'shipping' => [
                        'name'    => $shipping->name  ?? null,
                        'phone'   => $shipping->phone ?? null,
                        'latitude'  => $shipping->latitude  ?? null,
                        'longitude' => $shipping->longitude ?? null,
                        'address' => $shipping ? $this->formatAddressFromData([
                            'address1' => $shipping->address1,
                            'address2' => $shipping->address2,
                            'city'     => $shipping->city->name   ?? null,
                            'county'   => $shipping->county->name ?? null,
                            'country'  => $shipping->country->name ?? null,
                            'zip_code' => $shipping->zip_code,
                        ]) : '',
                    ],
                ];
            }

            return response()->json([
                'status' => 'success',
                'count'  => count($pending),
                'data'   => $pending,
            ], 200);

        } catch (Exception $e) {
            \Log::error('getPendingDeliveryRequests: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    private function formatAddressFromData(array $a): string
    {
        $parts = array_filter([
            $a['address1'] ?? null,
            $a['address2'] ?? null,
            $a['city']     ?? null,
            $a['county']   ?? null,
            $a['country']  ?? null,
            $a['zip_code'] ?? null,
        ]);
        return implode(', ', $parts);
    }
}