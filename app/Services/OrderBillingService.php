<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\DeliveryCharge;
use Illuminate\Support\Facades\Session;

class OrderBillingService
{
    /**
     * Calculate delivery fees per client and total.
     */
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

    /**
     * Recalculate billing for order and optionally for specific client.
     */
    public static function recalcBilling(Order $order, ?int $clientId = null)
    {
        $activeCoupon = Session::get('active_coupon');
        $taxRate      = (float) config('services.tax_rate', 0.20);

        // 🔹 Recalculate for client
        if ($clientId) {
            $clientOrder = ClientOrder::where('order_id', $order->id)
                ->where('client_id', $clientId)
                ->firstOrFail();

            $clientItems = $order->orderItems()
                ->where('client_id', $clientId)
                ->whereIn('status', ['pending', 'approved', 'delivered'])
                ->get();

            $subtotal = $clientItems->sum('total_price');
            $couponDiscount = 0;

            if ($activeCoupon && $activeCoupon['client_id'] == $clientId && $subtotal > 0) {
                $couponDiscount = min($activeCoupon['discount'], $subtotal);
            } elseif ($clientOrder->coupon_discount > 0 && $clientOrder->subtotal > 0) {
                // use proportional discount if saved previously
                $rate = $clientOrder->coupon_discount / $clientOrder->subtotal;
                $couponDiscount = round($subtotal * $rate, 2);
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

        // 🔹 Recalculate for main order
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

    /**
     * Calculate refund for canceled item.
     */
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

        // If this was the last active item, reset coupon usage
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

                $order->update(['coupon_discount' => 0]);
                ClientOrder::where('order_id', $order->id)
                    ->update(['coupon_discount' => 0]);
            }
        }

        return [
            'subtotal'        => $subtotal,
            'coupon_discount' => $couponDiscount + $extraCouponRefund,
            'tax'             => $tax,
            'delivery_fee'    => $deliveryRefund,
            'total_refund'    => $totalRefund + $extraCouponRefund,
        ];
    }
}
