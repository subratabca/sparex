<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\MealCart;
use App\Models\Product;
use App\Models\MealType;
use App\Models\User;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use Carbon\Carbon;
use Exception;

class MealCartController extends Controller
{
    public function getMealCourierCharge(Request $request)
    {
        $email = $request->header('email'); 

        if (!$email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email header missing'
            ], 422);
        }

        $customer = User::where('email', $email)->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid customer'
            ], 404);
        }

        // Validate required shipping address fields
        $request->validate([
            'city_id'   => 'required|integer',
            'address1'  => 'required|string',
            'zip_code'  => 'required|string',
        ]);

        $shippingAddress = [
            'city_id'  => $request->city_id,
            'address1' => $request->address1,
            'zip_code' => $request->zip_code,
        ];

        // Fetch cart items grouped by meal_date
        $cartItems = MealCart::with('client', 'mealType')
            ->where('customer_id', $customer->id)
            ->orderBy('meal_date')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Meal cart is empty'
            ], 404);
        }

        $totalCharge = 0;
        $chargesPerDate = [];

        // Group cart items by meal_date
        $cartItemsByDate = $cartItems->groupBy('meal_date');

        foreach ($cartItemsByDate as $date => $itemsForDate) {
            $chargesPerClientMealType = [];

            foreach ($itemsForDate as $item) {
                $client = $item->client;
                $mealType = $item->mealType;

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

                // Calculate distance (for now using fixed 5km as in your example)
                $distance = $this->getDistanceBetweenLocations($clientAddress, $shippingAddress);
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
            }

            // Sum charges for this date
            $dateCharge = array_sum($chargesPerClientMealType);
            $chargesPerDate[$date] = $dateCharge;
            $totalCharge += $dateCharge;
        }

        if ($totalCharge <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to calculate courier charge'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'total_charge' => $totalCharge,
            'details' => $chargesPerDate
        ]);
    }


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

            \Log::info("Distance API Response:", $data);

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
}


from above code (showing correct delivery_fee) and below code both will not given same delivery charge.In MealOrder model delivery_fee is showing 67 (summation of all clients delivery_fee).But in ClientMealOrder it is showing 21 for client one and 48 for client three so total delivery is 69 which is not equal to MealOrder model delivery_fee.Fix it and give me full updated  public function storeByCash(Request $request) so i can copy paste.

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