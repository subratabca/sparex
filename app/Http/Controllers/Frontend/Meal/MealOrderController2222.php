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
use App\Models\User;
use Carbon\Carbon;
use Exception;

class MealOrderController extends Controller
{
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
            
            // Get TAX_RATE from config (default to 0.10 if not set)
            $taxRate = (float) config('services.tax_rate', 0.10);

            // Calculate overall totals for SINGLE order
            $overallSubtotal = 0;
            $overallTax = 0;
            
            foreach ($mealOrders as $item) {
                $overallSubtotal += $item['total_price'];
            }
            
            $overallTax = $overallSubtotal * $taxRate;
            $overallTotal = $overallSubtotal + $overallTax + $request->delivery_charge;

            // Generate order number and invoice number
            $orderNumber = 'MO-' . Str::upper(Str::random(8)) . '-' . time();
            $invoiceNo = 'INV-' . Str::upper(Str::random(6)) . '-' . time();

            // ✅ Create SINGLE meal order for ALL dates using Eloquent
            $mealOrder = MealOrder::create([
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'invoice_no' => $invoiceNo,
                'status' => 'pending',
                'delivery_type' => $request->delivery_option,
                'delivery_fee' => $request->delivery_charge, // ✅ Use frontend-calculated total delivery charge
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

            // ✅ Create shipping address for the SINGLE order
            $mealShippingAddress = MealShippingAddress::create([
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
                'latitude' => null, // No longer needed for distance calculation
                'longitude' => null, // No longer needed for distance calculation
            ]);

            $allOrderItems = [];
            $clientOrders = [];

            // ✅ Prepare customer shipping address for distance calculation
            $customerShippingAddress = [
                'city_id'  => $request->city_id,
                'address1' => $request->address1,
                'zip_code' => $request->zip_code,
            ];

            // ✅ Group items by client, meal_type, and date to calculate delivery charges
            $groupedItems = [];
            foreach ($mealOrders as $item) {
                $product = Product::findOrFail($item['product_id']);
                $mealType = MealType::findOrFail($item['meal_type_id']);
                
                $key = $product->client_id . '_' . $item['meal_type_id'] . '_' . $item['meal_date'];
                
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'client_id' => $product->client_id,
                        'meal_type_id' => $item['meal_type_id'],
                        'meal_date' => $item['meal_date'],
                        'items' => [],
                        'delivery_charge' => 0,
                        'distance' => null
                    ];
                }
                $groupedItems[$key]['items'][] = $item;
            }

            // ✅ Calculate delivery charges for each group using improved distance calculation
            foreach ($groupedItems as $key => &$group) {
                $deliveryResult = $this->calculateDistanceBasedDeliveryCharge(
                    $group['client_id'],
                    $group['meal_type_id'],
                    $customerShippingAddress
                );
                
                $group['delivery_charge'] = $deliveryResult['charge'];
                $group['distance'] = $deliveryResult['distance'];
            }

            // ✅ Process ALL meal items and track client orders
            foreach ($mealOrders as $item) {
                $product = Product::findOrFail($item['product_id']);
                $mealType = MealType::findOrFail($item['meal_type_id']);

                // ✅ Create MealOrderItem WITH meal_date
                $orderItem = MealOrderItem::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $product->client_id,
                    'meal_type_id' => $item['meal_type_id'],
                    'product_id' => $item['product_id'],
                    'meal_date' => $item['meal_date'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'status' => 'pending',
                ]);

                $allOrderItems[] = $orderItem;

