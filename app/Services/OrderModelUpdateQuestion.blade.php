Below is App\Services\OrderBillingService:

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\DeliveryCharge;
use Illuminate\Support\Facades\Session;

class OrderBillingService
{
    public static function calculateDeliveryFee($items, $cityId, $deliveryOption = 'myself')
    {
        $chargesPerClient = [];
        $totalDeliveryFee = 0;

        if ($deliveryOption === 'courier') {
            foreach ($items as $item) {
                $product = $item->product;
                $client  = $product->client;
                if (!$client) continue;

                $unitWeight  = $product->weight ?? 0;
                $totalWeight = $unitWeight * $item->quantity;

                if (!isset($chargesPerClient[$client->id])) {
                    $chargesPerClient[$client->id] = [
                        'client'      => $client,
                        'totalWeight' => 0,
                        'deliveryFee' => 0,
                    ];
                }

                $chargesPerClient[$client->id]['totalWeight'] += $totalWeight;
            }

            foreach ($chargesPerClient as $clientId => &$data) {
                $client = $data['client'];
                $deliveryCharge = DeliveryCharge::where('client_id', $client->id)->first();
                if (!$deliveryCharge) continue;

                $isInside = $client->city_id == $cityId;
                $weight   = $data['totalWeight'];

                if ($isInside) {
                    if ($weight <= 2) $charge = $deliveryCharge->inside_city_2kg;
                    elseif ($weight <= 5) $charge = $deliveryCharge->inside_city_5kg;
                    elseif ($weight <= 10) $charge = $deliveryCharge->inside_city_10kg;
                    else $charge = $deliveryCharge->inside_city_above_10kg;
                } else {
                    if ($weight <= 2) $charge = $deliveryCharge->outside_city_2kg;
                    elseif ($weight <= 5) $charge = $deliveryCharge->outside_city_5kg;
                    elseif ($weight <= 10) $charge = $deliveryCharge->outside_city_10kg;
                    else $charge = $deliveryCharge->outside_city_above_10kg;
                }

                $data['deliveryFee'] = $charge;
                $totalDeliveryFee   += $charge;
            }
        }

        return [
            'chargesPerClient' => $chargesPerClient,
            'totalDeliveryFee' => $totalDeliveryFee
        ];
    }

    public static function recalcBilling(Order $order, ?int $clientId = null)
    {
        $activeCoupon = Session::get('active_coupon');
        $taxRate      = (float) config('services.tax_rate', 0.20);

        if ($clientId) {
            $clientOrder = ClientOrder::where('order_id', $order->id)
                ->where('client_id', $clientId)
                ->firstOrFail();

            $clientItems = $order->orderItems()
                ->where('client_id', $clientId)
                ->whereIn('status', ['pending', 'approved','delivered']) // only active items
                ->get();

            $subtotal = $clientItems->sum('total_price');

            $couponDiscount = 0;
            if ($activeCoupon && $activeCoupon['client_id'] == $clientId && $subtotal > 0) {
                $couponDiscount = min($activeCoupon['discount'], $subtotal);
            }

            $taxable = $subtotal - $couponDiscount;
            $tax     = round($taxable * $taxRate, 2);

            $cityId = $order->shippingAddress->city_id ?? null;
            $deliveryOption = $order->delivery_type ?? 'myself';

            $deliveryCalc = self::calculateDeliveryFee($clientItems, $cityId, $deliveryOption);
            $deliveryFee  = $deliveryCalc['chargesPerClient'][$clientId]['deliveryFee'] ?? 0;
            $itemsWeight  = $deliveryCalc['chargesPerClient'][$clientId]['totalWeight'] ?? 0;

            $payable = $taxable + $tax + $deliveryFee;

            $clientOrder->update([
                'subtotal'        => $subtotal,
                'coupon_discount' => $couponDiscount,
                'tax'             => $tax,
                'items_weight'    => $itemsWeight,
                'delivery_fee'    => $deliveryFee,
                'payable_amount'  => $payable,
            ]);
        }

        $activeItems = $order->orderItems()
            ->whereIn('status', ['pending', 'approved', 'delivered'])
            ->get();

        $subtotal = $activeItems->sum('total_price');
        $totalCoupon = ClientOrder::where('order_id', $order->id)->sum('coupon_discount');
        $taxable = $subtotal - $totalCoupon;
        $tax     = round($taxable * $taxRate, 2);

        $cityId = $order->shippingAddress->city_id ?? null;
        $deliveryOption = $order->delivery_type ?? 'myself';

        $deliveryCalc = self::calculateDeliveryFee($activeItems, $cityId, $deliveryOption);
        $deliveryFee  = $deliveryCalc['totalDeliveryFee'];

        $payable = $taxable + $tax + $deliveryFee;

        $order->update([
            'subtotal'        => $subtotal,
            'coupon_discount' => $totalCoupon,
            'tax'             => $tax,
            'delivery_fee'    => $deliveryFee,
            'payable_amount'  => $payable,
        ]);
    }

