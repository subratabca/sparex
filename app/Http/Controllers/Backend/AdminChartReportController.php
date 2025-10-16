<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Order;
use Carbon\Carbon;

class AdminChartReportController extends Controller
{
    public function ChartOrderPage()
    {
        return view('backend.pages.chart-report.order-bar-chart');
    }

    public function getOrderChartData(Request $request)
    {
        $filter = $request->query('filter', 'last_7_days'); // Default to last 7 days
        $startDate = now();
        $endDate = now();

        switch ($filter) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $startDate = now()->subDays(6)->startOfDay();
                break;
            case 'last_30_days':
                $startDate = now()->subDays(29)->startOfDay();
                break;
            case 'current_month':
                $startDate = now()->startOfMonth();
                break;
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'weekly': // Added weekly filter
                $startDate = now()->subWeek()->startOfWeek();
                break;
        }

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'data' => [
                'labels' => $orders->pluck('date'),
                'orders' => $orders->pluck('count'),
            ]
        ]);
    }

    public function weeklyOrders()
    {
        try {
            // Get orders from the last 7 days
            $weeklyOrders = Order::whereDate('order_date', '>=', Carbon::now()->subDays(7)->toDateString())
                ->selectRaw('client_id, COUNT(*) as total_orders')
                ->groupBy('client_id')
                ->with('client') // Ensure the client relationship is loaded
                ->get();

            if ($weeklyOrders->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No orders found for the past 7 days.',
                    'data' => [
                        'labels' => [],
                        'orders' => []
                    ]
                ], 404);
            }

            $labels = [];
            $orders = [];

            foreach ($weeklyOrders as $order) {
                if ($order->client) { // Ensure client exists
                    $labels[] = $order->client->firstName . ' ' . ($order->client->lastName ?? '');
                    $orders[] = $order->total_orders;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'labels' => $labels,
                    'orders' => $orders
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch weekly orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function Orders(Request $request)
    {
        try {
            // Get the filter type from the request
            $filter = $request->input('filter', 'last_7_days');

            // Define the start and end dates based on the selected filter
            switch ($filter) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today();
                    break;
                case 'yesterday':
                    $startDate = Carbon::yesterday();
                    $endDate = Carbon::yesterday();
                    break;
                case 'last_7_days':
                    $startDate = Carbon::now()->subDays(7);
                    $endDate = Carbon::now();
                    break;
                case 'last_30_days':
                    $startDate = Carbon::now()->subDays(30);
                    $endDate = Carbon::now();
                    break;
                case 'current_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                default:
                    $startDate = Carbon::now()->subDays(7);
                    $endDate = Carbon::now();
                    break;
            }

            // Fetch orders within the selected date range
            $orders = Order::whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('client_id, COUNT(*) as total_orders')
                ->groupBy('client_id')
                ->with('client') 
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No orders found for the selected period.',
                    'data' => [
                        'labels' => [],
                        'orders' => []
                    ]
                ], 404);
            }

            $labels = [];
            $orderCounts = [];

            foreach ($orders as $order) {
                if ($order->client) { 
                    $labels[] = $order->client->firstName . ' ' . ($order->client->lastName ?? '');
                    $orderCounts[] = $order->total_orders;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'labels' => $labels,
                    'orders' => $orderCounts
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

    