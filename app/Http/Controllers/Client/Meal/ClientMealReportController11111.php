<?php
namespace App\Http\Controllers\Client\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // ===== Verify client role =====
    private function getClientId(Request $request): ?int
    {
        $clientId = $request->header('id');
        if (!$clientId) return null;
        return (int) $clientId;
    }

    // ===== Shared date range builder =====
    private function getDateRange(Request $request): array
    {
        $period    = $request->input('period', 'monthly');
        $endDate   = Carbon::now()->endOfDay();
        $startDate = null;

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
            $clientId  = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            // Total orders this client received
            $totalOrders = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->count();

            // Total payable to client
            $totalPayable = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->sum('payable_amount');

            // Paid amount
            $paidCount = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->where('payment_status', 'paid')
                ->count();

            $paidAmount = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->where('payment_status', 'paid')
                ->sum('payable_amount');

            // Due amount
            $dueCount = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->where('payment_status', 'due')
                ->count();

            $dueAmount = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->where('payment_status', 'due')
                ->sum('payable_amount');

            // Total items sold
            $totalItems = MealOrderItem::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->sum('quantity');

            // Total item revenue
            $totalItemRevenue = MealOrderItem::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->sum('total_price');

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'total_orders'      => $totalOrders,
                    'total_payable'     => number_format($totalPayable, 2),
                    'paid_count'        => $paidCount,
                    'paid_amount'       => number_format($paidAmount, 2),
                    'due_count'         => $dueCount,
                    'due_amount'        => number_format($dueAmount, 2),
                    'total_items_sold'  => $totalItems,
                    'total_item_revenue'=> number_format($totalItemRevenue, 2),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Order Report — Bar Chart =====
    public function getOrderReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
            }

            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $orders = ClientMealOrder::where('client_id', $clientId)
                ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(meal_orders.created_at, '{$format}') as label"),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(client_meal_orders.payable_amount) as total_payable'),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'paid' THEN client_meal_orders.payable_amount ELSE 0 END) as paid_amount"),
                    DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'due'  THEN client_meal_orders.payable_amount ELSE 0 END) as due_amount")
                )
                ->groupBy('label')
                ->orderBy('meal_orders.created_at')
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Payment Status — Pie Chart =====
    public function getPaymentStatusReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = ClientMealOrder::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->select('payment_status', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels' => $data->pluck('payment_status'),
                    'counts' => $data->pluck('count'),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Meal Type Report — Bar Chart =====
    public function getMealTypeReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrderItem::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->join('meal_types', 'meal_order_items.meal_type_id', '=', 'meal_types.id')
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Order Status — Pie Chart =====
    public function getOrderStatusReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
            }

            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = ClientMealOrder::where('client_id', $clientId)
                ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
                ->whereBetween('meal_orders.created_at', [$start, $end])
                ->select('meal_orders.status', DB::raw('COUNT(*) as count'))
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Daily Revenue — Line Chart =====
    public function getDailyRevenueReport(Request $request)
    {
        try {
            $clientId = $this->getClientId($request);
            if (!$clientId) {
                return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
            }

            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $data = MealOrderItem::where('client_id', $clientId)
                ->whereHas('mealOrder', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->join('meal_orders', 'meal_order_items.meal_order_id', '=', 'meal_orders.id')
                ->select(
                    DB::raw("DATE_FORMAT(meal_orders.created_at, '{$format}') as label"),
                    DB::raw('SUM(meal_order_items.total_price) as revenue'),
                    DB::raw('SUM(meal_order_items.quantity) as items_sold')
                )
                ->groupBy('label')
                ->orderBy('meal_orders.created_at')
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}