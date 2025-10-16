<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{
    Cart, Order, OrderItem, Product, ProductVariant,
    ShippingAddress, StockMovement, User
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{


    public function OldcashOrder(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $customer = User::findOrFail($request->header('id'));
            $cartItems = Cart::with(['product', 'productVariant'])
                ->where('customer_id', $customer->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your cart is empty'
                ], 400);
            }

            $this->validateStock($cartItems);

            $orders = $this->processOrders(
                customer: $customer,
                cartItems: $cartItems->groupBy(fn($item) => $item->product->client_id),
                requestData: $request->all()
            );

            $this->cleanupSession($customer);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'orders' => $this->formatOrderResponse($orders),
                'redirect_url' => route('order.confirmation', $orders->first()->id)
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            report($e); // Log the exception

            return response()->json([
                'status' => 'error',
                'message' => 'Order processing failed. Please try again.'
            ], 500);
        }
    }

    private function validateStock($cartItems): void
    {
        $cartItems->each(function ($item) {
            $stock = $item->product_variant_id
                ? $item->productVariant->current_stock
                : $item->product->current_stock;

            if ($stock < $item->quantity) {
                abort(400, "Insufficient stock for {$item->product->name}");
            }
        });
    }

    private function processOrders($customer, $cartItems, array $requestData): \Illuminate\Support\Collection
    {
        $activeCoupon = Session::get('active_coupon');
        $taxRate = (float) config('services.tax_rate', 0.20);
        //$taxRate = config('shop.tax_rate', 0.20);

        return $cartItems->map(function ($vendorItems, $clientId) use ($customer, $activeCoupon, $taxRate, $requestData) {
            $order = $this->createOrder(
                customer: $customer,
                clientId: $clientId,
                vendorItems: $vendorItems,
                activeCoupon: $activeCoupon,
                taxRate: $taxRate
            );

            $this->createShippingAddress($order, $requestData);
            $this->processOrderItems($vendorItems, $order, $clientId);

            return $order;
        });
    }

    private function createOrder($customer, $clientId, $vendorItems, $activeCoupon, $taxRate): Order
    {
        $subtotal = $vendorItems->sum(fn($item) => $item->price * $item->quantity);
        $couponDiscount = $this->calculateCouponDiscount($clientId, $activeCoupon, $subtotal);
        $taxableAmount = $subtotal - $couponDiscount;
        $tax = round($taxableAmount * $taxRate, 2);

        return Order::create([
            'customer_id' => $customer->id,
            'client_id' => $clientId,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'coupon_discount' => $couponDiscount,
            'payable_amount' => round($taxableAmount + $tax, 2),
            'paid_amount' => round($taxableAmount + $tax, 2),
            'payment_type' => Order::PAYMENT_TYPE_CASH,
            'payment_method' => Order::PAYMENT_METHOD_CASH,
            'currency' => config('shop.currency', 'USD'),
            'order_number' => 'CASH-' . Str::upper(Str::random(16)),
            'invoice_no' => 'INV' . mt_rand(10000000, 99999999),
            'order_date' => now()->toDateString(),
            'order_time' => now()->toTimeString(),
            'status' => Order::STATUS_PENDING,
            'accept_order_request_tnc' => true,
            'accept_product_delivery_tnc' => true,
        ]);
    }

    private function calculateCouponDiscount($clientId, $activeCoupon, $subtotal): float
    {
        return ($clientId === ($activeCoupon['client_id'] ?? null))
            ? min($activeCoupon['discount'] ?? 0, $subtotal)
            : 0;
    }

    private function createShippingAddress(Order $order, array $requestData): void
    {
        ShippingAddress::create([
            'order_id' => $order->id,
            'name' => $requestData['name'],
            'email' => $requestData['email'],
            'phone' => $requestData['phone'],
            'address1' => $requestData['address1'],
            'address2' => $requestData['address2'],
            'zip_code' => $requestData['zip_code'],
            'country_id' => $requestData['country_id'],
            'county_id' => $requestData['county_id'],
            'city_id' => $requestData['city_id']
        ]);
    }

    private function processOrderItems($vendorItems, Order $order, $clientId): void
    {
        $vendorItems->each(function ($item) use ($order, $clientId) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'total_price' => $item->price * $item->quantity,
                'color' => $item->productVariant->color ?? null,
                'size' => $item->productVariant->size ?? null
            ]);

            $this->updateStock($item);
            $this->recordStockMovement($item, $clientId, $order);
        });
    }

    private function updateStock($item): void
    {
        if ($item->product_variant_id) {
            $variant = ProductVariant::findOrFail($item->product_variant_id);
            $variant->decrement('current_stock', $item->quantity);
            
            // Update parent product stock from variants
            $variant->product->update([
                'current_stock' => $variant->product->variants()->sum('current_stock')
            ]);
        } else {
            Product::findOrFail($item->product_id)
                ->decrement('current_stock', $item->quantity);
        }
    }

    private function recordStockMovement($item, $clientId, Order $order): void
    {
        StockMovement::create([
            'product_id' => $item->product_variant_id ? null : $item->product_id,
            'variant_id' => $item->product_variant_id,
            'client_id' => $clientId,
            'order_id' => $order->id,
            'quantity' => -$item->quantity,
            'movement_type' => StockMovement::TYPE_SALE
        ]);
    }

    private function cleanupSession($customer): void
    {
        Cart::where('customer_id', $customer->id)->delete();
        Session::forget('active_coupon');
    }

    private function formatOrderResponse($orders): \Illuminate\Support\Collection
    {
        return $orders->map(fn($order) => [
            'id' => $order->id,
            'invoice_no' => $order->invoice_no,
            'client_id' => $order->client_id
        ]);
    }
}