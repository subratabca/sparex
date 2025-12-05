<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\CreditTransaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class MealOrderController extends Controller
{
    /**
     * Create a Payment Intent for Stripe
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'nullable|string|size:3',
                'description' => 'nullable|string',
                'metadata' => 'nullable|array'
            ]);

            // Get the secret key from config
            $stripeSecretKey = config('services.stripe.secret');
            
            if (empty($stripeSecretKey)) {
                throw new Exception('Stripe secret key is not configured. Check your .env file.');
            }

            // Check if it's a publishable key (shouldn't be)
            if (strpos($stripeSecretKey, 'pk_') === 0) {
                throw new Exception('Invalid Stripe secret key. You are using a publishable key (pk_) for server-side operations. Need secret key (sk_).');
            }

            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => intval($request->amount), // Amount in cents
                'currency' => $request->currency ?? 'usd',
                'description' => $request->description ?? 'Meal Order Payment',
                'metadata' => $request->metadata ?? [],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            \Log::info('Payment Intent Created Successfully', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment intent created successfully',
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount / 100, // Convert back to dollars
                    'currency' => $paymentIntent->currency
                ]
            ], 200);

        } catch (\Stripe\Exception\AuthenticationException $e) {
            \Log::error('Stripe Authentication Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Stripe authentication failed. Please check your API keys.',
                'error' => 'Authentication Error'
            ], 401);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            \Log::error('Stripe Invalid Request: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request to Stripe: ' . $e->getMessage(),
                'error' => 'Invalid Request'
            ], 400);
        } catch (Exception $e) {
            \Log::error('Payment Intent Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Payment Intent creation failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Stripe payment for meal order
     */
    public function storeByStripe(Request $request)
    {
        $request->validate([
            'meal_orders' => 'required|array|min:1',
            'meal_orders.*.meal_date' => 'required|date',
            'meal_orders.*.meal_type_id' => 'required|integer|exists:meal_types,id',
            'meal_orders.*.product_id' => 'required|integer|exists:products,id',
            'meal_orders.*.quantity' => 'required|integer|min:1',
            'meal_orders.*.unit_price' => 'required|numeric|min:0',
            'meal_orders.*.total_price' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'zip_code' => 'required|string|max:20',
            'country_id' => 'required|integer|exists:countries,id',
            'county_id' => 'required|integer|exists:counties,id',
            'city_id' => 'required|integer|exists:cities,id',
            'delivery_option' => 'required|in:self_pickup,courier',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'delivery_charge' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_intent_id' => 'required|string',
            'stripe_payment_id' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $customerId = $request->header('id');
            $mealOrders = $request->meal_orders;
            
            \Log::info('Stripe Order Processing Started', [
                'customer_id' => $customerId,
                'payment_intent_id' => $request->payment_intent_id,
                'total_amount' => $request->total_amount,
                'stripe_payment_id' => $request->stripe_payment_id
            ]);

            // Verify the payment intent
            $stripeSecretKey = config('services.stripe.secret');
            
            if (empty($stripeSecretKey)) {
                throw new Exception('Stripe secret key not configured');
            }
            
            \Stripe\Stripe::setApiKey($stripeSecretKey);
            
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            // Verify payment was successful
            if ($paymentIntent->status !== 'succeeded') {
                throw new Exception('Payment not completed successfully. Status: ' . $paymentIntent->status);
            }

            // Verify amount matches
            $expectedAmount = intval($request->total_amount * 100); // Convert to cents
            if ($paymentIntent->amount !== $expectedAmount) {
                throw new Exception('Payment amount mismatch. Expected: ' . $expectedAmount . ', Got: ' . $paymentIntent->amount);
            }

            // Get TAX_RATE from config
            $taxRate = (float) config('services.tax_rate', 0.10);

            // Calculate overall totals
            $overallSubtotal = 0;
            foreach ($mealOrders as $item) {
                $overallSubtotal += $item['total_price'];
            }
            $overallTax = $overallSubtotal * $taxRate;

            // Generate order numbers
            $orderNumber = 'MO-' . Str::upper(Str::random(8)) . '-' . time();
            $invoiceNo = 'INV-' . Str::upper(Str::random(6)) . '-' . time();

            // ✅ Prepare customer shipping address for distance calculation
            $customerShippingAddress = [
                'city_id'  => $request->city_id,
                'address1' => $request->address1,
                'zip_code' => $request->zip_code,
            ];

            // ✅ FIXED: Use EXACT same grouping logic as storeByCash
            $totalCalculatedDeliveryCharge = 0;
            $chargesPerDate = [];
            
            // Group by meal_date first (EXACTLY like storeByCash)
            $mealOrdersByDate = [];
            foreach ($mealOrders as $item) {
                $date = $item['meal_date'];
                if (!isset($mealOrdersByDate[$date])) {
                    $mealOrdersByDate[$date] = [];
                }
                $mealOrdersByDate[$date][] = $item;
            }

            // Calculate delivery charges per date (EXACTLY like storeByCash)
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $chargesPerClientMealType = [];
                $dateCharge = 0;

                foreach ($itemsForDate as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $client = User::find($product->client_id);
                    $mealType = MealType::find($item['meal_type_id']);
                    
                    if (!$client || !$mealType) continue;

                    // Key: client_id + meal_type_id to avoid double counting
                    $key = $client->id . '_' . $mealType->id;

                    if (isset($chargesPerClientMealType[$key])) {
                        // Already counted this client + meal type for this date
                        continue;
                    }

                    // Ensure client has valid address
                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    // Calculate distance
                    $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    // Get delivery charge for this client + meal type
                    $deliveryCharge = MealDeliveryCharge::where('client_id', $client->id)
                        ->where('meal_type_id', $mealType->id)
                        ->first();

                    if (!$deliveryCharge) continue;

                    // Determine charge based on distance
                    if ($distance <= 2) {
                        $charge = $deliveryCharge->inside_city_2km;
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                    }

                    // Store charge for this client + meal type (counted once)
                    $chargesPerClientMealType[$key] = $charge;
                    $dateCharge += $charge;
                }

                $chargesPerDate[$date] = $dateCharge;
                $totalCalculatedDeliveryCharge += $dateCharge;
            }

            // ✅ Use the CALCULATED delivery charge instead of frontend provided charge
            $calculatedDeliveryCharge = $totalCalculatedDeliveryCharge;
            $overallTotal = $overallSubtotal + $overallTax + $calculatedDeliveryCharge;

            // Create main meal order with CALCULATED delivery charge
            $mealOrder = MealOrder::create([
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'invoice_no' => $invoiceNo,
                'status' => 'pending',
                'delivery_type' => $request->delivery_option,
                'delivery_fee' => $calculatedDeliveryCharge,
                'subtotal' => $overallSubtotal,
                'tax' => $overallTax,
                'payable_amount' => $overallTotal,
                'paid_amount' => $overallTotal, // Full amount paid
                'payment_type' => 'stripe',
                'payment_method' => 'stripe',
                'payment_status' => 'paid',
                'currency' => 'USD',
                'transaction_id' => $request->stripe_payment_id,
            ]);

            // Create shipping address
            MealShippingAddress::create([
                'meal_order_id' => $mealOrder->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'zip_code' => $request->zip_code,
                'country_id' => $request->country_id,
                'county_id' => $request->county_id,
                'city_id' => $request->city_id,
                'latitude' => null,
                'longitude' => null,
            ]);

            $allOrderItems = [];

            // ✅ FIXED: Calculate client delivery fees using the SAME logic
            $clientDeliveryFees = [];
            $clientSubtotals = [];
            $clientTaxes = [];

            // Re-calculate delivery fees per client using the same grouping logic
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $calculatedClientsForDate = []; // Track which clients we've calculated for this date
                
                foreach ($itemsForDate as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $clientId = $product->client_id;
                    $mealTypeId = $item['meal_type_id'];
                    
                    $key = $clientId . '_' . $mealTypeId;

                    // Skip if already calculated this client+meal_type for this date
                    if (isset($calculatedClientsForDate[$key])) {
                        continue;
                    }

                    $client = User::find($clientId);
                    $mealType = MealType::find($mealTypeId);
                    
                    if (!$client || !$mealType) continue;

                    // Ensure client has valid address
                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    // Calculate distance
                    $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    // Get delivery charge
                    $deliveryCharge = MealDeliveryCharge::where('client_id', $clientId)
                        ->where('meal_type_id', $mealTypeId)
                        ->first();

                    if (!$deliveryCharge) continue;

                    // Determine charge based on distance
                    if ($distance <= 2) {
                        $charge = $deliveryCharge->inside_city_2km;
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                    }

                    // Add to client delivery fees
                    if (!isset($clientDeliveryFees[$clientId])) {
                        $clientDeliveryFees[$clientId] = 0;
                    }
                    $clientDeliveryFees[$clientId] += $charge;
                    
                    // Mark as calculated for this date
                    $calculatedClientsForDate[$key] = true;
                }
            }

            // Process meal items and calculate subtotals/taxes per client
            foreach ($mealOrders as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                $clientId = $product->client_id;

                // Initialize client data if not exists
                if (!isset($clientSubtotals[$clientId])) {
                    $clientSubtotals[$clientId] = 0;
                    $clientTaxes[$clientId] = 0;
                }

                // Add to subtotal and tax
                $clientSubtotals[$clientId] += $item['total_price'];
                $clientTaxes[$clientId] += $item['total_price'] * $taxRate;

                // Create order item
                $orderItem = MealOrderItem::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'meal_type_id' => $item['meal_type_id'],
                    'product_id' => $item['product_id'],
                    'meal_date' => $item['meal_date'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $allOrderItems[] = $orderItem;
            }

            // ✅ Create ClientMealOrder records with PROPER delivery fees
            foreach ($clientSubtotals as $clientId => $subtotal) {
                $deliveryFee = $clientDeliveryFees[$clientId] ?? 0;
                $tax = $clientTaxes[$clientId] ?? 0;
                $total = $subtotal + $tax + $deliveryFee;

                ClientMealOrder::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $total,
                    'paid_amount' => $total, // Full amount paid for this client
                    'payment_status' => 'paid',
                ]);
            }

            DB::commit();

            \Log::info('Stripe Order Successfully Created', [
                'order_id' => $mealOrder->id,
                'order_number' => $orderNumber,
                'total_amount' => $overallTotal,
                'items_count' => count($allOrderItems)
            ]);

            // Load relationships for the response
            $mealOrder->load(['items', 'clientMealOrders', 'mealShippingAddress']);

            return response()->json([
                'status' => 'success',
                'message' => 'Stripe payment completed successfully!',
                'data' => [
                    'meal_order' => $mealOrder,
                    'order_items' => $allOrderItems,
                    'total_items' => count($allOrderItems),
                    'total_clients' => count($clientSubtotals),
                    'tax_rate_used' => $taxRate,
                    'delivery_calculation' => [
                        'calculated_total' => $calculatedDeliveryCharge,
                        'frontend_provided' => $request->delivery_charge,
                        'details_per_date' => $chargesPerDate,
                    ],
                    'stripe_details' => [
                        'payment_intent_id' => $paymentIntent->id,
                        'amount_paid' => $paymentIntent->amount / 100,
                        'currency' => $paymentIntent->currency,
                        'payment_method' => $paymentIntent->payment_method,
                    ]
                ],
                'redirect_url' => '/user/meal-order'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Stripe Meal Order Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to process Stripe payment: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }











    public function storeByCash(Request $request)
    {
        $request->validate([
            'meal_orders' => 'required|array|min:1',
            'meal_orders.*.meal_date' => 'required|date',
            'meal_orders.*.meal_type_id' => 'required|integer|exists:meal_types,id',
            'meal_orders.*.product_id' => 'required|integer|exists:products,id',
            'meal_orders.*.quantity' => 'required|integer|min:1',
            'meal_orders.*.unit_price' => 'required|numeric|min:0',
            'meal_orders.*.total_price' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'zip_code' => 'required|string|max:20',
            'country_id' => 'required|integer|exists:countries,id',
            'county_id' => 'required|integer|exists:counties,id',
            'city_id' => 'required|integer|exists:cities,id',
            'delivery_option' => 'required|in:self_pickup,courier',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'delivery_charge' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $customerId = $request->header('id');
            $mealOrders = $request->meal_orders;
            
            // Get TAX_RATE from config
            $taxRate = (float) config('services.tax_rate', 0.10);

            // Calculate overall totals
            $overallSubtotal = 0;
            foreach ($mealOrders as $item) {
                $overallSubtotal += $item['total_price'];
            }
            $overallTax = $overallSubtotal * $taxRate;

            // Generate order numbers
            $orderNumber = 'MO-' . Str::upper(Str::random(8)) . '-' . time();
            $invoiceNo = 'INV-' . Str::upper(Str::random(6)) . '-' . time();

            // ✅ Prepare customer shipping address for distance calculation
            $customerShippingAddress = [
                'city_id'  => $request->city_id,
                'address1' => $request->address1,
                'zip_code' => $request->zip_code,
            ];

            // ✅ FIXED: Use EXACT same grouping logic as MealCartController
            $totalCalculatedDeliveryCharge = 0;
            $chargesPerDate = [];
            
            // Group by meal_date first (EXACTLY like MealCartController)
            $mealOrdersByDate = [];
            foreach ($mealOrders as $item) {
                $date = $item['meal_date'];
                if (!isset($mealOrdersByDate[$date])) {
                    $mealOrdersByDate[$date] = [];
                }
                $mealOrdersByDate[$date][] = $item;
            }

            // Calculate delivery charges per date (EXACTLY like MealCartController)
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $chargesPerClientMealType = [];
                $dateCharge = 0;

                foreach ($itemsForDate as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $client = User::find($product->client_id);
                    $mealType = MealType::find($item['meal_type_id']);
                    
                    if (!$client || !$mealType) continue;

                    // Key: client_id + meal_type_id to avoid double counting (EXACTLY like MealCartController)
                    $key = $client->id . '_' . $mealType->id;

                    if (isset($chargesPerClientMealType[$key])) {
                        // Already counted this client + meal type for this date
                        continue;
                    }

                    // Ensure client has valid address
                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    // Calculate distance
                    $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    // Get delivery charge for this client + meal type
                    $deliveryCharge = MealDeliveryCharge::where('client_id', $client->id)
                        ->where('meal_type_id', $mealType->id)
                        ->first();

                    if (!$deliveryCharge) continue;

                    // Determine charge based on distance (EXACTLY like MealCartController)
                    if ($distance <= 2) {
                        $charge = $deliveryCharge->inside_city_2km;
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                    }

                    // Store charge for this client + meal type (counted once)
                    $chargesPerClientMealType[$key] = $charge;
                    $dateCharge += $charge;
                }

                $chargesPerDate[$date] = $dateCharge;
                $totalCalculatedDeliveryCharge += $dateCharge;
            }

            // ✅ Use the CALCULATED delivery charge instead of frontend provided charge
            $calculatedDeliveryCharge = $totalCalculatedDeliveryCharge;
            $overallTotal = $overallSubtotal + $overallTax + $calculatedDeliveryCharge;

            // Create main meal order with CALCULATED delivery charge
            $mealOrder = MealOrder::create([
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'invoice_no' => $invoiceNo,
                'status' => 'pending',
                'delivery_type' => $request->delivery_option,
                'delivery_fee' => $calculatedDeliveryCharge, // ✅ Use calculated charge
                'subtotal' => $overallSubtotal,
                'tax' => $overallTax,
                'payable_amount' => $overallTotal,
                'paid_amount' => 0,
                'payment_type' => 'cash',
                'payment_method' => 'cash',
                'payment_status' => 'due',
                'currency' => 'USD',
                'transaction_id' => null,
            ]);

            // Create shipping address
            MealShippingAddress::create([
                'meal_order_id' => $mealOrder->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'zip_code' => $request->zip_code,
                'country_id' => $request->country_id,
                'county_id' => $request->county_id,
                'city_id' => $request->city_id,
                'latitude' => null,
                'longitude' => null,
            ]);

            $allOrderItems = [];

            // ✅ FIXED: Calculate client delivery fees using the SAME logic
            $clientDeliveryFees = [];
            $clientSubtotals = [];
            $clientTaxes = [];

            // Re-calculate delivery fees per client using the same grouping logic
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $calculatedClientsForDate = []; // Track which clients we've calculated for this date
                
                foreach ($itemsForDate as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $clientId = $product->client_id;
                    $mealTypeId = $item['meal_type_id'];
                    
                    $key = $clientId . '_' . $mealTypeId;

                    // Skip if already calculated this client+meal_type for this date
                    if (isset($calculatedClientsForDate[$key])) {
                        continue;
                    }

                    $client = User::find($clientId);
                    $mealType = MealType::find($mealTypeId);
                    
                    if (!$client || !$mealType) continue;

                    // Ensure client has valid address
                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    // Calculate distance
                    $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    // Get delivery charge
                    $deliveryCharge = MealDeliveryCharge::where('client_id', $clientId)
                        ->where('meal_type_id', $mealTypeId)
                        ->first();

                    if (!$deliveryCharge) continue;

                    // Determine charge based on distance
                    if ($distance <= 2) {
                        $charge = $deliveryCharge->inside_city_2km;
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                    }

                    // Add to client delivery fees
                    if (!isset($clientDeliveryFees[$clientId])) {
                        $clientDeliveryFees[$clientId] = 0;
                    }
                    $clientDeliveryFees[$clientId] += $charge;
                    
                    // Mark as calculated for this date
                    $calculatedClientsForDate[$key] = true;
                }
            }

            // Process meal items and calculate subtotals/taxes per client
            foreach ($mealOrders as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                $clientId = $product->client_id;

                // Initialize client data if not exists
                if (!isset($clientSubtotals[$clientId])) {
                    $clientSubtotals[$clientId] = 0;
                    $clientTaxes[$clientId] = 0;
                }

                // Add to subtotal and tax
                $clientSubtotals[$clientId] += $item['total_price'];
                $clientTaxes[$clientId] += $item['total_price'] * $taxRate;

                // Create order item
                $orderItem = MealOrderItem::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'meal_type_id' => $item['meal_type_id'],
                    'product_id' => $item['product_id'],
                    'meal_date' => $item['meal_date'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $allOrderItems[] = $orderItem;
            }

            // ✅ Create ClientMealOrder records with PROPER delivery fees
            foreach ($clientSubtotals as $clientId => $subtotal) {
                $deliveryFee = $clientDeliveryFees[$clientId] ?? 0;
                $tax = $clientTaxes[$clientId] ?? 0;
                $total = $subtotal + $tax + $deliveryFee;

                ClientMealOrder::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $total,
                    'paid_amount' => 0,
                    'payment_status' => 'due',
                ]);
            }

            DB::commit();

            // Load relationships for the response
            $mealOrder->load(['items', 'clientMealOrders', 'mealShippingAddress']);

            return response()->json([
                'status' => 'success',
                'message' => 'Cash meal order placed successfully!',
                'data' => [
                    'meal_order' => $mealOrder,
                    'order_items' => $allOrderItems,
                    'total_items' => count($allOrderItems),
                    'total_clients' => count($clientSubtotals),
                    'tax_rate_used' => $taxRate,
                    'delivery_calculation' => [
                        'calculated_total' => $calculatedDeliveryCharge,
                        'frontend_provided' => $request->delivery_charge,
                        'details_per_date' => $chargesPerDate,
                    ],
                ],
                'redirect_url' => '/user/meal-order'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Meal Order Cash Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to place cash meal order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate delivery charge based on distance between client address and customer shipping address
     * Using Google Distance Matrix API for accurate route-based calculation
     */
    private function calculateDistanceBasedDeliveryCharge($clientId, $mealTypeId, $customerShippingAddress)
    {
        try {
            // Get client from User model
            $client = User::where('id', $clientId)->where('role', 'client')->first();
            if (!$client) {
                \Log::warning("Client not found or not a client: {$clientId}");
                return [
                    'charge' => 5.00,
                    'distance' => null
                ];
            }

            // Ensure client has valid address
            if (!$client->city_id || !$client->address1 || !$client->zip_code) {
                \Log::warning("Client address incomplete for client: {$clientId}");
                return [
                    'charge' => 5.00,
                    'distance' => null
                ];
            }

            $clientAddress = [
                'city_id'  => $client->city_id,
                'address1' => $client->address1,
                'zip_code' => $client->zip_code,
            ];

            // Calculate distance using Google Distance Matrix API
            $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
            
            if ($distance === null) {
                \Log::warning("Distance calculation failed for client: {$clientId}");
                return [
                    'charge' => 5.00,
                    'distance' => null
                ];
            }

            // Get delivery charge for this client + meal type
            $deliveryCharge = MealDeliveryCharge::where('client_id', $client->id)
                ->where('meal_type_id', $mealTypeId)
                ->first();

            if (!$deliveryCharge) {
                \Log::info("No specific delivery charge found for client {$clientId}, meal type {$mealTypeId}, using default");
                $charge = $this->getDefaultDeliveryCharge($distance);
                return [
                    'charge' => $charge,
                    'distance' => $distance
                ];
            }

            // Determine charge based on distance
            if ($distance <= 2) {
                $charge = $deliveryCharge->inside_city_2km;
            } elseif ($distance <= 5) {
                $charge = $deliveryCharge->inside_city_5km;
            } elseif ($distance <= 10) {
                $charge = $deliveryCharge->inside_city_10km;
            } else {
                $charge = $deliveryCharge->inside_city_above_10km;
            }

            \Log::info("Delivery charge for client {$clientId}: \${$charge} for {$distance} km");

            return [
                'charge' => $charge,
                'distance' => $distance
            ];

        } catch (Exception $e) {
            \Log::error('Delivery charge calculation error: ' . $e->getMessage());
            return [
                'charge' => 5.00,
                'distance' => null
            ];
        }
    }

    /**
     * Calculate distance between locations using Google Distance Matrix API
     */
    private function getDistanceBetweenLocations($clientAddress, $shippingAddress)
    {
        // Google API key from config
        $apiKey = config('services.google_maps.api_key');

        if (!$apiKey) {
            \Log::error('Google Maps API key not configured');
            return null;
        }

        try {
            // Construct origin & destination with full addresses
            $origin = urlencode("{$clientAddress['address1']}, {$clientAddress['zip_code']}");
            $destination = urlencode("{$shippingAddress['address1']}, {$shippingAddress['zip_code']}");

            // Build URL for Google Distance Matrix API
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&mode=driving&units=metric&key={$apiKey}";

            $response = Http::timeout(10)->get($url);
            $data = $response->json();

            \Log::info("Distance API Response for client {$clientAddress['address1']} to customer {$shippingAddress['address1']}:", $data);

            if (isset($data['rows'][0]['elements'][0]['status']) && 
                $data['rows'][0]['elements'][0]['status'] === 'OK') {
                
                // Distance in meters
                $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
                // Convert to KM
                $distanceKm = $distanceMeters / 1000;
                
                \Log::info("Distance calculated: {$distanceKm} km");
                return round($distanceKm, 2);
            }

            \Log::warning("Distance API returned status: " . ($data['rows'][0]['elements'][0]['status'] ?? 'UNKNOWN'));
            return null;

        } catch (\Exception $e) {
            \Log::error("Distance calculation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get default delivery charge based on distance
     */
    private function getDefaultDeliveryCharge($distance)
    {
        if ($distance <= 2) {
            return 5.00;
        } elseif ($distance <= 5) {
            return 8.00;
        } elseif ($distance <= 10) {
            return 12.00;
        } else {
            return 15.00 + (ceil($distance - 10) * 2); // $2 per km after 10km
        }
    }

    /**
     * Get coordinates from address using Geocoding API
     * (Kept for backward compatibility if needed elsewhere)
     */
    private function getCoordinates($address)
    {
        try {
            // Use Google Maps Geocoding API or similar service
            $apiKey = config('services.google_maps.api_key');
            if (!$apiKey) {
                \Log::warning('Google Maps API key not configured');
                return null;
            }

            $address = urlencode($address);
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";
            
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if ($data['status'] === 'OK' && !empty($data['results'][0]['geometry']['location'])) {
                $coords = [
                    'lat' => $data['results'][0]['geometry']['location']['lat'],
                    'lng' => $data['results'][0]['geometry']['location']['lng']
                ];
                \Log::info("Coordinates found for address: {$address} - Lat: {$coords['lat']}, Lng: {$coords['lng']}");
                return $coords;
            }

            \Log::warning('Geocoding failed for address: ' . $address . ' - Status: ' . ($data['status'] ?? 'UNKNOWN'));
            return null;

        } catch (Exception $e) {
            \Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    public function storeByCredit(Request $request)
    {
        $request->validate([
            'meal_orders' => 'required|array|min:1',
            'meal_orders.*.meal_date' => 'required|date',
            'meal_orders.*.meal_type_id' => 'required|integer|exists:meal_types,id',
            'meal_orders.*.product_id' => 'required|integer|exists:products,id',
            'meal_orders.*.quantity' => 'required|integer|min:1',
            'meal_orders.*.unit_price' => 'required|numeric|min:0',
            'meal_orders.*.total_price' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'zip_code' => 'required|string|max:20',
            'country_id' => 'required|integer|exists:countries,id',
            'county_id' => 'required|integer|exists:counties,id',
            'city_id' => 'required|integer|exists:cities,id',
            'delivery_option' => 'required|in:self_pickup,courier',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'delivery_charge' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $customerId = $request->header('id');
            $mealOrders = $request->meal_orders;
            
            // Get customer with credit balance
            $customer = User::with('creditTransactions')->find($customerId);
            if (!$customer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Customer not found.',
                ], 404);
            }

            // Get TAX_RATE from config
            $taxRate = (float) config('services.tax_rate', 0.10);

            // Calculate overall totals
            $overallSubtotal = 0;
            foreach ($mealOrders as $item) {
                $overallSubtotal += $item['total_price'];
            }
            $overallTax = $overallSubtotal * $taxRate;

            // Generate order numbers
            $orderNumber = 'MO-' . Str::upper(Str::random(8)) . '-' . time();
            $invoiceNo = 'INV-' . Str::upper(Str::random(6)) . '-' . time();

            // ✅ Prepare customer shipping address for distance calculation
            $customerShippingAddress = [
                'city_id'  => $request->city_id,
                'address1' => $request->address1,
                'zip_code' => $request->zip_code,
            ];

            // ✅ FIXED: Use EXACT same grouping logic as MealCartController
            $totalCalculatedDeliveryCharge = 0;
            $chargesPerDate = [];
            
            // Group by meal_date first (EXACTLY like MealCartController)
            $mealOrdersByDate = [];
            foreach ($mealOrders as $item) {
                $date = $item['meal_date'];
                if (!isset($mealOrdersByDate[$date])) {
                    $mealOrdersByDate[$date] = [];
                }
                $mealOrdersByDate[$date][] = $item;
            }

            // Calculate delivery charges per date (EXACTLY like MealCartController)
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $chargesPerClientMealType = [];
                $dateCharge = 0;

                foreach ($itemsForDate as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $client = User::find($product->client_id);
                    $mealType = MealType::find($item['meal_type_id']);
                    
                    if (!$client || !$mealType) continue;

                    // Key: client_id + meal_type_id to avoid double counting (EXACTLY like MealCartController)
                    $key = $client->id . '_' . $mealType->id;

                    if (isset($chargesPerClientMealType[$key])) {
                        // Already counted this client + meal type for this date
                        continue;
                    }

                    // Ensure client has valid address
                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    // Calculate distance
                    $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    // Get delivery charge for this client + meal type
                    $deliveryCharge = MealDeliveryCharge::where('client_id', $client->id)
                        ->where('meal_type_id', $mealType->id)
                        ->first();

                    if (!$deliveryCharge) continue;

                    // Determine charge based on distance (EXACTLY like MealCartController)
                    if ($distance <= 2) {
                        $charge = $deliveryCharge->inside_city_2km;
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                    }

                    // Store charge for this client + meal type (counted once)
                    $chargesPerClientMealType[$key] = $charge;
                    $dateCharge += $charge;
                }

                $chargesPerDate[$date] = $dateCharge;
                $totalCalculatedDeliveryCharge += $dateCharge;
            }

            // ✅ Use the CALCULATED delivery charge instead of frontend provided charge
            $calculatedDeliveryCharge = $totalCalculatedDeliveryCharge;
            $overallTotal = $overallSubtotal + $overallTax + $calculatedDeliveryCharge;

            // Calculate customer's current credit balance
            $currentBalance = $this->calculateCustomerCreditBalance($customerId);

            // Check if customer has sufficient credit
            if ($currentBalance < $overallTotal) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Insufficient credit balance. Please add funds to your wallet.',
                    'data' => [
                        'current_balance' => $currentBalance,
                        'order_total' => $overallTotal,
                        'shortfall' => $overallTotal - $currentBalance
                    ]
                ], 400);
            }

            // Create main meal order with CALCULATED delivery charge
            $mealOrder = MealOrder::create([
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'invoice_no' => $invoiceNo,
                'status' => 'completed',
                'delivery_type' => $request->delivery_option,
                'delivery_fee' => $calculatedDeliveryCharge, // ✅ Use calculated charge
                'subtotal' => $overallSubtotal,
                'tax' => $overallTax,
                'payable_amount' => $overallTotal,
                'paid_amount' => $overallTotal, // Full amount paid from credit
                'payment_type' => 'courier',
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'currency' => 'USD',
                'transaction_id' => 'CREDIT-' . Str::upper(Str::random(10)),
            ]);

            // Create shipping address
            MealShippingAddress::create([
                'meal_order_id' => $mealOrder->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'zip_code' => $request->zip_code,
                'country_id' => $request->country_id,
                'county_id' => $request->county_id,
                'city_id' => $request->city_id,
                'latitude' => null,
                'longitude' => null,
            ]);

            $allOrderItems = [];

            // ✅ FIXED: Calculate client delivery fees using the SAME logic
            $clientDeliveryFees = [];
            $clientSubtotals = [];
            $clientTaxes = [];

            // Re-calculate delivery fees per client using the same grouping logic
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $calculatedClientsForDate = []; // Track which clients we've calculated for this date
                
                foreach ($itemsForDate as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $clientId = $product->client_id;
                    $mealTypeId = $item['meal_type_id'];
                    
                    $key = $clientId . '_' . $mealTypeId;

                    // Skip if already calculated this client+meal_type for this date
                    if (isset($calculatedClientsForDate[$key])) {
                        continue;
                    }

                    $client = User::find($clientId);
                    $mealType = MealType::find($mealTypeId);
                    
                    if (!$client || !$mealType) continue;

                    // Ensure client has valid address
                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    // Calculate distance
                    $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    // Get delivery charge
                    $deliveryCharge = MealDeliveryCharge::where('client_id', $clientId)
                        ->where('meal_type_id', $mealTypeId)
                        ->first();

                    if (!$deliveryCharge) continue;

                    // Determine charge based on distance
                    if ($distance <= 2) {
                        $charge = $deliveryCharge->inside_city_2km;
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                    }

                    // Add to client delivery fees
                    if (!isset($clientDeliveryFees[$clientId])) {
                        $clientDeliveryFees[$clientId] = 0;
                    }
                    $clientDeliveryFees[$clientId] += $charge;
                    
                    // Mark as calculated for this date
                    $calculatedClientsForDate[$key] = true;
                }
            }

            // Process meal items and calculate subtotals/taxes per client
            foreach ($mealOrders as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                $clientId = $product->client_id;

                // Initialize client data if not exists
                if (!isset($clientSubtotals[$clientId])) {
                    $clientSubtotals[$clientId] = 0;
                    $clientTaxes[$clientId] = 0;
                }

                // Add to subtotal and tax
                $clientSubtotals[$clientId] += $item['total_price'];
                $clientTaxes[$clientId] += $item['total_price'] * $taxRate;

                // Create order item
                $orderItem = MealOrderItem::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'meal_type_id' => $item['meal_type_id'],
                    'product_id' => $item['product_id'],
                    'meal_date' => $item['meal_date'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $allOrderItems[] = $orderItem;
            }

            // ✅ Create ClientMealOrder records with PROPER delivery fees
            foreach ($clientSubtotals as $clientId => $subtotal) {
                $deliveryFee = $clientDeliveryFees[$clientId] ?? 0;
                $tax = $clientTaxes[$clientId] ?? 0;
                $total = $subtotal + $tax + $deliveryFee;

                ClientMealOrder::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $total,
                    'paid_amount' => $total, // Full amount paid for this client
                    'payment_status' => 'paid',
                ]);
            }

            // ✅ Create credit transaction for the payment
            $newBalance = $currentBalance - $overallTotal;
            
            CreditTransaction::create([
                'customer_id' => $customerId,
                'type' => 'debit',
                'method' => 'credit',
                'amount' => $overallTotal,
                'balance_after' => $newBalance,
                'transaction_id' => $mealOrder->transaction_id,
                'currency' => 'USD',
                'description' => "Payment for meal order #{$orderNumber}",
            ]);

            DB::commit();

            // Load relationships for the response
            $mealOrder->load(['items', 'clientMealOrders', 'mealShippingAddress']);

            return response()->json([
                'status' => 'success',
                'message' => 'Credit meal order placed successfully!',
                'data' => [
                    'meal_order' => $mealOrder,
                    'order_items' => $allOrderItems,
                    'total_items' => count($allOrderItems),
                    'total_clients' => count($clientSubtotals),
                    'tax_rate_used' => $taxRate,
                    'credit_balance' => [
                        'previous_balance' => $currentBalance,
                        'amount_used' => $overallTotal,
                        'new_balance' => $newBalance,
                    ],
                    'delivery_calculation' => [
                        'calculated_total' => $calculatedDeliveryCharge,
                        'frontend_provided' => $request->delivery_charge,
                        'details_per_date' => $chargesPerDate,
                    ],
                ],
                'redirect_url' => '/user/meal-order'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Meal Order Credit Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to place credit meal order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate customer's current credit balance
     */
    private function calculateCustomerCreditBalance($customerId)
    {
        try {
            $creditTransactions = CreditTransaction::where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            return $creditTransactions ? $creditTransactions->balance_after : 0;
        } catch (Exception $e) {
            \Log::error('Credit balance calculation error: ' . $e->getMessage());
            return 0;
        }
    }

    public function index()
    {
        return view('frontend.pages.meal-order.index');
    }


    public function getMealOrders(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            $mealOrders = MealOrder::with(['customer', 'items.mealType', 'items.product.nutrient'])
                ->where('customer_id', $customer_id)
                ->latest()
                ->get();

            // Get all meal types for reference
            $allMealTypes = MealType::pluck('name')->toArray();

            $data = [];
            
            foreach ($mealOrders as $order) {
                // Group items by meal_date
                $groupedItems = $order->items->groupBy('meal_date');
                
                foreach ($groupedItems as $mealDate => $items) {
                    // Get meal types for this specific date
                    $mealTypesForDate = $items->map(function ($item) {
                        return $item->mealType->name ?? null;
                    })->filter()->unique()->values()->toArray();
                    
                    // Calculate calories for this specific date
                    $caloriesForDate = $items->sum(function ($item) {
                        $cal = $item->product->nutrient->calories ?? 0;
                        return $cal * $item->quantity;
                    });
                    
                    // Get remaining meal types for this date
                    $remainingMealTypes = array_diff($allMealTypes, $mealTypesForDate);
                    
                    $data[] = [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? $order->id,
                        'meal_date' => $mealDate,
                        'meal_types' => implode(', ', $mealTypesForDate),
                        'remaining_meal_types' => array_values($remainingMealTypes), // Ensure it's a proper array
                        'calories' => $caloriesForDate,
                        'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')) ?: '-',
                        'is_future_date' => $mealDate > Carbon::now()->format('Y-m-d') // Add future date flag
                    ];
                }
            }

            // Sort by meal date
            usort($data, function ($a, $b) {
                return strcmp($b['meal_date'], $a['meal_date']);
            });

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function view()
    {
        return view('frontend.pages.meal-order.view');
    }

    public function getMealOrderDetails($id)
    {
        try {
            $order = MealOrder::with([
                'items.mealType', 
                'items.product.nutrient',
                'items.client:id,firstName,lastName',
                'mealShippingAddress.country',
                'mealShippingAddress.county', 
                'mealShippingAddress.city'
            ])->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            // Group items by meal date and meal type
            $groupedItems = $order->items->groupBy(function($item) {
                return $item->meal_date;
            })->map(function($dayItems) {
                return $dayItems->groupBy(function($item) {
                    return $item->mealType->name ?? 'Other';
                });
            });

            // Total calories
            $totalCalories = $order->items->sum(function ($item) {
                return ($item->product->nutrient->calories ?? 0) * $item->quantity;
            });

            // Calories by meal type
            $caloriesByMealType = $order->items->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            })->map(function ($group) {
                return $group->sum(function ($item) {
                    return ($item->product->nutrient->calories ?? 0) * $item->quantity;
                });
            });

            // Calculate summary from order data
            $summary = [
                'subtotal' => floatval($order->subtotal ?? 0),
                'tax' => floatval($order->tax ?? 0),
                'delivery_fee' => floatval($order->delivery_fee ?? 0),
                'total' => floatval($order->payable_amount ?? 0),
                'total_items' => $order->items->sum('quantity')
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'summary' => $summary,
                    'meal_cart' => $groupedItems,
                    'nutrition' => [
                        'total_calories' => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewOrderDetailsByDate()
    {
        return view('frontend.pages.meal-order.view_order_details_by_date');
    }

    public function getMealOrderDetailsByDate($orderId, $date)
    {
        try {
            $order = MealOrder::with([
                'items' => function($query) use ($date) {
                    $query->where('meal_date', $date)
                          ->with(['mealType', 'product.nutrient', 'client:id,firstName,lastName,mobile,address1,address2,zip_code,city_id,county_id,country_id,latitude,longitude']);
                },
                'mealShippingAddress.country',
                'mealShippingAddress.county', 
                'mealShippingAddress.city'
            ])->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            // Group items by meal type for the specific date
            $groupedItems = $order->items->groupBy(function($item) {
                return $item->mealType->name ?? 'Other';
            });

            // Calculate nutrition for the specific date
            $totalCalories = $order->items->sum(function ($item) {
                return ($item->product->nutrient->calories ?? 0) * $item->quantity;
            });

            $caloriesByMealType = $order->items->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            })->map(function ($group) {
                return $group->sum(function ($item) {
                    return ($item->product->nutrient->calories ?? 0) * $item->quantity;
                });
            });

            // Calculate summary for the specific date
            $dateSubtotal = $order->items->sum('total_price');
            
            // Use tax rate from config with fallback to 20%
            $taxRate = (float) config('services.tax_rate', 0.20);
            $dateTax = $dateSubtotal * $taxRate;

            // Calculate delivery charge for this specific date
            $dateDeliveryCharge = $this->calculateDeliveryChargeForDate($order, $date);
            $dateTotal = $dateSubtotal + $dateTax + $dateDeliveryCharge;

            $summary = [
                'subtotal' => floatval($dateSubtotal),
                'tax' => floatval($dateTax),
                'tax_rate' => floatval($taxRate * 100), // Convert to percentage for display
                'delivery_charge' => floatval($dateDeliveryCharge),
                'total' => floatval($dateTotal),
                'total_items' => $order->items->sum('quantity')
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'selected_date' => $date,
                    'summary' => $summary,
                    'meal_cart' => $groupedItems,
                    'nutrition' => [
                        'total_calories' => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'delivery_calculation' => [
                        'calculated_charge' => $dateDeliveryCharge,
                        'calculation_method' => 'distance_based_per_client_meal_type'
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate delivery charge for a specific date in an order
     */
    private function calculateDeliveryChargeForDate($order, $date)
    {
        try {
            $totalDeliveryCharge = 0;
            
            // Get customer shipping address
            $shippingAddress = $order->mealShippingAddress;
            if (!$shippingAddress) {
                \Log::warning("No shipping address found for order: {$order->id}");
                return 0;
            }

            $customerShippingAddress = [
                'city_id'  => $shippingAddress->city_id,
                'address1' => $shippingAddress->address1,
                'zip_code' => $shippingAddress->zip_code,
                'latitude' => $shippingAddress->latitude,
                'longitude' => $shippingAddress->longitude,
            ];

            // Get items for this specific date
            $dateItems = $order->items->where('meal_date', $date);
            
            if ($dateItems->isEmpty()) {
                return 0;
            }

            // Group by client_id and meal_type_id to avoid duplicate charges for same client+meal_type on same date
            $clientMealTypeGroups = [];
            
            foreach ($dateItems as $item) {
                $clientId = $item->client_id;
                $mealTypeId = $item->meal_type_id;
                $key = $clientId . '_' . $mealTypeId;
                
                if (!isset($clientMealTypeGroups[$key])) {
                    $clientMealTypeGroups[$key] = [
                        'client_id' => $clientId,
                        'meal_type_id' => $mealTypeId,
                        'client' => $item->client,
                        'meal_type' => $item->mealType,
                        'items' => []
                    ];
                }
                $clientMealTypeGroups[$key]['items'][] = $item;
            }

            // Calculate delivery charge for each unique client+meal_type combination
            foreach ($clientMealTypeGroups as $group) {
                $client = $group['client'];
                $mealTypeId = $group['meal_type_id'];
                
                if (!$client) {
                    \Log::warning("Client not found for client_id: {$group['client_id']}");
                    continue;
                }

                // Ensure client has valid address
                if (!$client->city_id || !$client->address1 || !$client->zip_code) {
                    \Log::warning("Client address incomplete for client: {$client->id}");
                    continue;
                }

                $clientAddress = [
                    'city_id'  => $client->city_id,
                    'address1' => $client->address1,
                    'zip_code' => $client->zip_code,
                    'latitude' => $client->latitude,
                    'longitude' => $client->longitude,
                ];

                // Calculate distance
                $distance = $this->getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                
                if ($distance === null) {
                    \Log::warning("Distance calculation failed for client: {$client->id}");
                    // Use default charge
                    $charge = $this->getDefaultDeliveryCharge(5); // Default to 5km distance
                    $totalDeliveryCharge += $charge;
                    continue;
                }

                // Get delivery charge configuration for this client + meal type
                $deliveryChargeConfig = MealDeliveryCharge::where('client_id', $client->id)
                    ->where('meal_type_id', $mealTypeId)
                    ->first();

                if (!$deliveryChargeConfig) {
                    \Log::info("No specific delivery charge found for client {$client->id}, meal type {$mealTypeId}, using default");
                    $charge = $this->getDefaultDeliveryCharge($distance);
                    $totalDeliveryCharge += $charge;
                    continue;
                }

                // Determine charge based on distance (same logic as storeByCash)
                if ($distance <= 2) {
                    $charge = $deliveryChargeConfig->inside_city_2km;
                } elseif ($distance <= 5) {
                    $charge = $deliveryChargeConfig->inside_city_5km;
                } elseif ($distance <= 10) {
                    $charge = $deliveryChargeConfig->inside_city_10km;
                } else {
                    $charge = $deliveryChargeConfig->inside_city_above_10km;
                }

                $totalDeliveryCharge += $charge;

                \Log::info("Delivery charge for client {$client->id}, meal type {$mealTypeId} on {$date}: \${$charge} for {$distance} km");
            }

            \Log::info("Total delivery charge for order {$order->id}, date {$date}: \${$totalDeliveryCharge}");

            return $totalDeliveryCharge;

        } catch (Exception $e) {
            \Log::error('Delivery charge calculation error for date: ' . $e->getMessage());
            return 0;
        }
    }

    public function getDailyCalories(Request $request)
    {
        try {
            $customerId = $request->header('id');
            $range = $request->range ?? '7days';

            switch ($range) {
                case 'today':
                    $start = Carbon::today()->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    break;
                case 'yesterday':
                    $start = Carbon::yesterday()->startOfDay();
                    $end = Carbon::yesterday()->endOfDay();
                    break;
                case '7days':
                    $start = Carbon::now()->subDays(6)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
                case '30days':
                    $start = Carbon::now()->subDays(29)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
                case 'current_month':
                    $start = Carbon::now()->startOfMonth()->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
                case 'last_month':
                    $start = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                    $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                    break;
                default:
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Invalid range'
                    ], 400);
            }

            // Get meal order items with meal_date
            $orderItems = MealOrderItem::with(['product.nutrient', 'mealType'])
                ->whereHas('mealOrder', function($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->whereDate('meal_date', '>=', $start)
                ->whereDate('meal_date', '<=', $end)
                ->orderBy('meal_date', 'ASC')
                ->get();

            // Group by date and calculate totals
            $dailyTotals = $orderItems->groupBy(function ($item) {
                return Carbon::parse($item->meal_date)->format('Y-m-d');
            })->map(function ($itemsOnDay) {
                return $itemsOnDay->sum(function ($item) {
                    $cal = $item->product->nutrient->calories ?? 0;
                    return $cal * $item->quantity;
                });
            });

            // Meal type breakdown
            $mealTypeBreakdown = [];
            foreach ($orderItems as $item) {
                $date = Carbon::parse($item->meal_date)->format('Y-m-d');
                $mealType = $item->mealType->name ?? 'Other';
                $cal = ($item->product->nutrient->calories ?? 0) * $item->quantity;

                if (!isset($mealTypeBreakdown[$date])) {
                    $mealTypeBreakdown[$date] = [];
                }
                if (!isset($mealTypeBreakdown[$date][$mealType])) {
                    $mealTypeBreakdown[$date][$mealType] = 0;
                }
                $mealTypeBreakdown[$date][$mealType] += $cal;
            }

            $caloriesUnit = optional(optional($orderItems->first())->product->nutrient)->calories_unit ?? 'kcal';
            $totalCaloriesSum = $dailyTotals->sum();

            return response()->json([
                'status' => 'success',
                'dates' => $dailyTotals->keys()->values(),
                'calories' => $dailyTotals->values(),
                'calories_unit' => $caloriesUnit,
                'total_calories_sum' => $totalCaloriesSum,
                'meal_type_breakdown' => $mealTypeBreakdown,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDailyCalories111(Request $request)
    {
        try {
            $range = $request->range ?? '7days';

            switch ($range) {

                case 'today':
                    $start = Carbon::today()->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    break;

                case 'yesterday':
                    $start = Carbon::yesterday()->startOfDay();
                    $end = Carbon::yesterday()->endOfDay();
                    break;

                case '7days':
                    $start = Carbon::now()->subDays(6)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;

                case '30days':
                    $start = Carbon::now()->subDays(29)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;

                case 'current_month':
                    $start = Carbon::now()->startOfMonth()->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;

                case 'last_month':
                    $start = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                    $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                    break;

                default:
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Invalid range'
                    ], 400);
            }

            $orders = MealOrder::with('items.product')
                ->whereDate('meal_date', '>=', $start)
                ->whereDate('meal_date', '<=', $end)
                ->orderBy('meal_date', 'ASC')
                ->get();


            $caloriesUnit = optional(optional($orders->first()->items->first())->product->nutrient)->calories_unit ?? 'kcal';

            $dailyTotals = $orders->groupBy(function ($order) {
                return Carbon::parse($order->meal_date)->format('Y-m-d');
            })->map(function ($ordersOnDay) {
                return $ordersOnDay->sum(function ($order) {
                    return $order->items->sum(function ($item) {
                        $cal = $item->product->nutrient->calories ?? 0;
                        return $cal * $item->quantity;
                    });
                });
            });

            $mealTypeBreakdown = [];

            foreach ($orders as $order) {
                $date = Carbon::parse($order->meal_date)->format('Y-m-d');

                if (!isset($mealTypeBreakdown[$date])) {
                    $mealTypeBreakdown[$date] = [];
                }

                foreach ($order->items as $item) {
                    $mealType = $item->mealType->name ?? 'Other';
                    $cal = ($item->product->nutrient->calories ?? 0) * $item->quantity;

                    if (!isset($mealTypeBreakdown[$date][$mealType])) {
                        $mealTypeBreakdown[$date][$mealType] = 0;
                    }

                    $mealTypeBreakdown[$date][$mealType] += $cal;
                }
            }

            $totalCaloriesSum = $dailyTotals->sum();

            return response()->json([
                'status' => 'success',
                'dates' => $dailyTotals->keys()->values(),
                'calories' => $dailyTotals->values(),
                'calories_unit' => $caloriesUnit,
                'total_calories_sum' => $totalCaloriesSum,
                'meal_type_breakdown' => $mealTypeBreakdown,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteMealOrderItem($itemId)
    {
        try {
            $item = MealOrderItem::find($itemId);

            if (!$item) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order item not found.',
                ], 404);
            }

            $item->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal order item deleted successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteMealOrder($id)
    {
        DB::beginTransaction();

        try {
            $mealOrder = MealOrder::find($id);

            if (!$mealOrder) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            $mealOrder->items()->delete();
            $mealOrder->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal order and related items deleted successfully.',
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete meal order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
