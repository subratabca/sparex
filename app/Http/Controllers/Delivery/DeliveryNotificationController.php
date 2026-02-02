<?php
namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\DeliveryChargeLedger;
use Exception;

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

    public function showNotificationDetails(Request $request, $notificationId)
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

            // Get the notification
            $notification = $user->notifications()->where('id', $notificationId)->first();
            
            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found.'
                ], 404);
            }

            // Mark notification as read
            $notification->markAsRead();

            // Get the notification data
            $notificationData = $notification->data;
            
            // Extract inner data from notification
            $innerData = $notificationData['data'] ?? [];
            
            // If there's a delivery_charge_ledger_id, get current status from database
            if (isset($innerData['delivery_charge_ledger_id'])) {
                $deliveryChargeLedger = DeliveryChargeLedger::find($innerData['delivery_charge_ledger_id']);
                if ($deliveryChargeLedger) {
                    // Update the inner data with current status from database
                    $innerData['delivery_person_id'] = $deliveryChargeLedger->delivery_person_id;
                    $innerData['delivery_status'] = $deliveryChargeLedger->delivery_status;
                    $innerData['payment_status'] = $deliveryChargeLedger->payment_status;
                    $innerData['order_tracking'] = $deliveryChargeLedger->order_tracking;
                    
                    // Add delivery person info if assigned
                    if ($deliveryChargeLedger->delivery_person_id) {
                        $innerData['delivery_person'] = [
                            'id' => $deliveryChargeLedger->delivery_person_id,
                            'name' => $deliveryChargeLedger->deliveryPerson ? 
                                $deliveryChargeLedger->deliveryPerson->firstName . ' ' . $deliveryChargeLedger->deliveryPerson->lastName : null
                        ];
                    }
                }
            }

            // Prepare response data
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

            // Get the notification
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
            
            // Update delivery charge ledger
            $deliveryChargeLedger = DeliveryChargeLedger::find($deliveryChargeLedgerId);
            
            if (!$deliveryChargeLedger) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            // Check if already accepted by someone else
            if ($deliveryChargeLedger->delivery_person_id && $deliveryChargeLedger->delivery_person_id != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This delivery has already been accepted by another delivery person.'
                ], 400);
            }

            // Update delivery charge ledger
            $deliveryChargeLedger->delivery_person_id = $user->id;
            $deliveryChargeLedger->delivery_status = 'accept_order';
            $deliveryChargeLedger->save();

            // Create status history
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

      } catch (\Exception $e) {
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

}