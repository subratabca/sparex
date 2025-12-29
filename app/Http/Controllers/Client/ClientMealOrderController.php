<?php
namespace App\Http\Controllers\Client;

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


class ClientMealOrderController extends Controller
{
    public function index()
    {
        return view('client.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $client_id = $request->header('id');

            // Get meal orders where the client has order items
            $mealOrders = MealOrder::whereHas('items', function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->with([
                    'customer',
                    'items' => function($query) use ($client_id) {
                        $query->where('client_id', $client_id)
                              ->with(['client', 'mealType', 'product']);
                    }
                ])
                ->latest()
                ->get();

            $data = [];
            $orderGroups = [];
            $groupIndex = 0;
            
            foreach ($mealOrders as $order) {
                // Filter items to only include this client's items
                $clientItems = $order->items->filter(function($item) use ($client_id) {
                    return $item->client_id == $client_id;
                });
                
                if ($clientItems->isEmpty()) {
                    continue; // Skip if no items for this client
                }
                
                // Group items by meal_date
                $groupedItems = $clientItems->groupBy('meal_date');
                
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
                        'delivery_status' => $order->delivery_status ?? 'pending',
                        'payment_status_badge' => $this->getPaymentStatusBadge($order->payment_status),
                        'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')),
                        'customer_email' => $order->customer->email ?? '',
                        'total_items' => $items->count(),
                        'order_created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'client_id' => $client_id
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

        return view('client.pages.meal-order.view');
    }

    public function getMealOrderDetails(Request $request,$order_id)
    {
        try {

            $client_id = $request->header('id');
            $client = User::where('id', $client_id)
                ->first();
            
            if (!$client) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client not found.'
                ], 404);
            }