                // Track client orders for ClientMealOrder creation
                if (!isset($clientOrders[$product->client_id])) {
                    $clientOrders[$product->client_id] = [
                        'subtotal' => 0,
                        'tax' => 0,
                        'delivery_fee' => 0,
                    ];
                }
                $clientOrders[$product->client_id]['subtotal'] += $item['total_price'];
                $clientOrders[$product->client_id]['tax'] += $item['total_price'] * $taxRate;
            }

            // ✅ Add delivery charges to client orders based on grouped items
            foreach ($groupedItems as $group) {
                $clientId = $group['client_id'];
                if (isset($clientOrders[$clientId])) {
                    $clientOrders[$clientId]['delivery_fee'] += $group['delivery_charge'];
                }
            }

            // ✅ Create ClientMealOrder records for each client with specific delivery charges
            foreach ($clientOrders as $clientId => $clientData) {
                $clientSubtotal = $clientData['subtotal'];
                $clientTax = $clientData['tax'];
                $clientDeliveryFee = $clientData['delivery_fee'];
                $clientTotal = $clientSubtotal + $clientTax + $clientDeliveryFee;

                ClientMealOrder::create([
                    'meal_order_id' => $mealOrder->id,
                    'client_id' => $clientId,
                    'subtotal' => $clientSubtotal,
                    'tax' => $clientTax,
                    'delivery_fee' => $clientDeliveryFee, // ✅ Client-specific delivery charge
                    'payable_amount' => $clientTotal,
                    'paid_amount' => 0,
                    'payment_status' => 'due',
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            // Load relationships for the response
            $mealOrder->load(['items', 'clientMealOrders', 'mealShippingAddress']);

            return response()->json([
                'status' => 'success',
                'message' => 'Cash meal order placed successfully! Payment will be collected on delivery.',
                'data' => [
                    'meal_order' => $mealOrder,
                    'order_items' => $allOrderItems,
                    'total_items' => count($allOrderItems),
                    'total_clients' => count($clientOrders),
                    'tax_rate_used' => $taxRate,
                    'delivery_breakdown' => [
                        'total_delivery_fee' => $request->delivery_charge,
                        'client_breakdown' => $groupedItems,
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





    public function storeByCash11(Request $request)
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
            
            // Group meal items by date
            $ordersByDate = [];
            foreach ($mealOrders as $mealOrder) {
                $mealDate = $mealOrder['meal_date'];
                if (!isset($ordersByDate[$mealDate])) {
                    $ordersByDate[$mealDate] = [];
                }
                $ordersByDate[$mealDate][] = $mealOrder;
            }

            $allOrderItems = [];
            $createdMealOrders = [];

            // Process each date group
            foreach ($ordersByDate as $mealDate => $dateItems) {
                // Calculate totals for this date
                $dateSubtotal = 0;
                $dateItemsCount = 0;
                
                foreach ($dateItems as $item) {
                    $dateSubtotal += $item['total_price'];
                    $dateItemsCount++;
                }
                
                $dateTax = $dateSubtotal * 0.1; // 10% tax
                $dateTotal = $dateSubtotal + $dateTax + $request->delivery_charge;

                // ✅ Create meal order for this date
                $mealOrder = MealOrder::create([
                    'customer_id' => $customerId,
                    'meal_date' => $mealDate,
                    'order_number' => 'MO-' . Str::upper(Str::random(8)) . '-' . time(),
                    'status' => 'pending',
                    'delivery_type' => $request->delivery_option,
                    'delivery_fee' => $request->delivery_charge,
                    'subtotal' => $dateSubtotal,
                    'tax' => $dateTax,
                    'payable_amount' => $dateTotal,
                    'paid_amount' => 0, // Cash payment - paid on delivery
                    'payment_type' => 'cash',
                    'payment_method' => 'cash',
                    'payment_status' => 'pending',
                    'currency' => 'USD',
                ]);

                $createdMealOrders[] = $mealOrder;

                // ✅ Create shipping address
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
                ]);

                $clientOrders = [];

                // ✅ Process each item in this date
                foreach ($dateItems as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $mealType = MealType::findOrFail($item['meal_type_id']);

                    // ✅ Create MealOrderItem
                    $orderItem = MealOrderItem::create([
                        'meal_order_id' => $mealOrder->id,
                        'client_id' => $product->client_id,
                        'meal_type_id' => $item['meal_type_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['total_price'],
                        'status' => 'pending',
                    ]);

                    $allOrderItems[] = $orderItem;

                    // Track client orders for ClientMealOrder creation
                    if (!isset($clientOrders[$product->client_id])) {
                        $clientOrders[$product->client_id] = [
                            'subtotal' => 0,
                            'tax' => 0,
                        ];
                    }
                    $clientOrders[$product->client_id]['subtotal'] += $item['total_price'];
                    $clientOrders[$product->client_id]['tax'] += $item['total_price'] * 0.1;
                }

                // ✅ Create ClientMealOrder records for each client
                foreach ($clientOrders as $clientId => $clientData) {
                    $clientSubtotal = $clientData['subtotal'];
                    $clientTax = $clientData['tax'];
                    $clientTotal = $clientSubtotal + $clientTax;

                    ClientMealOrder::create([
                        'meal_order_id' => $mealOrder->id,
                        'client_id' => $clientId,
                        'subtotal' => $clientSubtotal,
                        'tax' => $clientTax,
                        'delivery_fee' => $request->delivery_charge,
                        'payable_amount' => $clientTotal,
                        'paid_amount' => 0,
                        'payment_status' => 'due',
                        'status' => 'pending',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Cash meal order placed successfully! Payment will be collected on delivery.',
                'data' => [
                    'meal_orders' => $createdMealOrders,
                    'order_items' => $allOrderItems,
                    'total_orders' => count($createdMealOrders),
                    'total_items' => count($allOrderItems),
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

    public function index()
    {
        return view('frontend.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            $mealOrders = MealOrder::with(['customer', 'items.mealType'])
                ->where('customer_id', $customer_id)
                ->latest()
                ->get();

            $data = $mealOrders->map(function ($order) {
                $mealTypes = $order->items
                    ->map(fn($item) => $item->mealType->name ?? null)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                // Total calories for this meal_date
                $totalCalories = $order->items->sum(function ($item) {
                    $cal = $item->product->nutrient->calories ?? 0;
                    return $cal * $item->quantity;
                });

                return [
                    'id' => $order->id,
                    'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')) ?: '-',
                    'meal_date' => $order->meal_date,
                    'meal_types' => $mealTypes ?: '-', 
                    'calories' => $totalCalories,  
                ];
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
            $order = MealOrder::with(['items.mealType', 'items.product'])->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            // Total calories
            $totalCalories = $order->items->sum(function ($item) {
                return ($item->product->calories ?? 0) * $item->quantity;
            });

            // Calories by meal type
            $caloriesByMealType = $order->items->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            })->map(function ($group) {
                return $group->sum(function ($item) {
                    return ($item->product->calories ?? 0) * $item->quantity;
                });
            });

            return response()->json([
                'status' => 'success',
                'order' => $order,
                'total_calories' => $totalCalories,
                'calories_by_meal_type' => $caloriesByMealType,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDailyCalories(Request $request)
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
