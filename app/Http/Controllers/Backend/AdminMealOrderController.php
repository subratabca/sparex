<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use App\Models\Product;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\CreditTransaction;
use App\Models\User;


class AdminMealOrderController extends Controller
{
    public function index()
    {
        return view('backend.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $mealOrders = MealOrder::with([
                    'customer',
                    'items.client',
                    'items.mealType',
                    'items.product'
                ])
                ->latest()
                ->get();

            $data = [];
            $orderGroups = [];
            $groupIndex = 0;
            
            foreach ($mealOrders as $order) {
                // Group items by meal_date
                $groupedItems = $order->items->groupBy('meal_date');
                
                $rowspan = count($groupedItems);
                $firstRow = true;
                
                foreach ($groupedItems as $mealDate => $items) {
                    // Calculate billed amount for this specific date
                    $billedAmountForDate = $items->sum('total_price');
                    
                    // Get meal types for this specific date
                    $mealTypesForDate = $items->map(function ($item) {
                        return $item->mealType->name ?? null;
                    })->filter()->unique()->values()->toArray();
                    
                    $data[] = [
                        'group_index' => $groupIndex,
                        'rowspan' => $rowspan,
                        'is_first_row' => $firstRow,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                        'meal_date' => $mealDate,
                        'meal_types' => $mealTypesForDate,
                        'formatted_meal_types' => implode(', ', $mealTypesForDate),
                        'billed_amount' => number_format($billedAmountForDate, 2),
                        'billed_amount_raw' => $billedAmountForDate,
                        'payment_status' => $order->payment_status ?? 'pending',
                        'payment_status_badge' => $this->getPaymentStatusBadge($order->payment_status),
                        'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')),
                        'customer_email' => $order->customer->email ?? '',
                        'total_items' => $items->count(),
                        'order_created_at' => $order->created_at->format('Y-m-d H:i:s')
                    ];
                    
                    $firstRow = false;
                }
                
                $groupIndex++;
            }

            // Sort by meal date descending within each order group
            $sortedData = [];
            $currentGroup = -1;
            $groupItems = [];
            
            foreach ($data as $item) {
                if ($item['group_index'] != $currentGroup) {
                    if (!empty($groupItems)) {
                        // Sort current group by date descending
                        usort($groupItems, function ($a, $b) {
                            return strcmp($b['meal_date'], $a['meal_date']);
                        });
                        $sortedData = array_merge($sortedData, $groupItems);
                    }
                    $currentGroup = $item['group_index'];
                    $groupItems = [$item];
                } else {
                    $groupItems[] = $item;
                }
            }
            
            // Sort last group
            if (!empty($groupItems)) {
                usort($groupItems, function ($a, $b) {
                    return strcmp($b['meal_date'], $a['meal_date']);
                });
                $sortedData = array_merge($sortedData, $groupItems);
            }

            // Add sequential SL numbers
            foreach ($sortedData as $index => &$item) {
                $item['sl'] = $index + 1;
            }

            return response()->json([
                'status' => 'success',
                'data' => $sortedData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getPaymentStatusBadge($status)
    {
        $badges = [
            'paid' => '<span class="badge bg-success">Paid</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'refunded' => '<span class="badge bg-info">Refunded</span>',
            'partially_paid' => '<span class="badge bg-primary">Partially Paid</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    public function getMealOrders111(Request $request)
    {
        try {
            $mealOrders = MealOrder::with([
                    'customer',
                    'items.mealType',
                ])
                ->latest()
                ->get();

            $data = $mealOrders->map(function ($order) {
                $mealTypes = $order->items
                    ->pluck('mealType.name')
                    ->unique()
                    ->implode(', ');

                $subtotal = $order->clientMealOrders->sum('subtotal');
                $tax = $order->clientMealOrders->sum('tax');
                $payable = $order->clientMealOrders->sum('payable_amount');

                return [
                    'id' => $order->id,
                    'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')) ?: '-',
                    'meal_date' => $order->meal_date,
                    'meal_types' => $mealTypes ?: '-',
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

    public function view(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', $email)->first();

        $notification_id = $request->query('notification_id');
        if ($notification_id) {
            $notification = $user->notifications()->where('id', $notification_id)->first();

            if ($notification && is_null($notification->read_at)) {
                $notification->markAsRead();
            }
        }
        
        return view('backend.pages.meal-order.view');
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

    public function getMealOrderDetails111(Request $request, $meal_order_id)
    {
        try {
            $order = MealOrder::with([
                'customer',
                'items.mealType',
                'items.product.client',
                'clientMealOrders.client'
            ])->find($meal_order_id);

            if (!$order || $order->items->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found or has no items.',
                ], 404);
            }

            $itemsGrouped = $order->items->groupBy('meal_type_id');

            $meals = [];
            foreach ($itemsGrouped as $meal_type_id => $items) {
                $mealTypeName = $items->first()->mealType->name ?? 'N/A';

                $products = $items->map(function($item) {
                $client = $item->product->client ?? null;
                $clientName = $client 
                    ? trim($client->firstName . ' ' . ($client->lastName ?? ''))
                    : 'N/A';

                    return [
                        'name' => $item->product->name ?? 'N/A',
                        'image' => $item->product->image ? asset('upload/product/medium/' . $item->product->image) : asset('upload/no_image.jpg'),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'client_name' => $clientName, 
                    ];
                });

                $meals[] = [
                    'meal_type_name' => $mealTypeName,
                    'products' => $products,
                ];
            }

            $subtotal = $order->clientMealOrders->sum('subtotal');
            $tax = $order->clientMealOrders->sum('tax');
            $payable_amount = $order->clientMealOrders->sum('payable_amount');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'meal_date' => $order->meal_date,
                    'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'payable_amount' => $payable_amount,
                    'meals' => $meals,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewOrderDetailsByDate(Request $request)
    {
        return view('backend.pages.meal-order.view_order_details_by_date');
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