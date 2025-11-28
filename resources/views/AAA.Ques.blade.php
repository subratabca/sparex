<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
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

            // ✅ Get customer shipping address coordinates from request
            $shippingAddress = $request->address1 . ' ' . $request->zip_code;
            $customerCoords = $this->getCoordinates($shippingAddress);

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
                'latitude' => $customerCoords['lat'] ?? null,
                'longitude' => $customerCoords['lng'] ?? null,
            ]);

            $allOrderItems = [];
            $clientOrders = [];

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
                        'delivery_charge' => 0
                    ];
                }
                $groupedItems[$key]['items'][] = $item;
            }

            // ✅ Calculate delivery charges for each group based on distance
            foreach ($groupedItems as $key => &$group) {
                $deliveryCharge = $this->calculateDistanceBasedDeliveryCharge(
                    $group['client_id'],
                    $group['meal_type_id'],
                    $customerCoords
                );
                $group['delivery_charge'] = $deliveryCharge;
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
     */
    private function calculateDistanceBasedDeliveryCharge($clientId, $mealTypeId, $customerCoords)
    {
        try {
            // Get client coordinates from User model (client address)
            $client = User::where('id', $clientId)->where('role', 'client')->first();
            if (!$client) {
                \Log::warning("Client not found or not a client: {$clientId}");
                return 5.00; // Default charge
            }

            // Get client address coordinates
            $clientAddress = $client->address1 . ' ' . $client->zip_code;
            $clientCoords = $this->getCoordinates($clientAddress);
            
            if (!$clientCoords) {
                \Log::warning("Could not get coordinates for client address: {$clientAddress}");
                return 5.00; // Default charge if coordinates not available
            }

            if (!$customerCoords) {
                \Log::warning("Customer coordinates not available");
                return 5.00; // Default charge if customer coordinates not available
            }

            // Calculate distance in kilometers between client and customer shipping address
            $distance = $this->calculateHaversineDistance(
                $clientCoords['lat'], 
                $clientCoords['lng'],
                $customerCoords['lat'], 
                $customerCoords['lng']
            );

            \Log::info("Distance between client {$clientId} and customer: {$distance} km");

            // Get delivery charges for this client and meal type
            $deliveryCharge = MealDeliveryCharge::where('client_id', $clientId)
                ->where('meal_type_id', $mealTypeId)
                ->first();

            if (!$deliveryCharge) {
                // Fallback to default distance-based charges
                \Log::info("No specific delivery charge found for client {$clientId}, meal type {$mealTypeId}, using default");
                return $this->getDefaultDeliveryCharge($distance);
            }

            // Apply charges based on distance brackets from MealDeliveryCharge
            $charge = 0;
            if ($distance <= 2) {
                $charge = $deliveryCharge->inside_city_2km ?? 5.00;
            } elseif ($distance <= 5) {
                $charge = $deliveryCharge->inside_city_5km ?? 8.00;
            } elseif ($distance <= 10) {
                $charge = $deliveryCharge->inside_city_10km ?? 12.00;
            } else {
                $charge = $deliveryCharge->inside_city_above_10km ?? 15.00;
            }

            \Log::info("Delivery charge for client {$clientId}: \${$charge} for {$distance} km");
            return $charge;

        } catch (Exception $e) {
            \Log::error('Delivery charge calculation error: ' . $e->getMessage());
            return 5.00; // Default fallback charge
        }
    }

    /**
     * Get coordinates from address using Geocoding API
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

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return round($angle * $earthRadius, 2); // Round to 2 decimal places
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

}


in above code i have used london address for both shipping address and client address. and i browse from dhaka,bangladesh.It will give proper distance.

