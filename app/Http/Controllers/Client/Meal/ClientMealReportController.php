<?php
namespace App\Http\Controllers\Client\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ClientMealOrder;
use App\Models\MealOrderItem;
use App\Models\MealOrder;
use Carbon\Carbon;
use Exception;

class ClientMealReportController extends Controller
{
    // ===== Page =====
    public function index()
    {
        return view('client.pages.meal-reports.index');
    }

    // ===== Get Client ID from header =====
    private function getClientId(Request $request): ?int
    {
        $clientId = $request->header('id');
        if (!$clientId) return null;
        return (int) $clientId;
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
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            // ===== Single query for ClientMealOrder stats =====
            $orderStats = ClientMealOrder::where('client_id', $clientId)
                ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(client_meal_orders.payable_amount) as total_payable'),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count"),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'paid' THEN client_meal_orders.payable_amount ELSE 0 END) as paid_amount"),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'due'  THEN 1 ELSE 0 END) as due_count"),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'due'  THEN client_meal_orders.payable_amount ELSE 0 END) as due_amount")
                )
                ->first();

            // ===== Single query for MealOrderItem stats =====
            $itemStats = MealOrderItem::where('client_id', $clientId)
                ->join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    DB::raw('SUM(meal_order_items.quantity) as total_items'),
                    DB::raw('SUM(meal_order_items.total_price) as total_revenue')
                )
                ->first();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'total_orders'       => $orderStats->total_orders       ?? 0,
                    'total_payable'      => number_format($orderStats->total_payable  ?? 0, 2),
                    'paid_count'         => $orderStats->paid_count          ?? 0,
                    'paid_amount'        => number_format($orderStats->paid_amount     ?? 0, 2),
                    'due_count'          => $orderStats->due_count           ?? 0,
                    'due_amount'         => number_format($orderStats->due_amount      ?? 0, 2),
                    'total_items_sold'   => $itemStats->total_items          ?? 0,
                    'total_item_revenue' => number_format($itemStats->total_revenue    ?? 0, 2),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ClientMealReportController@getSummary: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Order Report — Bar Chart =====
    public function getOrderReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $orders = ClientMealOrder::where('client_meal_orders.client_id', $clientId)
                ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(meal_orders.created_at, '{$format}') as label"),
                    DB::raw('MIN(meal_orders.created_at) as sort_date'), // ← fix strict mode
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(client_meal_orders.payable_amount) as total_payable'),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'paid' THEN client_meal_orders.payable_amount ELSE 0 END) as paid_amount"),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'due'  THEN client_meal_orders.payable_amount ELSE 0 END) as due_amount")
                )
                ->groupBy('label')
                ->orderBy('sort_date') // ← use grouped column for ordering
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'        => $orders->pluck('label'),
                    'total_orders'  => $orders->pluck('total_orders'),
                    'total_payable' => $orders->pluck('total_payable'),
                    'paid_amount'   => $orders->pluck('paid_amount'),
                    'due_amount'    => $orders->pluck('due_amount'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ClientMealReportController@getOrderReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Payment Status — Pie Chart =====
    public function getPaymentStatusReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = ClientMealOrder::where('client_meal_orders.client_id', $clientId)
                ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    'client_meal_orders.payment_status',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('client_meal_orders.payment_status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels' => $data->pluck('payment_status'),
                    'counts' => $data->pluck('count'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ClientMealReportController@getPaymentStatusReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Meal Type Report — Bar Chart =====
    public function getMealTypeReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrderItem::where('meal_order_items.client_id', $clientId)
                ->join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
                ->join('meal_types',  'meal_order_items.meal_type_id',  '=', 'meal_types.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    'meal_types.name as label',
                    DB::raw('SUM(meal_order_items.quantity) as total_items'),
                    DB::raw('SUM(meal_order_items.total_price) as total_revenue')
                )
                ->groupBy('meal_types.name')
                ->orderByDesc('total_items')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'        => $data->pluck('label'),
                    'total_items'   => $data->pluck('total_items'),
                    'total_revenue' => $data->pluck('total_revenue'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ClientMealReportController@getMealTypeReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Order Status — Pie Chart =====
    public function getOrderStatusReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = ClientMealOrder::where('client_meal_orders.client_id', $clientId)
                ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    'meal_orders.status',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('meal_orders.status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels' => $data->pluck('status'),
                    'counts' => $data->pluck('count'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ClientMealReportController@getOrderStatusReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Daily Revenue — Line Chart =====
    public function getDailyRevenueReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized.'], 401);
            }

            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $data = MealOrderItem::where('meal_order_items.client_id', $clientId)
                ->join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(meal_orders.created_at, '{$format}') as label"),
                    DB::raw('MIN(meal_orders.created_at) as sort_date'), // ← fix strict mode
                    DB::raw('SUM(meal_order_items.total_price) as revenue'),
                    DB::raw('SUM(meal_order_items.quantity) as items_sold')
                )
                ->groupBy('label')
                ->orderBy('sort_date') // ← use grouped column
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'     => $data->pluck('label'),
                    'revenue'    => $data->pluck('revenue'),
                    'items_sold' => $data->pluck('items_sold'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('ClientMealReportController@getDailyRevenueReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}