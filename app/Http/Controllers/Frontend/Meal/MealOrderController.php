<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\MealDeliveryCharge;
use App\Models\DeliveryChargeLedger; 
use App\Models\User;
use Carbon\Carbon;
use Exception;

class MealOrderController extends Controller
{

    public function index()
    {
        return view('frontend.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            $mealOrders = MealOrder::with([
                    'customer:id,firstName,lastName',
                    'items.mealType:id,name',
                    'items.product.nutrient',
                ])
                ->where('customer_id', $customer_id)
                ->latest()
                ->get();

            $allMealTypes = MealType::pluck('name')->toArray();
            $today        = Carbon::now()->format('Y-m-d');

            $data = [];

            foreach ($mealOrders as $order) {
                $customerName = trim(
                    ($order->customer->firstName ?? '') . ' ' .
                    ($order->customer->lastName  ?? '')
                ) ?: '-';

                $groupedItems = $order->items->groupBy('meal_date');

                foreach ($groupedItems as $mealDate => $items) {
                    $mealTypesForDate = $items->map(fn($item) => $item->mealType->name ?? null)
                        ->filter()->unique()->values()->toArray();

                    $caloriesForDate = $items->sum(function ($item) {
                        return ($item->product->nutrient->calories ?? 0) * $item->quantity;
                    });

                    $remainingMealTypes = array_values(array_diff($allMealTypes, $mealTypesForDate));

                    $data[] = [
                        'order_id'             => $order->id,
                        'order_number'         => $order->order_number ?? $order->id,
                        'meal_date'            => $mealDate,
                        'meal_types'           => implode(', ', $mealTypesForDate),
                        'remaining_meal_types' => $remainingMealTypes,
                        'calories'             => $caloriesForDate,
                        'customer_name'        => $customerName,
                        'is_future_date'       => $mealDate > $today,
                    ];
                }
            }

