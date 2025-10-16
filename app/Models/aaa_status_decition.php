All Pending → pending

All Approved → approved

All Delivered → delivered

All Canceled → canceled

Some Approved + Some Pending → partially_approved

Some Approved + Some Canceled → partially_approved 

Some Delivered + Some Approved/Pending/Canceled → partially_delivered



As per above decition update status of Order and OrderItem model is working fine in public function orderApproved(Request $request),public function orderCanceled(Request $request) and public function orderDelivered(Request $request) method. In ClientOrder model it is not working properly.In ClientOrder model it will match with client_id and order_id with order_items model each item than update status.



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
use App\Models\ClientOrderReturn;
use App\Services\OrderBillingService;
use Exception;

class ClientOrderController extends Controller
{
    private function recalcOrderStatuses(Order $order)
    {
        $order->loadMissing(['orderItems', 'clientOrders.orderItems']);

        $statuses = $order->orderItems->pluck('status')->unique()->toArray();

        // Main order status
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

        // Each client order
        foreach ($order->clientOrders as $clientOrder) {
            $clientStatuses = $clientOrder->orderItems->pluck('status')->unique()->toArray();

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

            // Find item with relations
            $item = OrderItem::with(['order.customer', 'client', 'product', 'variant'])
                ->where('id', $itemId)
                ->where('client_id', $client_id)
                ->firstOrFail();

            // Update item as approved
            $item->update([
                'status'       => 'approved',
                'approve_date' => now()->format('Y-m-d'),
                'approve_time' => now()->format('H:i:s'),
            ]);

            $order       = $item->order;
            $clientOrder = $item->clientOrder; 

            // 🔹 Recalculate statuses
            $this->recalcOrderStatuses($order);

            // Reload relations for notification
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

            // Notify customer
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
            $itemId    = $request->input('item_id'); // cancel specific item
            $client_id = $request->header('id');

            $item = OrderItem::with(['order', 'product', 'variant'])
                ->where('id', $itemId)
                ->where('client_id', $client_id)
                ->where('status', 'pending') // only pending items can be canceled
                ->firstOrFail();

            $order = $item->order;
            $clientOrder = ClientOrder::where('order_id', $order->id)
                ->where('client_id', $client_id)
                ->firstOrFail();

            // 1. Mark item canceled
            $item->update([
                'status'      => 'canceled',
                'cancel_date' => now()->format('Y-m-d'),
                'cancel_time' => now()->format('H:i:s'),
            ]);

            // 2. Restore stock
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

            // 3. Calculate refund dynamically
            $refund = OrderBillingService::calculateRefund($item, $clientOrder);

            ClientOrderReturn::create([
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
                'return_date'     => now()->format('Y-m-d'),
                'return_time'     => now()->format('H:i:s'),
            ]);

            // 4. Recalculate billing
            OrderBillingService::recalcBilling($order, $client_id);

            // 🔹 Recalculate statuses
            $this->recalcOrderStatuses($order);

            // Notifications
            if ($order->customer) {
                $order->customer->notify(new OrderStatusUpdateNotification($order, $clientOrder));
            }

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new OrderStatusUpdateNotification($order, $clientOrder));
            }

            ActivityLogger::log(
                'order_canceled_success',
                "Client #{$client_id} canceled items of order #{$order->id}",
                $request,
                'client_orders'
            );

            DB::commit();

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

            // Update only order items (approved → delivered)
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

            // 🔹 Recalculate statuses
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
}
