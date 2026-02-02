<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;
use App\Models\MealDeliveryPaymentHistory;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use App\Models\User;


class ClientDeliveryPaymentController extends Controller
{
    public function index()
    {
        return view('client.pages.meal-delivery-payment.index');
    }

    public function getMealOrderPayments(Request $request)
    {
        try {
            $client_id = $request->header('id');

            $ledgers = DeliveryChargeLedger::with([
                    'mealOrder.customer',
                    'mealType',
                    'client',
                    'deliveryPerson',
                ])
                ->where('client_id', $client_id)
                ->whereNotNull('delivery_person_id')
                ->latest()
                ->get();

            $data = [];

            foreach ($ledgers as $index => $ledger) {

                $mealTypeName = $ledger->mealType->name ?? 'N/A';

                $data[] = [
                    'sl' => $index + 1,
                    'delivery_charge_ledger_id' => $ledger->id,
                    'order_number' => $ledger->mealOrder->order_number
                        ?? 'ORD-' . str_pad($ledger->mealOrder->id, 6, '0', STR_PAD_LEFT),

                    'meal_types' => [$mealTypeName],
                    'formatted_meal_types' => $mealTypeName,

                    'meal_date' => $ledger->delivery_date,
                    'delivery_charge' => number_format($ledger->delivery_charge, 2),
                    'payment_status' => $ledger->payment_status,
                    'delivery_status' => $ledger->delivery_status,

                    'delivery_person_id' => $ledger->delivery_person_id,
                    'delivery_person_name' => trim(
                        ($ledger->deliveryPerson->firstName ?? '') . ' ' .
                        ($ledger->deliveryPerson->lastName ?? '')
                    ),

                    'customer_name' => trim(
                        ($ledger->mealOrder->customer->firstName ?? '') . ' ' .
                        ($ledger->mealOrder->customer->lastName ?? '')
                    ),

                    'client_name' => trim(
                        ($ledger->client->firstName ?? '') . ' ' .
                        ($ledger->client->lastName ?? '')
                    ),
                ];
            }

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

    public function storeByStripe(Request $request)
    {
        $request->validate([
            'meal_orders' => 'required|array|min:1',
            'meal_orders.*.meal_date' => 'required|date',
            'meal_orders.*.meal_time' => 'nullable|string',
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

            // ✅ Group by meal_date
            $mealOrdersByDate = [];
            foreach ($mealOrders as $item) {
                $date = $item['meal_date'];
                if (!isset($mealOrdersByDate[$date])) {
                    $mealOrdersByDate[$date] = [];
                }
                $mealOrdersByDate[$date][] = $item;
            }

            $totalCalculatedDeliveryCharge = 0;
            $chargesPerDate = [];
            $deliveryLedgerData = [];

            // Calculate delivery charges per date
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
                        $category = 'inside_city_2km';
                    } elseif ($distance <= 5) {
                        $charge = $deliveryCharge->inside_city_5km;
                        $category = 'inside_city_5km';
                    } elseif ($distance <= 10) {
                        $charge = $deliveryCharge->inside_city_10km;
                        $category = 'inside_city_10km';
                    } else {
                        $charge = $deliveryCharge->inside_city_above_10km;
                        $category = 'inside_city_above_10km';
                    }

                    // Store charge for this client + meal type (counted once)
                    $chargesPerClientMealType[$key] = $charge;
                    $dateCharge += $charge;

                    // Store delivery ledger data
                    $deliveryLedgerData[] = [
                        'client_id' => $client->id,
                        'meal_type_id' => $mealType->id,
                        'delivery_date' => $date,
                        'delivery_charge' => $charge,
                        'distance_km' => $distance,
                        'distance_category' => $category,
                    ];
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

            // Calculate client delivery fees
            $clientDeliveryFees = [];
            $clientSubtotals = [];
            $clientTaxes = [];

            // Re-calculate delivery fees per client
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $calculatedClientsForDate = [];
                
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
                    
                    $calculatedClientsForDate[$key] = true;
                }
            }

            // Process meal items and calculate subtotals/taxes per client
            foreach ($mealOrders as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                $clientId = $product->client_id;
                $mealTypeId = $item['meal_type_id'];
                $mealDate = $item['meal_date'];
                $mealTime = $item['meal_time'] ?? null;

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
                    'meal_time' => $mealTime,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $allOrderItems[] = $orderItem;
            }

            // ✅ NOW Create ClientMealOrder records with PROPER delivery fees (after calculating subtotals/taxes)
            $clientMealOrders = [];
            foreach ($clientSubtotals as $clientId => $subtotal) {
                $deliveryFee = $clientDeliveryFees[$clientId] ?? 0;
                $tax = $clientTaxes[$clientId] ?? 0;
                $total = $subtotal + $tax + $deliveryFee;

                $clientMealOrder = ClientMealOrder::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $total,
                    'paid_amount' => $total, // Full amount paid for this client
                    'payment_status' => 'paid',
                ]);

                $clientMealOrders[] = $clientMealOrder;
            }

