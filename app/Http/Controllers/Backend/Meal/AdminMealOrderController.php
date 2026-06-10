<?php
namespace App\Http\Controllers\Backend\Meal;

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
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use Carbon\Carbon;
use Exception;

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
                'customer:id,firstName,lastName,email',
            ])
            ->latest()
            ->get();

            $data = $mealOrders->map(function ($order, $index) {

                $customerName = trim(
                    ($order->customer->firstName ?? '') . ' ' .
                    ($order->customer->lastName  ?? '')
                );

                // Admin profit = service_fee + client_platform_fee + rider_platform_fee
                $profit = ($order->service_fee          ?? 0)
                        + ($order->client_platform_fee   ?? 0)
                        + ($order->rider_platform_fee    ?? 0);

                return [
                    'sl'                   => $index + 1,
                    'order_id'             => $order->id,
                    'order_date'           => $order->created_at->format('d M Y'),
                    'invoice_no'           => $order->invoice_no     ?? '-',
                    'order_number'         => $order->order_number   ?? '-',
                    'customer_name'        => $customerName          ?: '-',
                    'customer_email'       => $order->customer->email ?? '-',
                    'subtotal'             => number_format($order->subtotal              ?? 0, 2),
                    'tax'                  => number_format($order->tax                   ?? 0, 2),
                    'service_fee'          => number_format($order->service_fee           ?? 0, 2),
                    'client_platform_fee'  => number_format($order->client_platform_fee  ?? 0, 2),
                    'rider_platform_fee'   => number_format($order->rider_platform_fee   ?? 0, 2),
                    'delivery_fee'         => number_format($order->delivery_fee          ?? 0, 2),
                    'profit'               => number_format($profit, 2),
                    'payable_amount'       => number_format($order->payable_amount        ?? 0, 2),
                    'payment_status'       => $order->payment_status  ?? 'unknown',
                    'delivery_type'        => $order->delivery_type   ?? '-',
                    'status'               => $order->status          ?? '-',
                ];
            });

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
        
        return view('backend.pages.meal-order.view');
    }

    public function getMealOrderDetails($meal_order_id)
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
                'deliveryChargeLedgers.statusHistories' => function ($query) {
                    $query->latest()->take(5);
                }
            ])->find($meal_order_id);

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Meal order not found.',
                ], 404);
            }

            $deliveryLedgers = $order->deliveryChargeLedgers
                ->keyBy(function ($ledger) {
                    return $ledger->client_id . '_' .
                           $ledger->meal_type_id . '_' .
                           Carbon::parse($ledger->delivery_date)->format('Y-m-d');
                });

            $groupedItems = $order->items->groupBy(function ($item) {
                return Carbon::parse($item->meal_date)->format('Y-m-d');
            })->map(function ($dayItems, $date) use ($deliveryLedgers) {

                return $dayItems->groupBy(function ($item) use ($date, $deliveryLedgers) {
                    $mealTypeName = $item->mealType->name ?? 'Other';
                    $ledgerKey    = $item->client_id . '_' . $item->meal_type_id . '_' . $date;
                    $deliveryLedger = $deliveryLedgers->get($ledgerKey);

                    $item->delivery_info = $deliveryLedger ? [
                        'delivery_status'       => $deliveryLedger->delivery_status,
                        'delivery_status_label' => DeliveryChargeLedger::STATUS_LABELS[$deliveryLedger->delivery_status] ?? 'Pending',
                        'delivery_person_name'  => $deliveryLedger->deliveryPerson
                            ? $deliveryLedger->deliveryPerson->firstName . ' ' . $deliveryLedger->deliveryPerson->lastName
                            : 'Not Assigned',
                        'delivery_person_id'    => $deliveryLedger->delivery_person_id,
                        'order_tracking'        => $deliveryLedger->order_tracking,
                        'delivery_charge'       => $deliveryLedger->delivery_charge,
                        'payment_status'        => $deliveryLedger->payment_status,
                    ] : [
                        'delivery_status'       => 'pending',
                        'delivery_status_label' => 'Pending',
                        'delivery_person_name'  => 'Not Assigned',
                        'delivery_person_id'    => null,
                        'order_tracking'        => null,
                        'delivery_charge'       => 0,
                        'payment_status'        => 'due',
                    ];

                    return $mealTypeName;
                });
            });

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

            $taxRate             = (float) config('services.tax_rate',            0.20);
            $serviceFeeRate      = (float) config('services.service_fee',         0.05);
            $platformFeeRate     = (float) config('services.client_platform_fee', 0.05);
            $riderPlatformFeeRate= (float) config('services.rider_platform_fee',  0.05);

            $summary = [
                'subtotal'              => (float) ($order->subtotal              ?? 0),
                'tax'                   => (float) ($order->tax                   ?? 0),
                'service_fee'           => (float) ($order->service_fee           ?? 0),
                'client_platform_fee'   => (float) ($order->client_platform_fee  ?? 0),
                'rider_platform_fee'    => (float) ($order->rider_platform_fee   ?? 0),
                'delivery_fee'          => (float) ($order->delivery_fee          ?? 0),
                'total'                 => (float) ($order->payable_amount        ?? 0),
                'total_items'           => $order->items->sum('quantity'),
                'tax_rate'              => $taxRate,
                'service_fee_rate'      => $serviceFeeRate,
                'platform_fee_rate'     => $platformFeeRate,
                'rider_platform_fee_rate'=> $riderPlatformFeeRate,
            ];

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'order'            => $order,
                    'summary'          => $summary,
                    'meal_cart'        => $groupedItems,
                    'nutrition'        => [
                        'total_calories'       => $totalCalories,
                        'calories_by_meal_type'=> $caloriesByMealType,
                    ],
                    'shipping_address' => $order->mealShippingAddress,
                    'items'            => $order->items->map(function ($item) {
                        return [
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
                        ];
                    }),
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

}