    public function storeByCash(Request $request)
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

                // Find the corresponding delivery charge ledger for this item
                $deliveryLedger = collect($deliveryLedgers ?? [])->first(function ($ledger) use ($clientId, $mealTypeId, $mealDate) {
                    return $ledger->client_id == $clientId 
                        && $ledger->meal_type_id == $mealTypeId 
                        && $ledger->delivery_date == $mealDate;
                });

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
                    'delivery_charge_ledger_id' => $deliveryLedger ? $deliveryLedger->id : null,
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
                    'paid_amount' => 0,
                    'payment_status' => 'due',
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

            // Update meal order items with delivery charge ledger IDs
            foreach ($allOrderItems as $orderItem) {
                $deliveryLedger = collect($deliveryLedgers)->first(function ($ledger) use ($orderItem) {
                    return $ledger->client_id == $orderItem->client_id 
                        && $ledger->meal_type_id == $orderItem->meal_type_id 
                        && $ledger->delivery_date == $orderItem->meal_date;
                });

                if ($deliveryLedger) {
                    $orderItem->update([
                        'delivery_charge_ledger_id' => $deliveryLedger->id
                    ]);
                }
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
            $mealOrder->load(['items', 'clientMealOrders', 'mealShippingAddress', 'deliveryChargeLedgers']);

            return response()->json([
                'status' => 'success',
                'message' => 'Cash meal order placed successfully!',
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
                ],
                'redirect_url' => '/user/meal-order'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to place cash meal order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function storeByCredit(Request $request)
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

            // NEW: Store delivery charge ledger data
            $deliveryLedgerData = [];

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

            // NEW: Create DeliveryChargeLedger records for each unique client+meal_type+date combination
            $deliveryLedgers = [];
            foreach ($deliveryLedgerData as $ledgerItem) {
                $chargeKey = DeliveryChargeLedger::generateChargeKey(
                    $mealOrder->id,
                    $ledgerItem['client_id'],
                    $ledgerItem['meal_type_id'],
                    $ledgerItem['delivery_date']
                );

                // Create delivery charge ledger record
                $deliveryLedger = DeliveryChargeLedger::create([
                    'meal_order_id' => $mealOrder->id,
                    'customer_id' => $customerId,
                    'client_id' => $ledgerItem['client_id'],
                    'delivery_person_id' => null, // Will be assigned later
                    'meal_type_id' => $ledgerItem['meal_type_id'],
                    'delivery_date' => $ledgerItem['delivery_date'],
                    'delivery_charge' => $ledgerItem['delivery_charge'],
                    'distance_km' => $ledgerItem['distance_km'],
                    'distance_category' => $ledgerItem['distance_category'],
                    'payment_status' => 'due', // Delivery person payment is still due
                    'is_charge_counted' => true,
                    'charge_key' => $chargeKey,
                ]);

                $deliveryLedgers[] = $deliveryLedger;
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

                // Find the corresponding delivery charge ledger for this item
                $deliveryLedger = collect($deliveryLedgers)->first(function ($ledger) use ($clientId, $mealTypeId, $mealDate) {
                    return $ledger->client_id == $clientId 
                        && $ledger->meal_type_id == $mealTypeId 
                        && $ledger->delivery_date == $mealDate;
                });

                // Create order item with reference to delivery charge ledger
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
                    'delivery_charge_ledger_id' => $deliveryLedger ? $deliveryLedger->id : null,
                    'delivery_charge' => $deliveryLedger ? $deliveryLedger->delivery_charge : 0,
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

            // Notify customer
            $customer = User::find($customerId);
            if ($customer) {
                $customer->notify(new NewMealOrderNotification($mealOrder));
            }

            // Notify each client
            foreach ($mealOrder->clientMealOrders as $clientMealOrder) {
                $client = User::find($clientMealOrder->client_id);
                if ($client) {
                    $client->notify(new NewMealOrderNotification($clientMealOrder));
                }
            }

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
                    'delivery_ledgers_created' => count($deliveryLedgers),
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
            \Log::error('Credit meal order creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to place credit meal order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


in above code public function storeByCash(Request $request) is working fine. Now give me full updated code public function storeByCredit(Request $request) only.