            // Sort by meal date descending
            usort($data, fn($a, $b) => strcmp($b['meal_date'], $a['meal_date']));

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ], 200);

        } catch (Exception $e) {
            Log::error('getMealOrders: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
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
                'mealShippingAddress.city',
                'deliveryChargeLedgers.mealType',
                'deliveryChargeLedgers.deliveryPerson:id,firstName,lastName',
                'deliveryChargeLedgers.statusHistories' => fn($q) => $q->orderBy('created_at', 'asc'),
            ])->find($id);

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            $statusFlow = array_keys(DeliveryChargeLedger::STATUS_LABELS);
            $totalSteps = count($statusFlow);

            $deliveryLedgers = $order->deliveryChargeLedgers
                ->keyBy(fn($ledger) =>
                    $ledger->client_id . '_' .
                    $ledger->meal_type_id . '_' .
                    Carbon::parse($ledger->delivery_date)->format('Y-m-d')
                );

            $groupedItems = $order->items->groupBy(fn($item) =>
                Carbon::parse($item->meal_date)->format('Y-m-d')
            )->map(function ($dayItems, $date) use ($deliveryLedgers, $statusFlow, $totalSteps) {

                return $dayItems->groupBy(function ($item) use ($date, $deliveryLedgers, $statusFlow, $totalSteps) {

                    $mealTypeName   = $item->mealType->name ?? 'Other';
                    $ledgerKey      = $item->client_id . '_' . $item->meal_type_id . '_' . $date;
                    $deliveryLedger = $deliveryLedgers->get($ledgerKey);

                    if ($deliveryLedger) {
                        $histories     = $deliveryLedger->statusHistories->values();
                        $currentStatus = $histories->last()->delivery_status ?? 'pending';
                        $currentIndex  = array_search($currentStatus, $statusFlow, true);
                        $percentage    = $currentIndex !== false
                            ? intval((($currentIndex + 1) / $totalSteps) * 100)
                            : 0;

                        $item->delivery_info = [
                            'delivery_status'       => $deliveryLedger->delivery_status,
                            'delivery_status_label' => DeliveryChargeLedger::STATUS_LABELS[$deliveryLedger->delivery_status] ?? 'Pending',
                            'delivery_person_name'  => $deliveryLedger->deliveryPerson
                                ? $deliveryLedger->deliveryPerson->firstName . ' ' . $deliveryLedger->deliveryPerson->lastName
                                : 'Not Assigned',
                            'delivery_person_id'    => $deliveryLedger->delivery_person_id,
                            'order_tracking'        => $deliveryLedger->order_tracking,
                            'delivery_charge'       => floatval($deliveryLedger->delivery_charge ?? 0),
                            'payment_status'        => $deliveryLedger->payment_status,
                            'progress_percentage'   => $percentage,
                            'current_status'        => $currentStatus,
                            'status_history'        => $histories->map(fn($history) => [
                                'id'               => $history->id,
                                'delivery_status'  => $history->delivery_status,
                                'status_label'     => DeliveryChargeLedger::STATUS_LABELS[$history->delivery_status] ?? 'Unknown',
                                'notes'            => $history->notes,
                                'updated_by_label' => $history->updated_by_label,
                                'pick_up_at'       => $history->pick_up_at ? $history->pick_up_at->format('Y-m-d H:i:s') : null,
                                'created_at'       => $history->created_at ? $history->created_at->format('Y-m-d H:i:s') : null,
                            ])->values(),
                        ];
                    } else {
                        $item->delivery_info = [
                            'delivery_status'       => 'pending',
                            'delivery_status_label' => 'Pending',
                            'delivery_person_name'  => 'Not Assigned',
                            'delivery_person_id'    => null,
                            'order_tracking'        => null,
                            'delivery_charge'       => 0,
                            'payment_status'        => 'due',
                            'progress_percentage'   => 0,
                            'current_status'        => 'pending',
                            'status_history'        => [],
                        ];
                    }

                    return $mealTypeName;
                });
            });

            $totalCalories = $order->items->sum(fn($item) =>
                ($item->product?->nutrient?->calories ?? 0) * $item->quantity
            );

            $caloriesByMealType = $order->items->groupBy(fn($item) =>
                $item->mealType->name ?? 'Other'
            )->map(fn($group) => $group->sum(fn($item) =>
                ($item->product?->nutrient?->calories ?? 0) * $item->quantity
            ));

            $taxRate        = (float) config('services.tax_rate',    0.20);
            $serviceFeeRate = (float) config('services.service_fee', 0.05);

            $summary = [
                'subtotal'         => floatval($order->subtotal       ?? 0),
                'tax'              => floatval($order->tax            ?? 0),
                'tax_rate'         => $taxRate * 100,         // ← percentage
                'service_fee'      => floatval($order->service_fee    ?? 0),
                'service_fee_rate' => $serviceFeeRate * 100,  // ← percentage
                'delivery_fee'     => floatval($order->delivery_fee   ?? 0),
                'total'            => floatval($order->payable_amount ?? 0),
                'total_items'      => $order->items->sum('quantity'),
            ];

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'order'            => $order,
                    'order_id'         => $order->id, // ← needed for Current Order calories
                    'summary'          => $summary,
                    'meal_cart'        => $groupedItems,
                    'nutrition'        => [
                        'total_calories'        => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'items'            => $order->items->map(fn($item) => [
                        'id'            => $item->id,
                        'meal_date'     => $item->meal_date,
                        'meal_time'     => $item->meal_time,
                        'meal_type'     => $item->mealType ? [
                            'id'   => $item->mealType->id,
                            'name' => $item->mealType->name,
                        ] : null,
                        'product'       => $item->product,
                        'client'        => $item->client,
                        'quantity'      => $item->quantity,
                        'unit_price'    => $item->unit_price,
                        'total_price'   => $item->total_price,
                        'delivery_info' => $item->delivery_info ?? null,
                    ]),
                    'delivery_statuses'=> DeliveryChargeLedger::STATUS_LABELS,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('getMealOrderDetails: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewOrderDetailsByDate()
    {
        return view('frontend.pages.meal-order.view_order_details_by_date');
    }

    public function getMealOrderDetailsByDate1111($orderId, $date)
    {
        try {
            $order = MealOrder::with([
                'items' => function ($query) use ($date) {
                    $query->where('meal_date', $date)
                          ->with([
                              'mealType',
                              'product.nutrient',
                              'client:id,firstName,lastName,mobile,address1,address2,zip_code,city_id,county_id,country_id,latitude,longitude',
                          ]);
                },
                'mealShippingAddress.country',
                'mealShippingAddress.county',
                'mealShippingAddress.city',
            ])->find($orderId);

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            if ($order->items->isEmpty()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'No items found for this date.',
                ], 404);
            }

            // Group items by meal type
            $groupedItems = $order->items->groupBy(function ($item) {
                return $item->mealType->name ?? 'Other';
            });

            // Nutrition
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

            // ← Get delivery charge from DeliveryChargeLedger directly
            $dateDeliveryCharge = DeliveryChargeLedger::where('meal_order_id', $order->id)
                ->where('delivery_date', $date)
                ->sum('delivery_charge');

            $taxRate      = (float) config('services.tax_rate', 0.20);
            $dateSubtotal = $order->items->sum('total_price');
            $dateTax      = round($dateSubtotal * $taxRate, 2);
            $dateTotal    = $dateSubtotal + $dateTax + $dateDeliveryCharge;

            $summary = [
                'subtotal'         => (float) $dateSubtotal,
                'tax'              => (float) $dateTax,
                'tax_rate'         => $taxRate * 100, // percentage for display
                'delivery_charge'  => (float) $dateDeliveryCharge,
                'total'            => (float) $dateTotal,
                'total_items'      => $order->items->sum('quantity'),
            ];

            $itemsWithTime = $order->items->map(fn($item) => [
                'id'          => $item->id,
                'meal_date'   => $item->meal_date,
                'meal_time'   => $item->meal_time,
                'meal_type'   => $item->mealType ? [
                    'id'   => $item->mealType->id,
                    'name' => $item->mealType->name,
                ] : null,
                'product'     => $item->product,
                'client'      => $item->client,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'total_price' => $item->total_price,
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'order'            => $order,
                    'selected_date'    => $date,
                    'summary'          => $summary,
                    'meal_cart'        => $groupedItems,
                    'nutrition'        => [
                        'total_calories'        => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'items'            => $itemsWithTime,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('getMealOrderDetailsByDate: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMealOrderDetailsByDate($orderId, $date)
    {
        try {
            $order = MealOrder::with([
                'items' => function ($query) use ($date) {
                    $query->where('meal_date', $date)
                          ->with([
                              'mealType',
                              'product.nutrient',
                              'client:id,firstName,lastName,mobile,address1,address2,zip_code,city_id,county_id,country_id,latitude,longitude',
                          ]);
                },
                'mealShippingAddress.country',
                'mealShippingAddress.county',
                'mealShippingAddress.city',
            ])->find($orderId);

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            if ($order->items->isEmpty()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'No items found for this date.',
                ], 404);
            }

            // Group items by meal type
            $groupedItems = $order->items->groupBy(fn($item) => $item->mealType->name ?? 'Other');

            // Nutrition
            $totalCalories = $order->items->sum(fn($item) =>
                ($item->product->nutrient->calories ?? 0) * $item->quantity
            );

            $caloriesByMealType = $order->items->groupBy(fn($item) =>
                $item->mealType->name ?? 'Other'
            )->map(fn($group) => $group->sum(fn($item) =>
                ($item->product->nutrient->calories ?? 0) * $item->quantity
            ));

            // Delivery charge from ledger directly
            $dateDeliveryCharge = DeliveryChargeLedger::where('meal_order_id', $order->id)
                ->where('delivery_date', $date)
                ->sum('delivery_charge');

            $taxRate        = (float) config('services.tax_rate',    0.20);
            $serviceFeeRate = (float) config('services.service_fee', 0.05);

            $dateSubtotal   = $order->items->sum('total_price');
            $dateTax        = round($dateSubtotal * $taxRate,        2);
            $dateServiceFee = round($dateSubtotal * $serviceFeeRate, 2);
            $dateTotal      = $dateSubtotal + $dateTax + $dateServiceFee + $dateDeliveryCharge;

            $summary = [
                'subtotal'         => (float) $dateSubtotal,
                'tax'              => (float) $dateTax,
                'tax_rate'         => $taxRate * 100,         // ← percentage
                'service_fee'      => (float) $dateServiceFee,
                'service_fee_rate' => $serviceFeeRate * 100,  // ← percentage
                'delivery_charge'  => (float) $dateDeliveryCharge,
                'total'            => (float) $dateTotal,
                'total_items'      => $order->items->sum('quantity'),
            ];

            $itemsWithTime = $order->items->map(fn($item) => [
                'id'          => $item->id,
                'meal_date'   => $item->meal_date,
                'meal_time'   => $item->meal_time,
                'meal_type'   => $item->mealType ? [
                    'id'   => $item->mealType->id,
                    'name' => $item->mealType->name,
                ] : null,
                'product'     => $item->product,
                'client'      => $item->client,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'total_price' => $item->total_price,
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'order'            => $order,
                    'order_id'         => $order->id,   // ← for Current Date calories
                    'selected_date'    => $date,        // ← for Current Date calories
                    'summary'          => $summary,
                    'meal_cart'        => $groupedItems,
                    'nutrition'        => [
                        'total_calories'        => $totalCalories,
                        'calories_by_meal_type' => $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'items'            => $itemsWithTime,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('getMealOrderDetailsByDate: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCalories(Request $request)
    {
        try {
            $customerId = $request->header('id');
            $range      = $request->range ?? '7days';
            $orderId    = $request->order_id; // ← for current_order / current_date
            $date       = $request->date;     // ← for current_date

            // ===== Current Date — calories for a specific order + date =====
            if ($range === 'current_date') {
                if (!$orderId || !$date) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'Order ID and date are required for current date range.'
                    ], 400);
                }

                $orderItems = MealOrderItem::with(['product.nutrient', 'mealType'])
                    ->where('meal_order_id', $orderId)
                    ->whereDate('meal_date', $date)
                    ->whereHas('mealOrder', fn($q) => $q->where('customer_id', $customerId))
                    ->orderBy('meal_date', 'ASC')
                    ->get();

            // ===== Current Order — calories for this specific order only =====
            } elseif ($range === 'current_order') {
                if (!$orderId) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'Order ID is required for current order range.'
                    ], 400);
                }

                $orderItems = MealOrderItem::with(['product.nutrient', 'mealType'])
                    ->where('meal_order_id', $orderId)
                    ->whereHas('mealOrder', fn($q) => $q->where('customer_id', $customerId))
                    ->orderBy('meal_date', 'ASC')
                    ->get();

            // ===== Date-range based =====
            } else {
                switch ($range) {
                    case 'today':
                        $start = Carbon::today()->startOfDay();
                        $end   = Carbon::today()->endOfDay();
                        break;
                    case 'yesterday':
                        $start = Carbon::yesterday()->startOfDay();
                        $end   = Carbon::yesterday()->endOfDay();
                        break;
                    case '7days':
                        $start = Carbon::now()->subDays(6)->startOfDay();
                        $end   = Carbon::now()->endOfDay();
                        break;
                    case '30days':
                        $start = Carbon::now()->subDays(29)->startOfDay();
                        $end   = Carbon::now()->endOfDay();
                        break;
                    case 'current_month':
                        $start = Carbon::now()->startOfMonth()->startOfDay();
                        $end   = Carbon::now()->endOfDay();
                        break;
                    case 'last_month':
                        $start = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                        $end   = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                        break;
                    default:
                        return response()->json([
                            'status'  => 'failed',
                            'message' => 'Invalid range'
                        ], 400);
                }

                $orderItems = MealOrderItem::with(['product.nutrient', 'mealType'])
                    ->whereHas('mealOrder', fn($q) => $q->where('customer_id', $customerId))
                    ->whereDate('meal_date', '>=', $start)
                    ->whereDate('meal_date', '<=', $end)
                    ->orderBy('meal_date', 'ASC')
                    ->get();
            }

            // Group by date and calculate daily totals
            $dailyTotals = $orderItems->groupBy(fn($item) =>
                Carbon::parse($item->meal_date)->format('Y-m-d')
            )->map(fn($itemsOnDay) => $itemsOnDay->sum(fn($item) =>
                ($item->product?->nutrient?->calories ?? 0) * $item->quantity
            ));

            // Meal type breakdown per date
            $mealTypeBreakdown = [];
            foreach ($orderItems as $item) {
                $itemDate = Carbon::parse($item->meal_date)->format('Y-m-d');
                $mealType = $item->mealType->name ?? 'Other';
                $cal      = ($item->product?->nutrient?->calories ?? 0) * $item->quantity;

                $mealTypeBreakdown[$itemDate][$mealType] =
                    ($mealTypeBreakdown[$itemDate][$mealType] ?? 0) + $cal;
            }

            $caloriesUnit     = $orderItems->first()?->product?->nutrient?->calories_unit ?? 'kcal';
            $totalCaloriesSum = $dailyTotals->sum();

            return response()->json([
                'status'              => 'success',
                'dates'               => $dailyTotals->keys()->values(),
                'calories'            => $dailyTotals->values(),
                'calories_unit'       => $caloriesUnit,
                'total_calories_sum'  => $totalCaloriesSum,
                'meal_type_breakdown' => $mealTypeBreakdown,
            ], 200);

        } catch (Exception $e) {
            Log::error('getCalories: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
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