    public static function calculateRefund(OrderItem $item, ClientOrder $clientOrder)
    {
        $taxRate = (float) config('services.tax_rate', 0.20);
        $subtotal = $item->total_price;
        $couponDiscount = 0;
        if ($clientOrder->coupon_discount > 0 && $clientOrder->subtotal > 0) {
            $couponShare = ($subtotal / $clientOrder->subtotal) * $clientOrder->coupon_discount;
            $couponDiscount = round($couponShare, 2);
        }

        $taxable = $subtotal - $couponDiscount;
        $tax     = round($taxable * $taxRate, 2);

        $remainingItems = $clientOrder->order
            ->orderItems()
            ->where('client_id', $item->client_id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('id', '!=', $item->id)
            ->get();

        $cityId = $clientOrder->order->shippingAddress->city_id ?? null;
        $deliveryOption = $clientOrder->order->delivery_type ?? 'myself';

        $oldDeliveryFee = $clientOrder->delivery_fee;

        $deliveryCalc = self::calculateDeliveryFee($remainingItems, $cityId, $deliveryOption);
        $newDeliveryFee = $deliveryCalc['chargesPerClient'][$item->client_id]['deliveryFee'] ?? 0;

        $deliveryRefund = max(0, $oldDeliveryFee - $newDeliveryFee);

        $totalRefund = $subtotal - $couponDiscount + $tax + $deliveryRefund;

        $activeItemsCount = $clientOrder->order
            ->orderItems()
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        $extraCouponRefund = 0;

        if ($activeItemsCount === 0) {
            $order = $clientOrder->order;
            $orderCoupon = $order->coupon_discount ?? 0;

            if ($orderCoupon > 0) {
                $extraCouponRefund = $orderCoupon;

                // ✅ Reset coupon_discount in main order
                $order->update([
                    'coupon_discount' => 0,
                ]);

                // ✅ Reset coupon_discount for all client orders under this order
                ClientOrder::where('order_id', $order->id)
                    ->update(['coupon_discount' => 0]);
            }
        }

        return [
        'subtotal'            => $subtotal,
        'coupon_discount'     => $couponDiscount + $extraCouponRefund, // combined
        'tax'                 => $tax,
        'delivery_fee'        => $deliveryRefund,
        'total_refund'        => $subtotal - $couponDiscount + $tax + $deliveryRefund + $extraCouponRefund,
        ];
    }

}





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
                'action_reason'   => $actionReason,
                'action_type'     => 'canceled',
                'action_by_id'    => $client_id,
                'action_by_role'  => 'client',
            ]);

            OrderBillingService::recalcBilling($order, $client_id);
            $this->recalcOrderStatuses($order);

            ActivityLogger::log(
                'order_canceled_success',
                "Client #{$client_id} canceled items of order #{$order->id}",
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
}

before cancel:
subtotal   coupon_discount(10%)      tax     delivery fee    payable_amount
 1000         100                    180      100              1180

 there is 2 item in above order model of order id 1. when a client cancel an item which price is 500 and qty is 1 and there is coupon_discount 100 of 2 items(10%). there is 20% tax rate.So price will deduct 500,tax will deduct 90.delivery_fee depending on weigt and city.after cancelation order model will be as follows.check coupon_discount belongs to client or not.

After cancel it will be like this:
subtotal   coupon_discount      tax     delivery fee    payable_amount
 500          50                 90           100            640

 after cancel it will properly update Order,ClientOrder,ClientOrderAction model with proper bill calculation.


From above code when execute public function orderCanceled(Request $request) all model is working fine and updated as expexted.But when client cancel an item than should check that if there is no item with status pending,approved for an specific order(because an order may have multiple clients multiple item) and also check have coupon_discount exists or not.if coupon_discount exists and there is no item in order_item model with pending,approved than coupon_discount will be rufund.Apply this logic in above code.






before cancel:
subtotal   coupon_discount      tax     delivery fee    payable_amount
 10300      50                  2050      180              12480

 above is now order model column of order id 1. when a client cancel an item which price is 200 and qty is 2 and there is no coupon_discount of cancel item. there is 20% tax rate.So price will deduct 400,tax will deduct 80.delivery_fee depending on weigt and city.after cancelation order model will be as follows.

After cancel it will be like this:
subtotal   coupon_discount      tax     delivery fee    payable_amount
 9900      50                   480      180              12000
...................................................................................

