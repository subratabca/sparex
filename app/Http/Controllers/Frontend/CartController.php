<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\BannedCustomer;
use App\Models\Coupon;
use Exception;

class CartController extends Controller
{
    public function myCart()
    {
        return view('frontend.pages.cart.cart-page');
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'variant_id' => 'nullable|exists:product_variants,id'
            ]);

            $customer_id = $request->header('id');
            $product = Product::findOrFail($request->product_id);
            $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;
            $price = $product->has_discount_price ? $product->discount_price : $product->price;
            $variantId = $variant ? $variant->id : null;

            $banned = BannedCustomer::where('client_id', $product->client_id)
                ->where('customer_id', $customer_id)
                ->first();

            if ($banned) {
                $clientName = optional($banned->client)->name ?? 'this client';
                return response()->json([
                    'status' => 'failed',
                    'message' => "You are banned by {$clientName}. You cannot order until the client removes the ban."
                ], 403);
            }

            $cart = Cart::where([
                'customer_id' => $customer_id,
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
            ])->first();

            if ($cart) {
                $cart->increment('quantity', $request->quantity);
            } else {
                $cart = Cart::create([
                    'customer_id' => $customer_id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variantId,
                    'quantity' => $request->quantity, 
                    'price' => $price
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Product added to cart',
                'data' => $cart
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error adding to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function count(Request $request)
    {
        try {
            $customer_id = $request->header('id');
            if (!$customer_id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized! Need to login.',
                ], 400);
            }
            $count = Cart::where('customer_id', $customer_id)->sum('quantity');

            return response()->json([
                'status' => 'success',
                'count' => $count,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving cart count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'cart_id' => 'required|exists:carts,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem = Cart::findOrFail($request->cart_id);
            $cartItem->update(['quantity' => $request->quantity]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cart updated',
                'data' => $cartItem
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error updating cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $request->validate([
                'cart_id' => 'required|exists:carts,id'
            ]);

            Cart::where('id', $request->cart_id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Item removed from cart'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error removing item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function total(Request $request)
    {
        try {
            $customer_id = $request->header('id');
            $total = Cart::where('customer_id', $customer_id)->sum(\DB::raw('price * quantity'));

            return response()->json([
                'status' => 'success',
                'data' => ['total' => $total]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving cart total',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateCoupon($code)
    {
        try {
            $coupon = Coupon::where('coupon_name', $code)
                ->where('expire_date', '>', now())
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'expire_date' => $coupon->expire_date,
                    'discount_percent' => $coupon->coupon_discount,
                    'client_id' => $coupon->client_id
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired coupon code'
            ], 404);
        }
    }

    public function applyCoupon(Request $request)
    {
        try {
            $customerId = $request->header('id');
            $couponCode = $request->coupon_code;

            // Get coupon details
            $coupon = Coupon::where('coupon_name', $couponCode)
                ->where('expire_date', '>', now())
                ->firstOrFail();

            // Get customer's cart items
            $cartItems = Cart::with(['product.client'])
                ->where('customer_id', $customerId)
                ->get();

            // Calculate discount for specific client's products
            $discountableAmount = $cartItems->sum(function ($item) use ($coupon) {
                if ($item->product->client_id == $coupon->client_id) {
                    return $item->price * $item->quantity;
                }
                return 0;
            });

            if ($discountableAmount <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No applicable products for this coupon'
                ], 400);
            }

            $discount = round($discountableAmount * ($coupon->coupon_discount / 100), 2);

            // Store coupon in session
            session()->put('active_coupon', [
                'code' => $couponCode,
                'discount' => $discount,
                'discount_percent' => $coupon->coupon_discount,
                'client_id' => $coupon->client_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Coupon applied successfully',
                'discount' => $discount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to apply coupon: ' . $e->getMessage()
            ], 400);
        }
    }

    public function removeCoupon(Request $request)
    {
        try {
            session()->forget('active_coupon');
            return response()->json([
                'status' => 'success',
                'message' => 'Coupon removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove coupon'
            ], 500);
        }
    }

    public function getCartProduct(Request $request)
    {
        try {
            $customerId = $request->header('id');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 3);
            $offset = ($page - 1) * $perPage;

            // Get all cart items for summary calculation
            $allCartItems = Cart::with([
                    'product:id,name,price,discount_price,image,client_id,current_stock',
                    'product.client:id,firstName,lastName',
                    'productVariant:id,color,size,current_stock'
                ])
                ->where('customer_id', $customerId)
                ->get();

            // Paginate results
            $paginatedItems = $allCartItems->slice($offset, $perPage)->values();

            // Calculate summary using all items
            $cartQty = $allCartItems->sum('quantity');
            $subtotal = $allCartItems->sum(function ($item) {
                return round($item->price * $item->quantity, 2);
            });

            // Coupon discount calculation
            $couponDiscount = 0;
            if (session()->has('active_coupon')) {
                $couponDiscount = session('active_coupon')['discount'];
            }

            $taxRate = (float) config('services.tax_rate', 0.20);
            $taxableAmount = $subtotal - $couponDiscount;
            $tax = round($taxableAmount * $taxRate, 2);
            $total = round($taxableAmount + $tax, 2);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'cart_items' => $paginatedItems,
                    'summary' => [
                        'cartQty' => $cartQty,
                        'subtotal' => $subtotal,
                        'coupon_discount' => $couponDiscount,
                        'taxRate' => $taxRate,
                        'tax' => $tax,
                        'total' => $total,
                        'item_count' => $allCartItems->count(),
                        'total_quantity' => $cartQty
                    ],
                    'pagination' => [
                        'has_more' => ($offset + $perPage) < $allCartItems->count(),
                        'total_items' => $allCartItems->count(),
                        'current_page' => (int)$page,
                        'per_page' => (int)$perPage
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Cart API Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to retrieve cart items',
                // Only include error details in non-production environments
                'error' => $e->getMessage()
            ], 500);
        }
    }

}




