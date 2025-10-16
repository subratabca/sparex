<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\Order\NewOrderNotification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\OrderBillingService;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\Cart;
use App\Models\DeliveryCharge;
use App\Models\ShippingAddress;
use App\Models\StockMovement;
use App\Models\OrderHistory;

use Stripe\Stripe;
use Stripe\Charge;
use Exception;

class PaymentController extends Controller
{
    public function getCourierCharge(Request $request)
    {
        $customer = User::findOrFail($request->header('id'));
        $cityId = $request->query('city_id');

        if (!$cityId) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must select city'
            ], 422);
        }

        $cartItems = Cart::with('product.client')
            ->where('customer_id', $customer->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty'
            ], 404);
        }

        $chargesPerClient = [];
        $totalCharge = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            $client = $product->client;

            if (!$client) {
                continue; 
            }

            $productWeight = ($product->weight ?? 0) * $item->quantity;
            if (!isset($chargesPerClient[$client->id])) {
                $chargesPerClient[$client->id] = [
                    'client' => $client,
                    'totalWeight' => 0
                ];
            }

            $chargesPerClient[$client->id]['totalWeight'] += $productWeight;
        }

        foreach ($chargesPerClient as $clientId => $data) {
            $client = $data['client'];
            $totalWeight = $data['totalWeight'];

            $deliveryCharge = DeliveryCharge::where('client_id', $client->id)->first();
            if (!$deliveryCharge) {
                continue; 
            }

            $isInside = $client->city_id == $cityId;

            if ($isInside) {
                if ($totalWeight <= 2) {
                    $charge = $deliveryCharge->inside_city_2kg;
                } elseif ($totalWeight <= 5) {
                    $charge = $deliveryCharge->inside_city_5kg;
                } elseif ($totalWeight <= 10) {
                    $charge = $deliveryCharge->inside_city_10kg;
                } else {
                    $charge = $deliveryCharge->inside_city_above_10kg;
                }
            } else {
                if ($totalWeight <= 2) {
                    $charge = $deliveryCharge->outside_city_2kg;
                } elseif ($totalWeight <= 5) {
                    $charge = $deliveryCharge->outside_city_5kg;
                } elseif ($totalWeight <= 10) {
                    $charge = $deliveryCharge->outside_city_10kg;
                } else {
                    $charge = $deliveryCharge->outside_city_above_10kg;
                }
            }

            $totalCharge += $charge;
        }

        if ($totalCharge <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to calculate courier charge'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'charge' => $totalCharge
        ]);
    }

    public function cashOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $customer = User::findOrFail($request->header('id'));
            $cartItems = Cart::with(['product', 'productVariant', 'product.client'])
                ->where('customer_id', $customer->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your cart is empty'
                ], 400);
            }

            foreach ($cartItems as $item) {
                $stock = $item->product_variant_id
                    ? $item->productVariant->current_stock
                    : $item->product->current_stock;

                if ($stock < $item->quantity) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Insufficient stock for {$item->product->name}"
                    ], 400);
                }
            }

            $currency       = 'usd';
            $deliveryOption = $request->input('delivery_option', 'myself');

            $deliveryData     = OrderBillingService::calculateDeliveryFee($cartItems, $request->city_id, $deliveryOption);
            $chargesPerClient = $deliveryData['chargesPerClient'];
            $totalDeliveryFee = $deliveryData['totalDeliveryFee'];

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'delivery_type' => $deliveryOption,
                'subtotal' => 0,
                'tax' => 0,
                'delivery_fee' => $totalDeliveryFee,
                'coupon_discount' => 0,
                'payable_amount' => 0,
                'paid_amount' => 0,
                'payment_type' => 'cash',
                'payment_method' => 'cash',
                'currency' => $currency,
                'order_number' => 'CASH-' . \Str::uuid(),
                'invoice_no' => 'INV' . time(),
                'order_date' => now()->format('Y-m-d'),
                'order_time' => now()->format('H:i:s'),
                'accept_order_request_tnc' => $request->input('accept_tnc') ? 1 : 0,
            ]);

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

            $productsToUpdate = [];
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'client_id' => $item->product->client_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'color' => $item->productVariant->color ?? null,
                    'size' => $item->productVariant->size ?? null,
                    'status' => 'pending',
                ]);

                if ($item->product_variant_id) {
                    $variant = $item->productVariant;
                    $variant->decrement('current_stock', $item->quantity);
                    $productsToUpdate[] = $item->product_id;
                } else {
                    $item->product->decrement('current_stock', $item->quantity);
                }

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'client_id' => $item->product->client_id,
                    'order_id' => $order->id,
                    'variant_id' => $item->product_variant_id,
                    'quantity' => -$item->quantity,
                    'movement_type' => StockMovement::TYPE_SALE
                ]);
            }

            foreach (array_unique($productsToUpdate) as $productId) {
                $product = Product::find($productId);
                $product->update([
                    'current_stock' => $product->variants()->sum('current_stock')
                ]);
            }

            $groupedItems = $order->orderItems->groupBy('client_id');

            foreach ($groupedItems as $clientId => $items) {
                $clientSubtotal = $items->sum('total_price');
                $clientDeliveryFee = $deliveryOption === 'courier'
                    ? ($chargesPerClient[$clientId]['deliveryFee'] ?? 0)
                    : 0;

                $clientOrder = ClientOrder::create([
                    'order_id' => $order->id,
                    'client_id' => $clientId,
                    'subtotal' => $clientSubtotal,
                    'coupon_discount' => 0,
                    'tax' => 0,
                    'payable_amount' => 0,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'items_weight' => $chargesPerClient[$clientId]['totalWeight'] ?? 0,
                    'delivery_fee' => $clientDeliveryFee,
                ]);

                OrderBillingService::recalcBilling($order, $clientId);
            }

            OrderBillingService::recalcBilling($order);

            $order->update([
                'paid_amount' => $order->payable_amount,
            ]);

            $order->clientOrders()->update([
                'paid_amount' => DB::raw('payable_amount')
            ]);

            Cart::where('customer_id', $customer->id)->delete();
            Session::forget('active_coupon');

            DB::commit();

            $admin = User::where('role', 'admin')->first();
            $admin?->notify(new NewOrderNotification($order));

            if ($order->customer) {
                $order->customer->notify(new NewOrderNotification($order));
            }

            foreach ($order->clientOrders as $clientOrder) {
                if ($clientOrder->client) {
                    $clientOrder->client->notify(new NewOrderNotification($clientOrder));
                }
            }

            return response()->json([
                'status' => 'success',
                'redirect_url' => route('order.confirmation', ['orderId' => $order->id])
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Order processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function createPaymentIntent(Request $request)
    {
        DB::beginTransaction();
        try {
            $customer = User::findOrFail($request->header('id'));
            $cartItems = Cart::with(['product', 'productVariant'])
                ->where('customer_id', $customer->id)
                ->get();

            // Calculate order total
            $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            $activeCoupon = Session::get('active_coupon');
            $couponDiscount = 0;
            
            if ($activeCoupon) {
                $couponClientId = $activeCoupon['client_id'] ?? null;
                $eligibleSubtotal = $cartItems->sum(function($item) use ($couponClientId) {
                    return $item->product->client_id == $couponClientId 
                        ? $item->price * $item->quantity 
                        : 0;
                });
                $couponDiscount = min($activeCoupon['discount'], $eligibleSubtotal);
            }
            
            $taxableAmount = $subtotal - $couponDiscount;
            $tax = round($taxableAmount * config('services.tax_rate'), 2);
            $total = round($taxableAmount + $tax, 2);

            Stripe::setApiKey(config('services.stripe.secret'));
            
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $total * 100, // Amount in cents
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'customer_id' => $customer->id,
                    'cart_hash' => md5($cartItems->toJson()) // For cart validation
                ]
            ]);

            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'client_secret' => $paymentIntent->client_secret
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Intent creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stripeOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $customer = User::findOrFail($request->header('id'));
            $paymentIntentId = $request->input('payment_intent_id');

            // Validate payment intent
            Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not completed successfully'
                ], 400);
            }

            // Process cart items
            $cartItems = Cart::with(['product', 'productVariant'])
                ->where('customer_id', $customer->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Your cart is empty'], 400);
            }

            // Stock validation
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
            }

            // Calculate totals
            $activeCoupon = Session::get('active_coupon');
            $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            $couponDiscount = 0;
            
            if ($activeCoupon) {
                $couponClientId = $activeCoupon['client_id'] ?? null;
                $eligibleSubtotal = $cartItems->sum(function($item) use ($couponClientId) {
                    return $item->product->client_id == $couponClientId 
                        ? $item->price * $item->quantity 
                        : 0;
                });
                $couponDiscount = min($activeCoupon['discount'], $eligibleSubtotal);
            }
            
            $taxableAmount = $subtotal - $couponDiscount;
            $tax = round($taxableAmount * config('services.tax_rate'), 2);
            $total = round($taxableAmount + $tax, 2);

            // Validate amount matches
            $calculatedAmount = (int)($total * 100);
            if ($paymentIntent->amount !== $calculatedAmount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order total has changed. Please refresh and try again.'
                ], 400);
            }

            // Create order
            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'coupon_discount' => $couponDiscount,
                'payable_amount' => $total,
                'paid_amount' => $total,
                'payment_type' => 'card',
                'payment_method' => 'stripe',
                'transaction_id' => $paymentIntent->id,
                'currency' => 'usd',
                'order_number' => 'STRIPE-' . Str::upper(Str::random(16)),
                'invoice_no' => 'INV' . mt_rand(100000, 999999),
                'order_date' => now()->format('Y-m-d'),
                'order_time' => now()->format('H:i:s'),
                'status' => 'pending',
                'accept_order_request_tnc' => 1,
                'accept_product_delivery_tnc' => 1,
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

            // Process order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'client_id' => $item->product->client_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'color' => $item->productVariant->color ?? null,
                    'size' => $item->productVariant->size ?? null
                ]);

                // Update stock
                if ($item->product_variant_id) {
                    $variant = $item->productVariant;
                    $variant->decrement('current_stock', $item->quantity);
                    $item->product->update([
                        'current_stock' => $item->product->variants()->sum('current_stock')
                    ]);
                } else {
                    $item->product->decrement('current_stock', $item->quantity);
                }

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'client_id' => $item->product->client_id,
                    'order_id' => $order->id,
                    'variant_id' => $item->product_variant_id,
                    'quantity' => -$item->quantity,
                    'movement_type' => StockMovement::TYPE_SALE
                ]);
            }

            // Cleanup
            Cart::where('customer_id', $customer->id)->delete();
            Session::forget('active_coupon');
            DB::commit();

            return response()->json([
                'status' => 'success',
                'redirect_url' => route('order.confirmation', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Order processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stripeOrder111111111111(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $customer = User::findOrFail($request->header('id'));
            $cartItems = Cart::with(['product', 'productVariant'])
                ->where('customer_id', $customer->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Your cart is empty'], 400);
            }

            // Stock validation
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
            }

            $activeCoupon = Session::get('active_coupon');
            $taxRate = (float) config('services.tax_rate', 0.20);

            // Calculate totals
            $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
            
            // Calculate coupon discount
            $couponDiscount = 0;
            if ($activeCoupon) {
                $couponClientId = $activeCoupon['client_id'] ?? null;
                $eligibleSubtotal = $cartItems->sum(function($item) use ($couponClientId) {
                    return $item->product->client_id == $couponClientId 
                        ? $item->price * $item->quantity 
                        : 0;
                });
                $couponDiscount = min($activeCoupon['discount'], $eligibleSubtotal);
            }

            $taxableAmount = $subtotal - $couponDiscount;
            $tax = round($taxableAmount * $taxRate, 2);
            $total = round($taxableAmount + $tax, 2);

            // Process Stripe payment
            Stripe::setApiKey(config('services.stripe.secret'));
            $orderNumber = 'STRIPE-' . Str::random(16); // Generate before charge

            $charge = Charge::create([
                'amount' => $total * 100, // Convert to cents
                'currency' => 'usd',
                'source' => $request->stripeToken,
                'description' => 'Order payment for ' . $customer->email,
                'metadata' => [
                    'order_number' => $orderNumber,
                    'customer_id' => $customer->id
                ],
            ]);

            // Create single order
            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'coupon_discount' => $couponDiscount,
                'payable_amount' => $total,
                'paid_amount' => $total,
                'payment_type' => 'card', // Explicit payment type
                'payment_method' => 'stripe',
                'transaction_id' => $charge->balance_transaction,
                'currency' => 'usd', // Hardcode if only USD supported
                'order_number' => 'STRIPE-' . Str::upper(Str::random(16)),
                'invoice_no' => 'INV' . mt_rand(100000, 999999),
                'order_date' => now()->format('Y-m-d'),
                'order_time' => now()->format('H:i:s'),
                'status' => 'pending',
                'accept_order_request_tnc' => 1,
                'accept_product_delivery_tnc' => 1,
            ]);

            // Create single shipping address
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

            // Process all order items
            foreach ($cartItems as $item) {
                // Create order item with vendor tracking
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'client_id' => $item->product->client_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'color' => $item->productVariant->color ?? null,
                    'size' => $item->productVariant->size ?? null
                ]);

                // Update stock
                if ($item->product_variant_id) {
                    $variant = $item->productVariant;
                    $variant->decrement('current_stock', $item->quantity);
                    
                    // Update parent product stock from variants sum
                    $item->product->update([
                        'current_stock' => $item->product->variants()->sum('current_stock')
                    ]);
                } else {
                    $item->product->decrement('current_stock', $item->quantity);
                }

                // Stock movement record
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'client_id' => $item->product->client_id,
                    'order_id' => $order->id,
                    'variant_id' => $item->product_variant_id,
                    'quantity' => -$item->quantity,
                    'movement_type' => StockMovement::TYPE_SALE
                ]);
            }

            // Cleanup
            Cart::where('customer_id', $customer->id)->delete();
            Session::forget('active_coupon');
            DB::commit();

            return response()->json([
                'status' => 'success',
                'order' => [
                    'id' => $order->id,
                    'invoice_no' => $order->invoice_no,
                ],
                'redirect_url' => route('order.confirmation', $order->id)
            ]);

        } catch (\Stripe\Exception\CardException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed: ' . $e->getError()->message
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Order processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orderConfirmationPage($orderId)
    {
        return view('frontend.pages.checkout.order-confirmation-page');
    }

    public function getOrderInfo(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $customerId = $request->header('id');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 3);

            $order = Order::with([
                'customer',
                'customer.country',
                'customer.county',
                'customer.city',
                'shippingAddress',
                'shippingAddress.country',
                'shippingAddress.county',
                'shippingAddress.city',
                'clientOrders'
            ])
            ->where('id', $orderId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

            // Load paginated order items
            $orderItems = $order->orderItems()
                ->with(['product', 'variant', 'product.client'])
                ->paginate($perPage, ['*'], 'page', $page);

            // Add pagination data
            $order->order_items = $orderItems->items();
            $order->pagination = [
                'has_more' => $orderItems->hasMorePages(),
                'current_page' => $orderItems->currentPage(),
                'total_items' => $orderItems->total(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            Log::error('Order confirmation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found or unauthorized access'
            ], 404);
        }
    }

}