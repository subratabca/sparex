<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use Carbon\Carbon;
use Exception;

class MealReportController extends Controller
{
    // ===== Page =====
    public function index()
    {
        return view('frontend.pages.meal-reports.index');
    }

    // ===== Get Customer ID from header =====
    private function getCustomerId(Request $request): ?int
    {
        $customerId = $request->header('id');
        if (!$customerId) return null;
        return (int) $customerId;
    }

    // ===== Shared date range builder =====
    private function getDateRange(Request $request): array
    {
        $period  = $request->input('period', 'monthly');
        $endDate = Carbon::now()->endOfDay();

        switch ($period) {
            case 'daily':
                $startDate = Carbon::now()->startOfDay();
                break;
            case 'weekly':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'monthly':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate   = Carbon::parse($request->input('end_date'))->endOfDay();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
        }

        return ['start' => $startDate, 'end' => $endDate, 'period' => $period];
    }

    // ===== Shared group by format =====
    private function getGroupByFormat(string $period): string
    {
        return match($period) {
            'daily'   => '%H:00',
            'weekly'  => '%a',
            'monthly' => '%d %b',
            'yearly'  => '%b %Y',
            'custom'  => '%d %b %Y',
            default   => '%d %b',
        };
    }

    // ===== KPI Summary =====
    public function getSummary(Request $request)
    {
        try {
            $customerId = $this->getCustomerId($request);
            if (!$customerId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            // Single query for order stats
            $orderStats = MealOrder::where('customer_id', $customerId)
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(payable_amount) as total_spent'),
                    DB::raw('SUM(subtotal) as total_subtotal'),
                    DB::raw('SUM(tax) as total_tax'),
                    DB::raw('SUM(service_fee) as total_service_fee'),
                    DB::raw('SUM(delivery_fee) as total_delivery_fee'),
                    DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count"),
                    DB::raw("SUM(CASE WHEN payment_status = 'due'  THEN 1 ELSE 0 END) as due_count"),
                    DB::raw("SUM(CASE WHEN status = 'completed'  THEN 1 ELSE 0 END) as completed_count"),
                    DB::raw("SUM(CASE WHEN status = 'pending'    THEN 1 ELSE 0 END) as pending_count"),
                    DB::raw("SUM(CASE WHEN status = 'cancelled'  THEN 1 ELSE 0 END) as cancelled_count")
                )
                ->first();


            // Single query for item stats — fix customer_id filter
            $itemStats = MealOrderItem::join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
                ->where('meal_orders.customer_id', $customerId)  // ← fix: meal_orders.customer_id
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    DB::raw('SUM(meal_order_items.quantity) as total_items'),
                    DB::raw('COUNT(DISTINCT meal_order_items.meal_order_id) as total_unique_orders')
                )
                ->first();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'total_orders'       => $orderStats->total_orders      ?? 0,
                    'total_spent'        => number_format($orderStats->total_spent        ?? 0, 2),
                    'total_subtotal'     => number_format($orderStats->total_subtotal     ?? 0, 2),
                    'total_tax'          => number_format($orderStats->total_tax          ?? 0, 2),
                    'total_service_fee'  => number_format($orderStats->total_service_fee  ?? 0, 2),
                    'total_delivery_fee' => number_format($orderStats->total_delivery_fee ?? 0, 2),
                    'paid_count'         => $orderStats->paid_count        ?? 0,
                    'due_count'          => $orderStats->due_count         ?? 0,
                    'completed_count'    => $orderStats->completed_count   ?? 0,
                    'pending_count'      => $orderStats->pending_count     ?? 0,
                    'cancelled_count'    => $orderStats->cancelled_count   ?? 0,
                    'total_items'        => $itemStats->total_items        ?? 0,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('UserMealReportController@getSummary: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Order Report — Bar Chart + Table =====
    public function getOrderReport(Request $request)
    {
        try {
            $customerId = $this->getCustomerId($request);
            if (!$customerId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $orders = MealOrder::where('customer_id', $customerId)
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '{$format}') as label"),
                    DB::raw('MIN(created_at) as sort_date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(payable_amount) as total_spent'),
                    DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count"),
                    DB::raw("SUM(CASE WHEN payment_status = 'due'  THEN 1 ELSE 0 END) as due_count")
                )
                ->groupBy('label')
                ->orderBy('sort_date')
                ->get();

            // Table data — individual orders
            $tableData = MealOrder::where('customer_id', $customerId)
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    'id',
                    'order_number',
                    'invoice_no',
                    'status',
                    'payment_status',
                    'subtotal',
                    'tax',
                    'service_fee',
                    'delivery_fee',
                    'payable_amount',
                    'delivery_type',
                    'created_at'
                )
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($o) => [
                    'id'             => $o->id,
                    'order_number'   => $o->order_number,
                    'invoice_no'     => $o->invoice_no,
                    'status'         => $o->status,
                    'payment_status' => $o->payment_status,
                    'subtotal'       => number_format($o->subtotal,      2),
                    'tax'            => number_format($o->tax,           2),
                    'service_fee'    => number_format($o->service_fee,   2),
                    'delivery_fee'   => number_format($o->delivery_fee,  2),
                    'payable_amount' => number_format($o->payable_amount, 2),
                    'delivery_type'  => $o->delivery_type,
                    'order_date'     => $o->created_at->format('d M Y H:i'),
                ]);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'chart' => [
                        'labels'       => $orders->pluck('label'),
                        'total_orders' => $orders->pluck('total_orders'),
                        'total_spent'  => $orders->pluck('total_spent'),
                        'paid_count'   => $orders->pluck('paid_count'),
                        'due_count'    => $orders->pluck('due_count'),
                    ],
                    'table' => $tableData,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('UserMealReportController@getOrderReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Payment Status — Pie Chart =====
    public function getPaymentStatusReport(Request $request)
    {
        try {
            $customerId = $this->getCustomerId($request);
            if (!$customerId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrder::where('customer_id', $customerId)
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(payable_amount) as total_amount')
                )
                ->groupBy('payment_status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'  => $data->pluck('payment_status'),
                    'counts'  => $data->pluck('count'),
                    'amounts' => $data->pluck('total_amount'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('UserMealReportController@getPaymentStatusReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Order Status — Pie Chart =====
    public function getOrderStatusReport(Request $request)
    {
        try {
            $customerId = $this->getCustomerId($request);
            if (!$customerId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrder::where('customer_id', $customerId)
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    'status',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels' => $data->pluck('status'),
                    'counts' => $data->pluck('count'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('UserMealReportController@getOrderStatusReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Meal Type Report — Bar Chart + Table =====
    public function getMealTypeReport(Request $request)
    {
        try {
            $customerId = $this->getCustomerId($request);
            if (!$customerId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrderItem::join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
                ->join('meal_types',  'meal_order_items.meal_type_id',  '=', 'meal_types.id')
                ->where('meal_orders.customer_id', $customerId)       // ← fix: use meal_orders.customer_id
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    'meal_types.name as label',
                    DB::raw('SUM(meal_order_items.quantity) as total_items'),
                    DB::raw('SUM(meal_order_items.total_price) as total_spent'),
                    DB::raw('COUNT(DISTINCT meal_order_items.meal_order_id) as total_orders')
                )
                ->groupBy('meal_types.name')
                ->orderByDesc('total_items')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'chart' => [
                        'labels'      => $data->pluck('label'),
                        'total_items' => $data->pluck('total_items'),
                        'total_spent' => $data->pluck('total_spent'),
                    ],
                    'table' => $data->map(fn($d) => [
                        'meal_type'    => $d->label,
                        'total_items'  => $d->total_items,
                        'total_spent'  => number_format($d->total_spent, 2),
                        'total_orders' => $d->total_orders,
                    ]),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('UserMealReportController@getMealTypeReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Daily Spending — Line Chart =====
    public function getDailySpendingReport(Request $request)
    {
        try {
            $customerId = $this->getCustomerId($request);
            if (!$customerId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $data = MealOrder::where('customer_id', $customerId)  // ← direct on MealOrder — no join needed
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '{$format}') as label"),
                    DB::raw('MIN(created_at) as sort_date'),
                    DB::raw('SUM(payable_amount) as total_spent'),
                    DB::raw('SUM(subtotal) as subtotal'),
                    DB::raw('SUM(tax) as tax'),
                    DB::raw('SUM(service_fee) as service_fee'),
                    DB::raw('SUM(delivery_fee) as delivery_fee'),
                    DB::raw('COUNT(*) as total_orders')
                )
                ->groupBy('label')
                ->orderBy('sort_date')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'       => $data->pluck('label'),
                    'total_spent'  => $data->pluck('total_spent'),
                    'subtotal'     => $data->pluck('subtotal'),
                    'tax'          => $data->pluck('tax'),
                    'service_fee'  => $data->pluck('service_fee'),
                    'delivery_fee' => $data->pluck('delivery_fee'),
                    'total_orders' => $data->pluck('total_orders'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('UserMealReportController@getDailySpendingReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}