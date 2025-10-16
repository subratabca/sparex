<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Notifications\Order\OrderStatusUpdateNotification;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\StockMovement;
use App\Models\ClientOrderAction;
use App\Services\OrderBillingService;
use Exception;
use PDF;

class ClientOrderController extends Controller
{
    public function orderPage()
    {
        return view('client.pages.order.order-list');
    }

    public function getOrders(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $orders = ClientOrder::with('order.customer')->where('client_id', $client_id)->latest()->get();

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

        return view('client.pages.order.order-details');
    }
    
    public function getOrderDetails(Request $request, $order_id)
    {
        try {
            $client_id = $request->header('id');
            $order = Order::with([
                'customer',
                'orderItems' => function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                },
                'orderItems.client',
                'orderItems.product',
                'orderItems.variant',
                'orderItems.product.category',
                'orderItems.product.brand',
                'shippingAddress.country',
                'shippingAddress.county',
                'shippingAddress.city',
                'clientOrders' => function($query) use ($client_id,$order_id) {
                    $query->where('client_id', $client_id)->where('order_id', $order_id);
                },
                'clientOrders.client' 
            ])->where('id', $order_id)->first();

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order details not found',
                ], 404);
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

    private function recalcOrderStatuses(Order $order)
    {
        $order->loadMissing(['orderItems', 'clientOrders']);

        $statuses = $order->orderItems->pluck('status')->unique()->toArray();

        if (count($statuses) === 1) {
            $map = [
                'pending'   => 'pending',
                'approved'  => 'approved',
                'delivered' => 'delivered',
                'canceled'  => 'canceled'
            ];
            $orderStatus = $map[$statuses[0]] ?? 'pending';
        } else {
            if (in_array('delivered', $statuses)) {
                $orderStatus = 'partially_delivered';
            } elseif (in_array('approved', $statuses) && (in_array('pending', $statuses) || in_array('canceled', $statuses))) {
                $orderStatus = 'partially_approved';
            } else {
                $orderStatus = 'pending';
            }
        }
        $order->update(['status' => $orderStatus]);

        foreach ($order->clientOrders as $clientOrder) {
            $clientStatuses = $order->orderItems()
                ->where('client_id', $clientOrder->client_id)
                ->pluck('status')
                ->unique()
                ->toArray();

            if (count($clientStatuses) === 1) {
                $clientStatus = $clientStatuses[0];
            } else {
                if (in_array('delivered', $clientStatuses)) {
                    $clientStatus = 'partially_delivered';
                } elseif (in_array('approved', $clientStatuses) && (in_array('pending', $clientStatuses) || in_array('canceled', $clientStatuses))) {
                    $clientStatus = 'partially_approved';
                } else {
                    $clientStatus = 'pending';
                }
            }

            $clientOrder->update(['status' => $clientStatus]);
        }
    }

    public function orderApproved(Request $request)
    {
        DB::beginTransaction();

        try {
            $itemId    = $request->input('item_id'); 
            $client_id = $request->header('id');

            $item = OrderItem::with(['order.customer', 'client', 'product', 'variant'])
                ->where('id', $itemId)
                ->where('client_id', $client_id)
                ->firstOrFail();

            $item->update([
                'status'       => 'approved',
                'approve_date' => now()->format('Y-m-d'),
                'approve_time' => now()->format('H:i:s'),
            ]);

            $order       = $item->order;
            $clientOrder = $item->clientOrder; 

            $this->recalcOrderStatuses($order);

            $order->load([
                'customer',
                'orderItems.client',
                'orderItems.product',
                'orderItems.variant',
                'orderItems.product.category',
                'orderItems.product.brand'
            ]);
            if ($clientOrder) {
                $clientOrder->load('client');
            }

            if ($order->customer) {
                $order->customer->notify(new OrderStatusUpdateNotification($order, $clientOrder));
            }

            // Notify admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new OrderStatusUpdateNotification($order, $clientOrder));
            }

            ActivityLogger::log(
                'order_item_approved_success',
                "Client #{$client_id} approved item #{$itemId} of order #{$order->id}",
                $request,
                'order_items'
            );

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Order item approved successfully.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            ActivityLogger::log(
                'order_item_approved_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'order_items'
            );

            return response()->json([
                'status'  => 'failed',
                'message' => 'Order item approval failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function orderCanceled(Request $request)
    {
        DB::beginTransaction();

        try {
            $itemId       = $request->input('item_id');
            $actionReason = $request->input('action_reason');
            $client_id    = $request->header('id');

            $item = OrderItem::with(['order', 'product', 'variant'])
                ->where('id', $itemId)
                ->where('client_id', $client_id)
                ->where('status', 'pending')
                ->firstOrFail();

            $order = $item->order;
            $clientOrder = ClientOrder::where('order_id', $order->id)
                ->where('client_id', $client_id)
                ->firstOrFail();

            $item->update([
                'status'      => 'canceled',
                'cancel_date' => now()->format('Y-m-d'),
                'cancel_time' => now()->format('H:i:s'),
            ]);

            if ($item->product_variant_id) {
                $variant = $item->variant;
                $variant->increment('current_stock', $item->quantity);
                $item->product->update([
                    'current_stock' => $item->product->variants()->sum('current_stock')
                ]);
            } else {
                $item->product->increment('current_stock', $item->quantity);
            }

            StockMovement::create([
                'product_id'    => $item->product_id,
                'client_id'     => $item->client_id,
                'order_id'      => $order->id,
                'variant_id'    => $item->product_variant_id,
                'quantity'      => $item->quantity,
                'movement_type' => StockMovement::TYPE_CANCELED,
                'notes'         => 'Order item canceled'
            ]);

            $refund = OrderBillingService::calculateRefund($item, $clientOrder);

            ClientOrderAction::create([
                'order_id'        => $item->order_id,
                'client_order_id' => $clientOrder->id,
                'order_item_id'   => $item->id,
                'client_id'       => $item->client_id,
                'quantity'        => $item->quantity,
                'subtotal'        => $refund['subtotal'],
                'coupon_discount' => $refund['coupon_discount'],
                'tax'             => $refund['tax'],
                'delivery_fee'    => $refund['delivery_fee'],
                'total_refund'    => $refund['total_refund'],
                'payment_status'  => 'due',
                'action_reason'   => $actionReason,
                'action_type'     => 'canceled',
                'action_by_id'    => $client_id,
                'action_by_role'  => 'client',
            ]);

            OrderBillingService::recalcBilling($order, $client_id);

            if (method_exists($this, 'recalcOrderStatuses')) {
                $this->recalcOrderStatuses($order);
            }

            ActivityLogger::log(
                'order_canceled_success',
                "Client #{$client_id} canceled item #{$itemId} of order #{$order->id}",
                $request,
                'client_orders'
            );

            DB::commit();

            $order->refresh();
            $clientOrder->refresh();
            $order->load('customer');

            try {
                if ($order->customer) {
                    $order->customer->notify(new OrderStatusUpdateNotification($order, $clientOrder));
                }

                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new OrderStatusUpdateNotification($order, $clientOrder));
                }
            } catch (Exception $e) {
                ActivityLogger::log(
                    'order_notification_failed',
                    'Failed to send order notifications: ' . $e->getMessage(),
                    $request,
                    'client_orders'
                );
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Order item canceled, refund saved, and billing updated successfully.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            ActivityLogger::log(
                'order_canceled_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'client_orders'
            );

            return response()->json([
                'status'  => 'failed',
                'message' => 'Order cancellation failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function orderDelivered(Request $request)
    {
        DB::beginTransaction();

        try {
            $orderId   = $request->input('order_id');
            $client_id = $request->header('id'); 

            $updatedCount = OrderItem::where('order_id', $orderId)
                ->where('client_id', $client_id)
                ->where('status', 'approved')
                ->update([
                    'status'        => 'delivered',
                    'delivery_date' => now()->format('Y-m-d'),
                    'delivery_time' => now()->format('H:i:s'),
                ]);

            if ($updatedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'No approved items found to deliver.'
                ], 404);
            }

            $order = Order::with('clientOrders.orderItems')->findOrFail($orderId);

            $this->recalcOrderStatuses($order);

            ActivityLogger::log(
                'order_items_delivered_success',
                "Client #{$client_id} delivered {$updatedCount} item(s) in order #{$orderId}",
                $request,
                'order_items'
            );

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "{$updatedCount} item(s) delivered successfully."
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            ActivityLogger::log(
                'order_items_delivered_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'order_items'
            );

            return response()->json([
                'status'  => 'failed',
                'message' => 'Order delivery failed',
                'error'   => $e->getMessage()
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

        $pdf = PDF::loadView('backend.pages.order.invoice',compact('order'))->setPaper('a4')->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
        ]);

        return $pdf->download('invoice-'.$order->invoice_no.'.pdf');
    }

}