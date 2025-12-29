<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
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
    public function store(Request $request)
    {
        $request->validate([
            'meal_date' => 'required|date|after:today',
            'meal_time' => 'nullable|date_format:H:i',
            'meal_type_id' => 'required|integer|exists:meal_types,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $customerId = $request->header('id');

            $product = Product::findOrFail($request->product_id);

            $unitPrice = $product->discount_price > 0 
                        ? $product->discount_price 
                        : $product->price;

            $totalPrice = $unitPrice * $request->quantity;

            // prevent duplicate in cart
            $exists = MealCart::where('customer_id', $customerId)
                ->where('meal_date', $request->meal_date)
                ->where('meal_type_id', $request->meal_type_id)
                ->where('product_id', $request->product_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Item already exists in cart.',
                ], 409);
            }

            MealCart::create([
                'meal_date' => $request->meal_date,
                'meal_time' => $request->meal_time, 
                'client_id' => $product->client_id,
                'customer_id' => $customerId,
                'meal_type_id' => $request->meal_type_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Item added to cart successfully!',
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function mealCart()
    {
        return view('frontend.pages.meal-cart.cart');
    }

    public function getMealCartData(Request $request)
    {
        try {
            $customerId = $request->header('id');

            // Eager load product and meal type
            $mealCart = MealCart::with(['product:id,name,image,price,discount_price', 'mealType:id,name','client:id,firstName,lastName'])
                ->where('customer_id', $customerId)
                ->orderBy('meal_date', 'asc')
                ->get()
                ->groupBy('meal_date'); 

            $summary = [
                'subtotal' => 0,
                'total_items' => 0,
            ];

            foreach ($mealCart as $day) {
                foreach ($day as $item) {
                    $summary['subtotal'] += $item->quantity * $item->unit_price;
                    $summary['total_items'] += $item->quantity;
                }
            }

            $taxRate = (float) config('services.tax_rate', 0.10);
            $tax = round($summary['subtotal'] * $taxRate, 2);
            $total = round($summary['subtotal'] + $tax, 2);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'meal_cart' => $mealCart->map(function($dayItems) {
                        return $dayItems->map(function($item) {
                            return [
                                'id' => $item->id,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price,
                                'meal_type' => $item->mealType,
                                'meal_time' => $item->meal_time, // Add meal_time here
                                'product' => $item->product,
                                'client' => $item->client ? [
                                    'firstName' => $item->client->firstName,
                                    'lastName' => $item->client->lastName
                                ] : null
                            ];
                        });
                    }),
                    'summary' => [
                        'subtotal' => $summary['subtotal'],
                        'tax' => $tax,
                        'total' => $total,
                        'total_items' => $summary['total_items']
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load meal cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateMealItem(Request $request)
    {
        $request->validate([
            'meal_item_id' => 'required|exists:meal_carts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = MealCart::find($request->meal_item_id);
        $item->quantity = $request->quantity;
        $item->total_price = $item->unit_price * $request->quantity;
        $item->save();

        return response()->json(['status' => 'success', 'message' => 'Meal item updated']);
    }

    public function removeMealItem(Request $request)
    {
        $request->validate([
            'meal_item_id' => 'required|exists:meal_carts,id',
        ]);

        $item = MealCart::find($request->meal_item_id);
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Meal item removed']);
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
            $count = MealCart::where('customer_id', $customer_id)->sum('quantity');

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

    public function checkoutPage()
    {
        return view('frontend.pages.meal-checkout.checkout-page');
    }

    public function getShippingAddressInfo(Request $request)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email header is required'
                ], 400);
            }

            $shippingAddresses = MealShippingAddress::with(['country', 'county', 'city'])
                ->where('email', $email)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Shipping addresses retrieved successfully',
                'data' => $shippingAddresses
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching shipping addresses: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve shipping addresses'
            ], 500);
        }
    }

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


    private function getDistanceBetweenLocations111($clientAddress, $shippingAddress)
    {
        // Google API key from .env
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        if (!$apiKey) {
            throw new \Exception('Google Maps API key not set in .env');
        }

        // Construct origin & destination strings
        $origin = urlencode($clientAddress['address1'] . ', ' . $clientAddress['zip_code']);
        $destination = urlencode($shippingAddress['address1'] . ', ' . $shippingAddress['zip_code']);

        // Build URL for Google Distance Matrix API
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&mode=driving&units=metric&key={$apiKey}";

        try {
            $response = Http::get($url);
            $data = $response->json();

            if (isset($data['rows'][0]['elements'][0]['status']) && $data['rows'][0]['elements'][0]['status'] === 'OK') {
                // Distance in meters
                $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
                // Convert to KM
                $distanceKm = $distanceMeters / 1000;
                return $distanceKm;
            }

            return null; // unable to calculate distance
        } catch (\Exception $e) {
            \Log::error("Distance calculation error: " . $e->getMessage());
            return null;
        }
    }
}
