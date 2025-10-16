<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\FoodRequestNotification;
use Illuminate\Validation\ValidationException; 
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Order;
use Exception;

class OrderController extends Controller
{

    public function orderPage()
    {
        return view('frontend.pages.order.order-page');
    }

    public function getOrders(Request $request)
    {
        try {
            $customer_id = $request->header('id');
            $orders = Order::with(['orderItems'])
                ->where('customer_id', $customer_id)
                ->latest()
                ->get();

            ActivityLogger::log('retrieve_order_success', 'Orders retrieved successfully.', $request, 'orders');
            return response()->json([
                'status' => 'success',
                'data' => $orders
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_order_failed', 'System error: ' . $e->getMessage(), $request, 'orders');
            return response()->json([
                'status' => 'failed',
                'message' => 'Order information not found',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderDetailsPage(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', $email)->first();

        $notification_id = $request->query('notification_id');
        
        if ($notification_id) {
            $notification = $user->notifications()->where('id', $notification_id)->first();

            if ($notification && is_null($notification->read_at)) {
                $notification->markAsRead();

                $originalNotificationId = $notification->data['original_notification_id'] ?? null;
                if ($originalNotificationId) {
                    $originalNotification = $user->notifications()->where('id', $originalNotificationId)->first();
                    if ($originalNotification && is_null($originalNotification->read_at)) {
                        $originalNotification->markAsRead();
                    }
                }
            }
        }

        return view('frontend.pages.order.order-details-page');
    }

    public function getOrderDetails(Request $request, $order_id)
    {
        try {
            $order = Order::with([
                'orderItems.client',
                'orderItems.product', 
                'orderItems.variant', 
                'orderItems.product.category', 
                'orderItems.product.brand', 
                'shippingAddress',
                'complaints',
                'clientOrders.client'
            ])->where('id', $order_id)->first();

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found',
                ], 404);
            }

            $admin_id = $request->header('id');
            $admin = User::findOrFail($admin_id);
            if ($admin) {
                $notification = $admin->notifications()
                    ->where('notifiable_id', $admin_id)
                    ->where('data->order_id', $order_id)
                    ->first();

                if ($notification && is_null($notification->read_at)) {
                    $notification->markAsRead();
                }
            }
            
            ActivityLogger::log('view_order_details_success', 'Order details accessed successfully.', $request, 'orders');
            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'order_items' => $order->orderItems,
                    'client_orders' => $order->clientOrders 
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving order information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}