            // Verify the order exists
            $order = MealOrder::find($order_id);
            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found.'
                ], 404);
            }

            // Get the specific client meal order for this client and order
            $clientMealOrder = ClientMealOrder::with([
                'mealOrder:id,order_number,invoice_no,status,created_at,delivery_fee,subtotal,tax,payable_amount,payment_status',
                'client:id,firstName,lastName,email,mobile,image',
            ])
            ->where('client_id', $client_id)
            ->where('meal_order_id', $order_id)
            ->first();

            // Get meal order items for this client and order
            $mealOrderItems = MealOrderItem::with([
                'mealType:id,name',
                'product:id,name,image,price',
                'client:id,firstName,lastName'
            ])
            ->where('client_id', $client_id)
            ->where('meal_order_id', $order_id)
            ->get();

            if ($mealOrderItems->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No items found for this client in the specified order.'
                ], 404);
            }

            // If no ClientMealOrder exists, create a virtual one from the items
            if (!$clientMealOrder) {
                // Calculate totals from items
                $subtotal = $mealOrderItems->sum('total_price');
                $deliveryFee = 0;
                $tax = 0;
                $payableAmount = $subtotal + $deliveryFee + $tax;

                $clientMealOrder = (object) [
                    'id' => null,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $payableAmount,
                    'paid_amount' => 0,
                    'payment_status' => 'pending',
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'mealOrder' => $order,
                    'client' => $client,
                    'mealOrderItems' => $mealOrderItems
                ];
            } else {
                // Load items for existing ClientMealOrder
                $clientMealOrder->mealOrderItems = $mealOrderItems;
            }

            // Group items by meal_date and meal_type
            $groupedItems = [];
            $totalAmount = 0;
            
            foreach ($clientMealOrder->mealOrderItems as $item) {
                $mealDate = $item->meal_date;
                $mealTypeName = $item->mealType ? $item->mealType->name : 'Other';
                
                if (!isset($groupedItems[$mealDate])) {
                    $groupedItems[$mealDate] = [];
                }
                
                if (!isset($groupedItems[$mealDate][$mealTypeName])) {
                    $groupedItems[$mealDate][$mealTypeName] = [];
                }
                
                $groupedItems[$mealDate][$mealTypeName][] = [
                    'id' => $item->id,
                    'product_name' => $item->product ? $item->product->name : 'N/A',
                    'product_image' => $item->product ? $item->product->image : null,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'meal_type_id' => $item->meal_type_id,
                    'meal_type_name' => $mealTypeName,
                    'client_name' => $item->client ? $item->client->firstName . ' ' . $item->client->lastName : 'N/A'
                ];
                
                $totalAmount += $item->total_price;
            }

            // Get all meal types for this client's items
            $mealTypeIds = $mealOrderItems->pluck('meal_type_id')->filter()->unique()->values();
            $mealTypes = MealType::whereIn('id', $mealTypeIds)->get(['id', 'name']);

            // Prepare the response data
            $data = [
                'client_meal_order' => [
                    'id' => $clientMealOrder->id,
                    'subtotal' => $clientMealOrder->subtotal,
                    'tax' => $clientMealOrder->tax,
                    'delivery_fee' => $clientMealOrder->delivery_fee,
                    'payable_amount' => $clientMealOrder->payable_amount,
                    'paid_amount' => $clientMealOrder->paid_amount,
                    'payment_status' => $clientMealOrder->payment_status,
                    'created_at' => $clientMealOrder->created_at ? $clientMealOrder->created_at->format('d M Y H:i:s') : null,
                    'updated_at' => $clientMealOrder->updated_at ? $clientMealOrder->updated_at->format('d M Y H:i:s') : null,
                ],
                'order' => [
                    'order_number' => $clientMealOrder->mealOrder->order_number,
                    'invoice_no' => $clientMealOrder->mealOrder->invoice_no,
                    'status' => $clientMealOrder->mealOrder->status,
                    'created_at' => $clientMealOrder->mealOrder->created_at->format('d M Y H:i:s'),
                    'payment_status' => $clientMealOrder->mealOrder->payment_status,
                ],
                'client' => [
                    'id' => $clientMealOrder->client->id,
                    'name' => $clientMealOrder->client->firstName . ' ' . $clientMealOrder->client->lastName,
                    'email' => $clientMealOrder->client->email,
                    'mobile' => $clientMealOrder->client->mobile,
                    'image' => $clientMealOrder->client->image,
                ],
                'customer' => [
                    'id' => $order->customer->id ?? null,
                    'name' => $order->customer ? ucwords(strtolower(trim($order->customer->firstName . ' ' . $order->customer->lastName))) : null,
                    'email' => $order->customer->email ?? null,
                    'mobile' => $order->customer->mobile ?? null,
                    'image' => $order->customer->image ?? null,
                ],
                'items' => $groupedItems,
                'summary' => [
                    'total_items' => $clientMealOrder->mealOrderItems->count(),
                    'total_dates' => count($groupedItems),
                    'total_meal_types' => $mealTypes->count(),
                    'subtotal' => $clientMealOrder->subtotal,
                    'tax' => $clientMealOrder->tax,
                    'delivery_fee' => $clientMealOrder->delivery_fee,
                    'total_amount' => $clientMealOrder->payable_amount,
                    'paid_amount' => $clientMealOrder->paid_amount,
                    'balance_amount' => $clientMealOrder->payable_amount - $clientMealOrder->paid_amount,
                ],
                'meal_types' => $mealTypes,
                'dates' => array_keys($groupedItems),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving payment details.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    // public function mealOrderDetailsByClient(Request $request)
    // {
    //     return view('client.pages.meal-order.meal-order-details-by-client');
    // }

    // public function getMealOrderDetailsByClient(Request $request, $client_id, $order_id)
    // {
    //     try {
    //         $client_id = $request->header('id');

    //         $client = User::where('id', $client_id)
    //             ->first();
            
    //         if (!$client) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Client not found.'
    //             ], 404);
    //         }

    //         // Verify the order exists
    //         $order = MealOrder::find($order_id);
    //         if (!$order) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Order not found.'
    //             ], 404);
    //         }

    //         // Get the specific client meal order for this client and order
    //         $clientMealOrder = ClientMealOrder::with([
    //             'mealOrder:id,order_number,invoice_no,status,created_at,delivery_fee,subtotal,tax,payable_amount,payment_status',
    //             'client:id,firstName,lastName,email,mobile,image',
    //         ])
    //         ->where('client_id', $client_id)
    //         ->where('meal_order_id', $order_id)
    //         ->first();

    //         // Get meal order items for this client and order
    //         $mealOrderItems = MealOrderItem::with([
    //             'mealType:id,name',
    //             'product:id,name,image,price',
    //             'client:id,firstName,lastName'
    //         ])
    //         ->where('client_id', $client_id)
    //         ->where('meal_order_id', $order_id)
    //         ->get();

    //         if ($mealOrderItems->isEmpty()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'No items found for this client in the specified order.'
    //             ], 404);
    //         }

    //         // If no ClientMealOrder exists, create a virtual one from the items
    //         if (!$clientMealOrder) {
    //             // Calculate totals from items
    //             $subtotal = $mealOrderItems->sum('total_price');
    //             $deliveryFee = 0;
    //             $tax = 0;
    //             $payableAmount = $subtotal + $deliveryFee + $tax;

    //             $clientMealOrder = (object) [
    //                 'id' => null,
    //                 'subtotal' => $subtotal,
    //                 'tax' => $tax,
    //                 'delivery_fee' => $deliveryFee,
    //                 'payable_amount' => $payableAmount,
    //                 'paid_amount' => 0,
    //                 'payment_status' => 'pending',
    //                 'created_at' => $order->created_at,
    //                 'updated_at' => $order->updated_at,
    //                 'mealOrder' => $order,
    //                 'client' => $client,
    //                 'mealOrderItems' => $mealOrderItems
    //             ];
    //         } else {
    //             // Load items for existing ClientMealOrder
    //             $clientMealOrder->mealOrderItems = $mealOrderItems;
    //         }

    //         // Group items by meal_date and meal_type
    //         $groupedItems = [];
    //         $totalAmount = 0;
            
    //         foreach ($clientMealOrder->mealOrderItems as $item) {
    //             $mealDate = $item->meal_date;
    //             $mealTypeName = $item->mealType ? $item->mealType->name : 'Other';
                
    //             if (!isset($groupedItems[$mealDate])) {
    //                 $groupedItems[$mealDate] = [];
    //             }
                
    //             if (!isset($groupedItems[$mealDate][$mealTypeName])) {
    //                 $groupedItems[$mealDate][$mealTypeName] = [];
    //             }
                
    //             $groupedItems[$mealDate][$mealTypeName][] = [
    //                 'id' => $item->id,
    //                 'product_name' => $item->product ? $item->product->name : 'N/A',
    //                 'product_image' => $item->product ? $item->product->image : null,
    //                 'unit_price' => $item->unit_price,
    //                 'quantity' => $item->quantity,
    //                 'total_price' => $item->total_price,
    //                 'meal_type_id' => $item->meal_type_id,
    //                 'meal_type_name' => $mealTypeName,
    //                 'client_name' => $item->client ? $item->client->firstName . ' ' . $item->client->lastName : 'N/A'
    //             ];
                
    //             $totalAmount += $item->total_price;
    //         }

    //         // Get all meal types for this client's items
    //         $mealTypeIds = $mealOrderItems->pluck('meal_type_id')->filter()->unique()->values();
    //         $mealTypes = MealType::whereIn('id', $mealTypeIds)->get(['id', 'name']);

    //         // Prepare the response data
    //         $data = [
    //             'client_meal_order' => [
    //                 'id' => $clientMealOrder->id,
    //                 'subtotal' => $clientMealOrder->subtotal,
    //                 'tax' => $clientMealOrder->tax,
    //                 'delivery_fee' => $clientMealOrder->delivery_fee,
    //                 'payable_amount' => $clientMealOrder->payable_amount,
    //                 'paid_amount' => $clientMealOrder->paid_amount,
    //                 'payment_status' => $clientMealOrder->payment_status,
    //                 'created_at' => $clientMealOrder->created_at ? $clientMealOrder->created_at->format('d M Y H:i:s') : null,
    //                 'updated_at' => $clientMealOrder->updated_at ? $clientMealOrder->updated_at->format('d M Y H:i:s') : null,
    //             ],
    //             'order' => [
    //                 'order_number' => $clientMealOrder->mealOrder->order_number,
    //                 'invoice_no' => $clientMealOrder->mealOrder->invoice_no,
    //                 'status' => $clientMealOrder->mealOrder->status,
    //                 'created_at' => $clientMealOrder->mealOrder->created_at->format('d M Y H:i:s'),
    //                 'payment_status' => $clientMealOrder->mealOrder->payment_status,
    //             ],
    //             'client' => [
    //                 'id' => $clientMealOrder->client->id,
    //                 'name' => $clientMealOrder->client->firstName . ' ' . $clientMealOrder->client->lastName,
    //                 'email' => $clientMealOrder->client->email,
    //                 'mobile' => $clientMealOrder->client->mobile,
    //                 'image' => $clientMealOrder->client->image,
    //             ],
    //             'items' => $groupedItems,
    //             'summary' => [
    //                 'total_items' => $clientMealOrder->mealOrderItems->count(),
    //                 'total_dates' => count($groupedItems),
    //                 'total_meal_types' => $mealTypes->count(),
    //                 'subtotal' => $clientMealOrder->subtotal,
    //                 'tax' => $clientMealOrder->tax,
    //                 'delivery_fee' => $clientMealOrder->delivery_fee,
    //                 'total_amount' => $clientMealOrder->payable_amount,
    //                 'paid_amount' => $clientMealOrder->paid_amount,
    //                 'balance_amount' => $clientMealOrder->payable_amount - $clientMealOrder->paid_amount,
    //             ],
    //             'meal_types' => $mealTypes,
    //             'dates' => array_keys($groupedItems),
    //         ];

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $data
    //         ], 200);

    //     } catch (Exception $e) {
    //         // Log the error
    //         error_log('Error in getClientPaymentDetailsInfo: ' . $e->getMessage());
    //         error_log('Trace: ' . $e->getTraceAsString());
            
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'An error occurred while retrieving payment details.',
    //             'error' => env('APP_DEBUG') ? $e->getMessage() : null
    //         ], 500);
    //     }
    // }

    public function viewOrderDetailsByDate(Request $request)
    {
        return view('client.pages.meal-order.view_order_details_by_date');
    }

    public function getMealOrderDetailsByDate(Request $request, $orderId, $date)
    {
        try {
            $client_id = $request->header('id');

            // Get the meal order with filtering for the specific client and date
            $order = MealOrder::whereHas('items', function($query) use ($client_id, $date) {
                    $query->where('client_id', $client_id)
                          ->where('meal_date', $date);
                })
                ->with([
                    'items' => function($query) use ($client_id, $date) {
                        // Only load items for this specific client and date
                        $query->where('client_id', $client_id)
                              ->where('meal_date', $date)
                              ->with(['mealType', 'product.nutrient', 'client:id,firstName,lastName,mobile,address1,address2,zip_code,city_id,county_id,country_id,latitude,longitude']);
                    },
                    'mealShippingAddress.country',
                    'mealShippingAddress.county', 
                    'mealShippingAddress.city'
                ])
                ->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            // Filter items to only include this client's items for the specific date
            $clientItems = $order->items->filter(function($item) use ($client_id, $date) {
                return $item->client_id == $client_id && $item->meal_date == $date;
            });

            if ($clientItems->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No items found for this client on the specified date.',
                ], 404);
            }

            // Group items by meal type for the specific date
            $groupedItems = $clientItems->groupBy(function($item) {
                return $item->mealType->name ?? 'Other';
            });

            // Calculate nutrition for this client on the specific date
            $totalCalories = $clientItems->sum(function ($item) {
                return ($item->product->nutrient->calories ?? 0) * $item->quantity;
            });

            $caloriesByMealType = $clientItems->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            })->map(function ($group) {
                return $group->sum(function ($item) {
                    return ($item->product->nutrient->calories ?? 0) * $item->quantity;
                });
            });

            // Calculate summary for this client on the specific date
            $dateSubtotal = $clientItems->sum('total_price');
            
            // Use tax rate from config with fallback to 20%
            $taxRate = (float) config('services.tax_rate', 0.20);
            $dateTax = $dateSubtotal * $taxRate;

            // Calculate delivery charge for this specific client and date
            $dateDeliveryCharge = $this->calculateDeliveryChargeForDate($order, $date);
            $dateTotal = $dateSubtotal + $dateTax + $dateDeliveryCharge;

            // Get client-specific order status for this date
            $clientOrder = ClientMealOrder::where('meal_order_id', $orderId)
                ->where('client_id', $client_id)
                ->first();

            $clientPaymentStatus = $clientOrder->payment_status ?? $order->payment_status;

            $summary = [
                'subtotal' => floatval($dateSubtotal),
                'tax' => floatval($dateTax),
                'tax_rate' => floatval($taxRate * 100), // Convert to percentage for display
                'delivery_charge' => floatval($dateDeliveryCharge),
                'total' => floatval($dateTotal),
                'total_items' => $clientItems->sum('quantity')
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'payment_status' => $clientPaymentStatus,
                        'created_at' => $order->created_at,
                    ],
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