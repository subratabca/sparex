<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\ClientOrder;
use App\Models\Product;

class ClientReportController extends Controller
{
    public function todaysOrderPage()
    {
        return view('client.pages.report.today-order-page');
    }

    public function todaysOrderInfo(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $today = Carbon::today();

            // Get today's client orders for the specific client
            $clientOrders = ClientOrder::with(['order.customer'])
                ->where('client_id', $client_id)
                ->whereDate('created_at', $today)
                ->latest()
                ->get();

            // Calculate totals by status
            $totalOrders = $clientOrders->count();
            $totalPendingOrders = $clientOrders->where('status', 'pending')->count();
            $totalApprovedOrders = $clientOrders->where('status', 'approved')->count();
            $totalDeliveredOrders = $clientOrders->where('status', 'delivered')->count();
            $totalCanceledOrders = $clientOrders->where('status', 'canceled')->count();
            $totalPartiallyApproved = $clientOrders->where('status', 'partially_approved')->count();
            $totalPartiallyDeliveredOrders = $clientOrders->where('status', 'partially_delivered')->count();
            $totalSales = $clientOrders->sum('payable_amount');

            return response()->json([
                'status' => 'success',
                'data' => $clientOrders,
                'total_orders' => $totalOrders,
                'total_pending_orders' => $totalPendingOrders,
                'total_approved_orders' => $totalApprovedOrders,
                'total_delivered_orders' => $totalDeliveredOrders,
                'total_canceled_orders' => $totalCanceledOrders,
                'total_partially_approved_orders' => $totalPartiallyApproved,
                'total_partially_delivered_orders' => $totalPartiallyDeliveredOrders,
                'total_sales' => $totalSales,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving client orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderBySearchPage(Request $request)
    {
       return view('client.pages.report.search-order-page');
    }

    public function orderBySearch(Request $request)
    {
        try {
            $request->validate([
                'date'       => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date',
            ]);

            $client_id = $request->header('id');

            if (!$client_id) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Client ID not found in header.'
                ], 400);
            }

            // ---- Determine filter type ----
            if ($request->filled('date')) {
                // Single Date Filter
                $date = Carbon::parse($request->date)->format('Y-m-d');

                $clientOrders = ClientOrder::with(['order.customer'])
                    ->where('client_id', $client_id)
                    ->whereDate('created_at', $date)
                    ->latest()
                    ->get();

            } elseif ($request->filled('start_date') && $request->filled('end_date')) {
                // Date Range Filter
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate   = Carbon::parse($request->end_date)->endOfDay();

                $clientOrders = ClientOrder::with(['order.customer'])
                    ->where('client_id', $client_id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->get();
            } else {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Please provide a valid search input.'
                ], 400);
            }

            // ---- Extract Orders ----
            $orders = $clientOrders->pluck('order')->filter();

            // ---- Calculate Stats ----
            $totalOrders = $orders->count();
            $totalSales = $orders->sum('payable_amount');
            $totalPendingOrders = $orders->where('status', 'pending')->count();
            $totalDeliveredOrders = $orders->where('status', 'delivered')->count();
            $totalCanceledOrders = $orders->where('status', 'canceled')->count();
            $totalPartiallyApproved = $orders->where('status', 'partially_approved')->count();
            $totalPartiallyDelivered = $orders->where('status', 'partially_delivered')->count();

            // ---- Response ----
            return response()->json([
                'status'  => 'success',
                'data'    => $orders->values(), // ensure re-indexed array
                'total_orders' => $totalOrders,
                'total_sales'  => $totalSales,
                'total_pending_orders' => $totalPendingOrders,
                'total_delivered_orders' => $totalDeliveredOrders,
                'total_canceled_orders' => $totalCanceledOrders,
                'total_partially_approved_orders' => $totalPartiallyApproved,
                'total_partially_delivered_orders' => $totalPartiallyDelivered,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while searching for orders.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function currentStockPage()
    {
        return view('client.pages.report.current-stock-page');
    }


    public function currentStockInfo(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $products = Product::with('productImages','variants','category','brand')->where('client_id',$client_id)->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $products
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stockDetailsPage(Request $request)
    {
        return view('client.pages.report.product-stock-details');
    }

    public function getProductStockInfo(Request $request,$id)
    {
        try {
            $product = Product::with('productImages','client','variants','category', 'brand', 'country','county','city')->find($id);

            if (!$product) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $product
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}