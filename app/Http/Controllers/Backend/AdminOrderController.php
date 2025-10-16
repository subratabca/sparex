<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Notifications\Order\OrderStatusUpdateNotification;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Order;
use App\Models\ClientOrder;
use App\Models\StockMovement;
use Exception;
use PDF;

class AdminOrderController extends Controller
{
    public function orderPage()
    {
        return view('backend.pages.order.order-list');
    }

    public function getOrders(Request $request)
    {
        try {
            $orders = Order::with('customer')->latest()->get();

            // if ($orders->isEmpty()) {
            //     ActivityLogger::log('retrieve_order_failed', 'No orders found.', $request, 'orders');
            //     return response()->json([
            //         'status' => 'failed',
            //         'message' => 'No orders found.'
            //     ], 404);
            // }

            ActivityLogger::log('retrieve_order_success', 'Orders retrieved successfully.', $request, 'orders');
            return response()->json([
                'status' => 'success',
                'data' => $orders
            ], 200); 

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_order_failed', 'System error: ' . $e->getMessage(), $request, 'orders');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving orders',
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
            }
        }

        return view('backend.pages.order.order-details');
    }

    public function getOrderDetails(Request $request, $order_id)
    {
        try {
            $order = Order::with([
                'customer',
                'orderItems.client',
                'orderItems.product', 
                'orderItems.variant', 
                'orderItems.product.category', 
                'orderItems.product.brand', 
                'shippingAddress.country',
                'shippingAddress.county',
                'shippingAddress.city',
                'clientOrders.client'
            ])->where('id', $order_id)->first();

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order details not found',
                ], 404);
            }

            $customer_id = $request->header('id');
            $customer = User::findOrFail($customer_id);
            if ($customer) {
                $notification = $customer->notifications()
                    ->where('notifiable_id', $customer_id)
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

    private function deleteImagesFromHTML($htmlContent)
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $htmlContent, $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $imageUrl) {
                $imagePath = ltrim(parse_url($imageUrl, PHP_URL_PATH), '/');
                $fullImagePath = public_path($imagePath);
                if (File::exists($fullImagePath)) {
                    File::delete($fullImagePath);
                }
            }
        }
    }

    public function delete(Request $request)
    {
        try {
            $order_id = $request->input('order_id');
            $order = Order::with([
                'complaints.conversations',
                'orderItems',
                'clientOrders',
                'shippingAddress'
            ])->findOrFail($order_id);

            if (!$order) {
                ActivityLogger::log(
                    'order_delete_failed',
                    'Order not found with the given ID.',
                    $request,
                    'orders'
                );

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found.',
                ], 404);
            }

            DB::beginTransaction();

            foreach ($order->complaints as $complaint) {
                foreach ($complaint->conversations as $conversation) {
                    if (!empty($conversation->reply_message)) {
                        $this->deleteImagesFromHTML($conversation->reply_message);
                    }
                    $conversation->delete();
                }

                if (!empty($complaint->message)) {
                    $this->deleteImagesFromHTML($complaint->message);
                }

                $complaint->delete();
            }

            foreach ($order->orderItems as $item) {
                $item->delete();
            }

            foreach ($order->clientOrders as $clientOrder) {
                $clientOrder->delete();
            }

            if ($order->shippingAddress) {
                $order->shippingAddress->delete();
            }

            $order->delete();

            ActivityLogger::log(
                'order_delete_success',
                'Order, related complaints, conversations, order items, client orders, and shipping address deleted successfully.',
                $request,
                'orders'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order and all related data deleted successfully.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            ActivityLogger::log(
                'order_delete_failed',
                'An error occurred while deleting the order: ' . $e->getMessage(),
                $request,
                'orders'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function invoiceDownload(Request $request, $order_id)
    {
        $order = Order::with([
            'customer',
            'orderItems.client',
            'orderItems.product', 
            'orderItems.variant', 
            'orderItems.product.category', 
            'orderItems.product.brand', 
            'shippingAddress.country',
            'shippingAddress.county',
            'shippingAddress.city'
        ])->where('id', $order_id)->first();
        //dd($order);

        $pdf = PDF::loadView('backend.pages.order.invoice',compact('order'))->setPaper('a4')->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
        ]);
        //return $pdf->download('invoice.pdf');
        return $pdf->download('invoice-'.$order->invoice_no.'.pdf');
    }

}