            // Create DeliveryChargeLedger records for each unique client+meal_type+date combination
            $deliveryLedgers = [];
            foreach ($deliveryLedgerData as $ledgerItem) {
                $chargeKey = DeliveryChargeLedger::generateChargeKey(
                    $mealOrder->id,
                    $ledgerItem['client_id'],
                    $ledgerItem['meal_type_id'],
                    $ledgerItem['delivery_date']
                );

                // Generate tracking number
                $trackingNumber = DeliveryChargeLedger::generateTrackingNumber();

                // Create delivery charge ledger record
                $deliveryLedger = DeliveryChargeLedger::create([
                    'meal_order_id' => $mealOrder->id,
                    'customer_id' => $customerId,
                    'client_id' => $ledgerItem['client_id'],
                    'delivery_person_id' => null,
                    'meal_type_id' => $ledgerItem['meal_type_id'],
                    'delivery_date' => $ledgerItem['delivery_date'],
                    'order_tracking' => $trackingNumber,
                    'delivery_charge' => $ledgerItem['delivery_charge'],
                    'distance_km' => $ledgerItem['distance_km'],
                    'distance_category' => $ledgerItem['distance_category'],
                    'payment_status' => 'due',
                    'is_charge_counted' => true,
                    'charge_key' => $chargeKey,
                ]);

                // Create initial status history
                MealDeliveryStatusHistory::create([
                    'delivery_charge_ledger_id' => $deliveryLedger->id,
                    'delivery_status' => 'pending',
                    'notes' => 'Order placed',
                    'updated_by_id' => $customerId,
                    'updated_by_type' => 'customer',
                ]);

                $deliveryLedgers[] = $deliveryLedger;
            }

            DB::commit();

            // Notifications
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new NewMealOrderNotification($mealOrder));
            }

            // Notify customer
            $customer = User::find($customerId);
            if ($customer) {
                $customer->notify(new NewMealOrderNotification($mealOrder));
            }

            // Notify each client using the created ClientMealOrder objects
            foreach ($clientMealOrders as $clientMealOrder) {
                $client = User::find($clientMealOrder->client_id);
                if ($client) {
                    // Pass the ClientMealOrder object directly
                    $client->notify(new NewMealOrderNotification($clientMealOrder));
                }
            }

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
                    'delivery_ledgers' => collect($deliveryLedgers)->map(function($ledger) {
                        return [
                            'id' => $ledger->id,
                            'tracking_number' => $ledger->order_tracking,
                            'client_id' => $ledger->client_id,
                            'meal_type_id' => $ledger->meal_type_id,
                            'delivery_date' => $ledger->delivery_date,
                            'delivery_status' => $ledger->delivery_status,
                            'delivery_charge' => $ledger->delivery_charge,
                        ];
                    }),
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
            \Log::error('Stripe meal order creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
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
            'ledger_id' => 'required|exists:delivery_charge_ledgers,id',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $ledger = DeliveryChargeLedger::findOrFail($request->ledger_id);
            $ledger->payment_status = 'paid';
            $ledger->save();

            $transactionId =  'CASH_' . strtoupper(Str::random(8)) . date('YmdHis');

            MealDeliveryPaymentHistory::create([
                'delivery_charge_ledger_id' => $ledger->id,
                'type' => 'credit', 
                'payment_method' => 'cash',
                'paid_amount' => $request->amount,
                'transaction_id' => $transactionId,
                'currency' => 'USD',
                'payment_notes' => 'Cash payment by client'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment recorded successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

}