<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Food;

class AdminNotificationController extends Controller
{
  public function NotificationPage()
  {
      return view('backend.pages.notification.notification-list');
  }

  public function LimitedNotificationList(Request $request) 
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
              $unreadNotifications = $user->unreadNotifications()->latest()->take(4)->get();
              $unreadCount = $unreadNotifications->count();
              $readNotifications = $user->readNotifications()->latest()->take(4 - $unreadCount)->get();

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

    /**
     * Poll endpoint for the admin layout: returns the admin's total unread
     * count (so the bell badge updates live, no page refresh) plus unread
     * "new meal order" notifications enriched with ALL clients' food items
     * (grouped per client, minimum required info) for the popup.
     */
    public function getNewMealOrders(Request $request)
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

            $unreadCount = $user->unreadNotifications()->count();

            $orders = $user->unreadNotifications()
                ->where('type', \App\Notifications\MealOrder\NewMealOrderNotification::class)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($n) {
                    $d = is_string($n->data) ? json_decode($n->data, true) : $n->data;
                    $mealOrderId = $d['meal_order_id'] ?? null;
                    if (!$mealOrderId) return null;

                    $order = \App\Models\MealOrder::with('customer:id,firstName,lastName')->find($mealOrderId);
                    if (!$order) return null;

                    $items = \App\Models\MealOrderItem::with([
                            'client:id,firstName,lastName',
                            'mealType:id,name',
                            'product:id,name',
                        ])
                        ->where('meal_order_id', $mealOrderId)
                        ->get();
                    if ($items->isEmpty()) return null;

                    // Group all items by client — admin sees every client's items (minimum info)
                    $clients = $items->groupBy('client_id')->map(function ($clientItems) {
                        $first = $clientItems->first();
                        return [
                            'client_name' => $first->client
                                ? trim($first->client->firstName . ' ' . $first->client->lastName) : 'Client',
                            'item_count'  => (int) $clientItems->sum('quantity'),
                            'items'       => $clientItems->map(function ($it) {
                                return [
                                    'product_name' => $it->product->name ?? 'N/A',
                                    'meal_type'    => $it->mealType->name ?? null,
                                    'meal_date'    => $it->meal_date ? \Carbon\Carbon::parse($it->meal_date)->format('d M Y') : null,
                                    'quantity'     => (int) $it->quantity,
                                ];
                            })->values(),
                        ];
                    })->values();

                    return [
                        'notification_id' => $n->id,
                        'meal_order_id'   => $mealOrderId,
                        'order_number'    => $order->order_number,
                        'customer_name'   => $order->customer
                            ? trim($order->customer->firstName . ' ' . $order->customer->lastName) : 'Customer',
                        'client_count'    => $clients->count(),
                        'item_count'      => (int) $items->sum('quantity'),
                        'total_amount'    => (float) $order->payable_amount,
                        'clients'         => $clients,
                        'created_at'      => optional($n->created_at)->toDateTimeString(),
                    ];
                })
                ->filter()
                ->values();

            return response()->json([
                'status'       => 'success',
                'unread_count' => $unreadCount,
                'data'         => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function NotificationList(Request $request)
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

            // Get all notifications for the table
            $unreadNotifications = $user->unreadNotifications()
                ->latest()
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'data' => $notification->data,
                        'type' => $notification->type,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toDateTimeString(),
                        'updated_at' => $notification->updated_at->toDateTimeString(),
                    ];
                })->toArray();

            $readNotifications = $user->readNotifications()
                ->latest()
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'data' => $notification->data,
                        'type' => $notification->type,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toDateTimeString(),
                        'updated_at' => $notification->updated_at->toDateTimeString(),
                    ];
                })->toArray();

            return response()->json([
                'status' => 'success',
                'message' => 'Notifications retrieved successfully',
                'unreadNotifications' => $unreadNotifications,
                'readNotifications' => $readNotifications,
            ], 200);

        } catch (Exception $e) {
            \Log::error('Notification List Info Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

  public function MarkAsRead(Request $request)
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