<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Food;

class ClientNotificationController extends Controller
{
  public function NotificationPage(){
      return view('client.pages.notification.notification-list');
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
   * Lightweight poll endpoint for the client dashboard popup.
   * Returns the client's unread "rider accepted your delivery" notifications
   * (with rider info) so the layout can pop them in near real-time.
   */
  public function getDeliveryAcceptances(Request $request)
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

          $acceptances = $user->unreadNotifications()
              ->where('type', \App\Notifications\Client\DeliveryAcceptedNotification::class)
              ->latest()
              ->get()
              ->map(function ($n) {
                  $d = $n->data;
                  return [
                      'notification_id'           => $n->id,
                      'meal_order_id'             => $d['meal_order_id'] ?? null,
                      'delivery_charge_ledger_id' => $d['delivery_charge_ledger_id'] ?? null,
                      'order_tracking'            => $d['order_tracking'] ?? null,
                      'order_number'              => $d['order_number'] ?? null,
                      'rider'                     => $d['rider'] ?? null,
                      'created_at'                => optional($n->created_at)->toDateTimeString(),
                  ];
              })->values();

          // Total unread count so the bell badge can update live (no page refresh).
          $unreadCount = $user->unreadNotifications()->count();

          return response()->json([
              'status'       => 'success',
              'data'         => $acceptances,
              'unread_count' => $unreadCount,
          ], 200);
      } catch (\Exception $e) {
          return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
      }
  }

  /**
   * Poll endpoint for the client layout: returns this client's unread
   * "new meal order" notifications, each enriched with ONLY this client's
   * food items for that order, so a popup can show in near real-time when a
   * customer places an order containing this client's products.
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

          $orders = $user->unreadNotifications()
              ->where('type', \App\Notifications\MealOrder\NewMealOrderNotification::class)
              ->latest()
              ->take(5)
              ->get()
              ->map(function ($n) use ($user) {
                  $d = is_string($n->data) ? json_decode($n->data, true) : $n->data;
                  $mealOrderId = $d['meal_order_id'] ?? null;
                  if (!$mealOrderId) return null;

                  $order = \App\Models\MealOrder::with('customer:id,firstName,lastName')->find($mealOrderId);
                  if (!$order) return null;

                  // Only THIS client's items in the order
                  $items = \App\Models\MealOrderItem::with(['product:id,name,image', 'mealType:id,name'])
                      ->where('meal_order_id', $mealOrderId)
                      ->where('client_id', $user->id)
                      ->get();
                  if ($items->isEmpty()) return null;

                  return [
                      'notification_id' => $n->id,
                      'meal_order_id'   => $mealOrderId,
                      'order_number'    => $order->order_number,
                      'customer_name'   => $order->customer
                          ? trim($order->customer->firstName . ' ' . $order->customer->lastName) : 'Customer',
                      'item_count'      => (int) $items->sum('quantity'),
                      'total_amount'    => (float) $items->sum('total_price'),
                      'items'           => $items->map(function ($it) {
                          return [
                              'product_name'  => $it->product->name ?? 'N/A',
                              'product_image' => $it->product->image ?? null,
                              'meal_type'     => $it->mealType->name ?? null,
                              'meal_date'     => $it->meal_date ? \Carbon\Carbon::parse($it->meal_date)->format('d M Y') : null,
                              'meal_time'     => $it->meal_time,
                              'quantity'      => (int) $it->quantity,
                          ];
                      })->values(),
                      'created_at'      => optional($n->created_at)->toDateTimeString(),
                  ];
              })
              ->filter()
              ->values();

          return response()->json(['status' => 'success', 'data' => $orders], 200);
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