<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\{
    User, Order, OrderItem, Cart, Product, 
    ProductVariant, StockMovement, ShippingAddress, Coupon
};
use Stripe\Stripe;
use Stripe\Charge;
use Exception;

class PaymentController extends Controller
{
    public function stripeOrder(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Get authenticated customer
            $customer_id = $request->header('id');

            $customer = User::where('id',$customer_id)->first();
            
            // Get cart items with product/variant information
            $cartItems = Cart::with(['product', 'productVariant'])
                ->where('customer_id', $customer->id)
                ->get();

            // Validate cart items
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your cart is empty'
                ], 400);
            }

            // Calculate totals and validate stock
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $stock = $item->product_variant_id 
                    ? $item->productVariant->current_stock
                    : $item->product->current_stock;

                if ($stock < $item->quantity) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Insufficient stock for {$item->product->name}",
                        'product_id' => $item->product_id
                    ], 400);
                }

                $subtotal += $item->price * $item->quantity;
            }

            // Apply coupon discount
            $couponDiscount = Session::get('active_coupon')['discount'] ?? 0;
            $totalAmount = max($subtotal - $couponDiscount, 0);

            // Process Stripe payment
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $charge = Charge::create([
                'amount' => $totalAmount * 100,
                'currency' => 'usd',
                'source' => $request->stripeToken,
                'description' => 'Order payment',
                'metadata' => ['order_id' => uniqid()]
            ]);

            // Create order
            $taxRate = config('services.tax_rate', 0.20); 
            $taxableAmount = $subtotal - $couponDiscount;
            $tax = round($taxableAmount * $taxRate, 2);
            $totalAmount = round($taxableAmount + $tax, 2);

            // Get client_id from the first product (assuming single vendor order)
            $clientId = $cartItems->first()->product->client_id;

            $order = Order::create([
                'customer_id' => $customer->id,
                'client_id' => $clientId,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount_amount' => 0, // Add other discounts if applicable
                'coupon_discount' => $couponDiscount,
                'payable_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'payment_method' => 'stripe',
                'payment_type' => 'card', // Example value
                'transaction_id' => $charge->balance_transaction,
                'currency' => $charge->currency,
                'order_number' => $charge->metadata->order_id,
                'invoice_no' => 'INV'.mt_rand(100000, 999999),
                'order_date' => Carbon::now()->format('Y-m-d'),
                'order_time' => Carbon::now()->format('H:i:s'),
                'status' => 'pending',
                'is_free' => $totalAmount <= 0,
                // These fields can be updated later as order progresses
                'accept_order_request_tnc' => now(),
                'accept_product_delivery_tnc' => null,
                'approve_date' => null,
                'approve_time' => null,
                'delivery_date' => null,
                'delivery_time' => null,
                'cancel_date' => null,
                'cancel_time' => null
            ]);

            // Create shipping address
            ShippingAddress::create([
                'order_id' => $order->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'zip_code' => $request->zip_code,
                'country_id' => $request->country_id,
                'county_id' => $request->county_id,
                'city_id' => $request->city_id
            ]);

            // Process order items and stock
            foreach ($cartItems as $item) {
                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'selling_qty' => $item->quantity,
                    'color' => $item->productVariant->color ?? null,
                    'size' => $item->productVariant->size ?? null
                ]);

                // Update stock
                if ($item->product_variant_id) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    $variant->decrement('current_stock', $item->quantity);
                    
                    StockMovement::create([
                        'variant_id' => $item->product_variant_id,
                        'client_id' => $item->product->client_id,
                        'order_id' => $order->id,
                        'quantity' => -$item->quantity,
                        'movement_type' => StockMovement::TYPE_SALE
                    ]);
                } else {
                    $product = Product::find($item->product_id);
                    $product->decrement('current_stock', $item->quantity);
                    
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'client_id' => $item->product->client_id,
                        'order_id' => $order->id,
                        'quantity' => -$item->quantity,
                        'movement_type' => StockMovement::TYPE_SALE
                    ]);
                }
            }

            // Clear cart and coupon session
            Cart::where('customer_id', $customer->id)->delete();
            Session::forget('active_coupon');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'order_id' => $order->id,
                'redirect_url' => route('order.confirmation', $order->id)
            ]);

        } catch (\Stripe\Exception\CardException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed: '.$e->getError()->message
            ], 400);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Order processing failed: '.$e->getMessage()
            ], 500);
        }
    }
}