<?php
namespace App\Http\Controllers\Backend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\DeliveryChargeLedger;
use Carbon\Carbon;
use Exception;

class MealReportController extends Controller
{
    public function index()
    {
        return view('backend.pages.meal-reports.index');
    }

    // ===== Shared date range builder =====
    private function getDateRange(Request $request): array
    {
        $period    = $request->input('period', 'monthly'); // daily,weekly,monthly,yearly,custom
        $startDate = null;
        $endDate   = Carbon::now()->endOfDay();

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
            'daily'   => '%H:00',        // by hour
            'weekly'  => '%a',           // by day name
            'monthly' => '%d %b',        // by day
            'yearly'  => '%b %Y',        // by month
            'custom'  => '%d %b %Y',     // by date
            default   => '%d %b',
        };
    }

    // ===== KPI Summary Cards =====
    public function getSummary(Request $request)
    {
        try {
            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $totalOrders    = MealOrder::whereBetween('created_at', [$start, $end])->count();
            $totalRevenue   = MealOrder::whereBetween('created_at', [$start, $end])->sum('payable_amount');
            $paidOrders     = MealOrder::whereBetween('created_at', [$start, $end])->where('payment_status', 'paid')->count();
            $dueOrders      = MealOrder::whereBetween('created_at', [$start, $end])->where('payment_status', 'due')->count();
            $pendingOrders  = MealOrder::whereBetween('created_at', [$start, $end])->where('status', 'pending')->count();
            $completedOrders= MealOrder::whereBetween('created_at', [$start, $end])->where('status', 'completed')->count();
            $cancelledOrders= MealOrder::whereBetween('created_at', [$start, $end])->where('status', 'cancelled')->count();

            $clientPaymentDue  = ClientMealOrder::whereHas('mealOrder', fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )->where('payment_status', 'due')->sum('payable_amount');

            $deliveryPaymentDue = DeliveryChargeLedger::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'due')->sum('delivery_charge');

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'total_orders'        => $totalOrders,
                    'total_revenue'       => number_format($totalRevenue, 2),
                    'paid_orders'         => $paidOrders,
                    'due_orders'          => $dueOrders,
                    'pending_orders'      => $pendingOrders,
                    'completed_orders'    => $completedOrders,
                    'cancelled_orders'    => $cancelledOrders,
                    'client_payment_due'  => number_format($clientPaymentDue, 2),
                    'delivery_payment_due'=> number_format($deliveryPaymentDue, 2),
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
            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $orders = MealOrder::whereBetween('created_at', [$start, $end])
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '{$format}') as label"),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(payable_amount) as total_revenue'),
                    DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid"),
                    DB::raw("SUM(CASE WHEN payment_status = 'due' THEN 1 ELSE 0 END) as due")
                )
                ->groupBy('label')
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'        => $orders->pluck('label'),
                    'total_orders'  => $orders->pluck('total_orders'),
                    'total_revenue' => $orders->pluck('total_revenue'),
                    'paid'          => $orders->pluck('paid'),
                    'due'           => $orders->pluck('due'),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Payment Status Report — Pie Chart =====
    public function getPaymentStatusReport(Request $request)
    {
        try {
            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrder::whereBetween('created_at', [$start, $end])
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

    // ===== Client Payment Report — Bar Chart =====
    public function getClientPaymentReport(Request $request)
    {
        try {
            ['start' => $start, 'end' => $end, 'period' => $period] = $this->getDateRange($request);
            $format = $this->getGroupByFormat($period);

            $data = ClientMealOrder::whereHas('mealOrder', fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )
            ->join('meal_orders', 'client_meal_orders.meal_order_id', '=', 'meal_orders.id')
            ->select(
                DB::raw("DATE_FORMAT(meal_orders.created_at, '{$format}') as label"),
                DB::raw('SUM(client_meal_orders.payable_amount) as total_payable'),
                DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'paid' THEN client_meal_orders.payable_amount ELSE 0 END) as paid_amount"),
                DB::raw("SUM(CASE WHEN client_meal_orders.payment_status = 'due' THEN client_meal_orders.payable_amount ELSE 0 END) as due_amount")
            )
            ->groupBy('label')
            ->orderBy('meal_orders.created_at')
            ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'        => $data->pluck('label'),
                    'total_payable' => $data->pluck('total_payable'),
                    'paid_amount'   => $data->pluck('paid_amount'),
                    'due_amount'    => $data->pluck('due_amount'),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Delivery Payment Report — Pie Chart =====
    public function getDeliveryPaymentReport(Request $request)
    {
        try {
            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = DeliveryChargeLedger::whereBetween('created_at', [$start, $end])
                ->select('payment_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(delivery_charge) as total'))
                ->groupBy('payment_status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels' => $data->pluck('payment_status'),
                    'counts' => $data->pluck('count'),
                    'totals' => $data->pluck('total'),
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
            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = MealOrderItem::whereHas('mealOrder', fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )
            ->join('meal_types', 'meal_order_items.meal_type_id', '=', 'meal_types.id')
            ->select(
                'meal_types.name as label',
                DB::raw('COUNT(*) as total_items'),
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

    // ===== Delivery Status Report — Pie Chart =====
    public function getDeliveryStatusReport(Request $request)
    {
        try {
            ['start' => $start, 'end' => $end] = $this->getDateRange($request);

            $data = DeliveryChargeLedger::whereBetween('created_at', [$start, $end])
                ->select('delivery_status', DB::raw('COUNT(*) as count'))
                ->groupBy('delivery_status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels' => $data->pluck('delivery_status'),
                    'counts' => $data->pluck('count'),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}