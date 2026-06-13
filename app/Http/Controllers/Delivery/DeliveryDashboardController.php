<?php
namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DeliveryChargeLedger;
use Carbon\Carbon;
use Exception;

class DeliveryDashboardController extends Controller
{
    public function index()
    {
        return view('delivery.pages.meal-reports.index');
    }

    /* =========================================================
     | Helpers
     ========================================================= */

    private function getDateRange(Request $request): array
    {
        $period = $request->query('period', 'monthly');

        switch ($period) {
            case 'daily':
                return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay(), $period];
            case 'weekly':
                return [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay(), $period];
            case 'yearly':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), $period];
            case 'custom':
                $start = $request->query('start_date');
                $end   = $request->query('end_date');
                return [
                    $start ? Carbon::parse($start)->startOfDay() : Carbon::now()->startOfMonth(),
                    $end   ? Carbon::parse($end)->endOfDay()     : Carbon::now()->endOfDay(),
                    $period,
                ];
            case 'monthly':
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'monthly'];
        }
    }

    private function getGroupFormat(string $period): string
    {
        return match ($period) {
            'daily'   => '%H:00',
            'weekly'  => '%a',
            'yearly'  => '%b %Y',
            'custom'  => '%d %b %Y',
            default   => '%d %b',   // monthly
        };
    }

    /* =========================================================
     | 1. Summary KPIs
     ========================================================= */

    public function getSummary(Request $request)
    {
        try {
            $riderId = $request->header('id');
            [$start, $end] = $this->getDateRange($request);

            $row = DeliveryChargeLedger::where('delivery_person_id', $riderId)
                ->whereBetween('delivery_date', [$start, $end])
                ->selectRaw("
                    COUNT(*)                                                                     AS total_deliveries,
                    COUNT(CASE WHEN delivery_status = 'delivered' THEN 1 END)                    AS delivered,
                    COUNT(CASE WHEN delivery_status = 'cancelled' THEN 1 END)                    AS cancelled,
                    COUNT(CASE WHEN delivery_status NOT IN ('delivered','cancelled') THEN 1 END) AS in_progress,
                    COALESCE(SUM(delivery_charge), 0)                                            AS total_delivery_charge,
                    COALESCE(SUM(rider_platform_fee), 0)                                         AS total_platform_fee,
                    COALESCE(SUM(payable_amount), 0)                                             AS total_earnings,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN payable_amount END), 0)  AS paid_earnings,
                    COALESCE(SUM(CASE WHEN payment_status = 'due'  THEN payable_amount END), 0)  AS due_earnings
                ")
                ->first();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'total_deliveries'      => (int) $row->total_deliveries,
                    'delivered'             => (int) $row->delivered,
                    'cancelled'             => (int) $row->cancelled,
                    'in_progress'           => (int) $row->in_progress,
                    'total_delivery_charge' => round((float) $row->total_delivery_charge, 2),
                    'total_platform_fee'    => round((float) $row->total_platform_fee, 2),
                    'total_earnings'        => round((float) $row->total_earnings, 2),
                    'paid_earnings'         => round((float) $row->paid_earnings, 2),
                    'due_earnings'          => round((float) $row->due_earnings, 2),
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Delivery getSummary: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | 2. Order Report — bar chart (period) + table (per invoice)
     ========================================================= */

    public function getOrderReport(Request $request)
    {
        try {
            $riderId = $request->header('id');
            [$start, $end, $period] = $this->getDateRange($request);
            $format = $this->getGroupFormat($period);

            // ---- Chart: grouped by period ----
            $chartRows = DeliveryChargeLedger::where('delivery_person_id', $riderId)
                ->whereBetween('delivery_date', [$start, $end])
                ->selectRaw("
                    DATE_FORMAT(delivery_date, ?) AS label,
                    MIN(delivery_date)            AS sort_date,
                    COUNT(*)                                                  AS total,
                    COUNT(CASE WHEN delivery_status = 'delivered' THEN 1 END) AS delivered,
                    COUNT(CASE WHEN delivery_status = 'cancelled' THEN 1 END) AS cancelled
                ", [$format])
                ->groupBy('label')
                ->orderBy('sort_date')
                ->get();

            // ---- Table: grouped by invoice (meal order) ----
            $tableRows = DeliveryChargeLedger::query()
                ->join('meal_orders', 'meal_orders.id', '=', 'delivery_charge_ledgers.meal_order_id')
                ->where('delivery_charge_ledgers.delivery_person_id', $riderId)
                ->whereBetween('delivery_charge_ledgers.delivery_date', [$start, $end])
                ->selectRaw("
                    meal_orders.invoice_no                          AS invoice_no,
                    MIN(delivery_charge_ledgers.delivery_date)      AS sort_date,
                    COUNT(*)                                                                          AS total,
                    COUNT(CASE WHEN delivery_charge_ledgers.delivery_status = 'delivered' THEN 1 END) AS delivered,
                    COUNT(CASE WHEN delivery_charge_ledgers.delivery_status = 'cancelled' THEN 1 END) AS cancelled,
                    COALESCE(SUM(delivery_charge_ledgers.delivery_charge), 0)    AS delivery_charge,
                    COALESCE(SUM(delivery_charge_ledgers.rider_platform_fee), 0) AS platform_fee,
                    COALESCE(SUM(delivery_charge_ledgers.payable_amount), 0)     AS earnings
                ")
                ->groupBy('meal_orders.invoice_no')
                ->orderBy('sort_date')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    // chart series
                    'labels'    => $chartRows->pluck('label'),
                    'delivered' => $chartRows->pluck('delivered'),
                    'cancelled' => $chartRows->pluck('cancelled'),
                    // table (per invoice)
                    'table'     => $tableRows->map(fn($r) => [
                        'period'          => $r->sort_date ? Carbon::parse($r->sort_date)->format('d M Y') : '-',
                        'invoice_no'      => $r->invoice_no ?? '-',
                        'total'           => (int) $r->total,
                        'delivered'       => (int) $r->delivered,
                        'cancelled'       => (int) $r->cancelled,
                        'delivery_charge' => round((float) $r->delivery_charge, 2),
                        'platform_fee'    => round((float) $r->platform_fee, 2),
                        'earnings'        => round((float) $r->earnings, 2),
                    ]),
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Delivery getOrderReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | 3. Delivery Status Report — pie
     ========================================================= */

    public function getOrderStatusReport(Request $request)
    {
        try {
            $riderId = $request->header('id');
            [$start, $end] = $this->getDateRange($request);

            $rows = DeliveryChargeLedger::where('delivery_person_id', $riderId)
                ->whereBetween('delivery_date', [$start, $end])
                ->selectRaw('delivery_status, COUNT(*) AS total')
                ->groupBy('delivery_status')
                ->get();

            $labels = DeliveryChargeLedger::STATUS_LABELS;

            return response()->json([
                'status' => 'success',
                'data'   => $rows->map(fn($r) => [
                    'status' => $r->delivery_status,
                    'label'  => $labels[$r->delivery_status] ?? ucfirst(str_replace('_', ' ', $r->delivery_status)),
                    'total'  => (int) $r->total,
                ]),
            ], 200);

        } catch (Exception $e) {
            Log::error('Delivery getOrderStatusReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | 4. Payment Status Report — doughnut
     ========================================================= */

    public function getPaymentStatusReport(Request $request)
    {
        try {
            $riderId = $request->header('id');
            [$start, $end] = $this->getDateRange($request);

            $rows = DeliveryChargeLedger::where('delivery_person_id', $riderId)
                ->whereBetween('delivery_date', [$start, $end])
                ->selectRaw('payment_status, COUNT(*) AS total, COALESCE(SUM(payable_amount),0) AS amount')
                ->groupBy('payment_status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $rows->map(fn($r) => [
                    'status' => $r->payment_status,
                    'label'  => ucfirst(str_replace('_', ' ', $r->payment_status)),
                    'total'  => (int) $r->total,
                    'amount' => round((float) $r->amount, 2),
                ]),
            ], 200);

        } catch (Exception $e) {
            Log::error('Delivery getPaymentStatusReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | 5. Earnings Report — line
     ========================================================= */

    public function getEarningsReport(Request $request)
    {
        try {
            $riderId = $request->header('id');
            [$start, $end, $period] = $this->getDateRange($request);
            $format = $this->getGroupFormat($period);

            $rows = DeliveryChargeLedger::where('delivery_person_id', $riderId)
                ->whereBetween('delivery_date', [$start, $end])
                ->selectRaw("
                    DATE_FORMAT(delivery_date, ?) AS label,
                    MIN(delivery_date)            AS sort_date,
                    COALESCE(SUM(payable_amount), 0)     AS payable,
                    COALESCE(SUM(rider_platform_fee), 0) AS platform_fee,
                    COALESCE(SUM(delivery_charge), 0)    AS gross
                ", [$format])
                ->groupBy('label')
                ->orderBy('sort_date')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'labels'       => $rows->pluck('label'),
                    'payable'      => $rows->pluck('payable')->map(fn($v) => round((float) $v, 2)),
                    'gross'        => $rows->pluck('gross')->map(fn($v) => round((float) $v, 2)),
                    'platform_fee' => $rows->pluck('platform_fee')->map(fn($v) => round((float) $v, 2)),
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Delivery getEarningsReport: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}