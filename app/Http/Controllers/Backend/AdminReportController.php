<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\ClientOrder;
use App\Models\Product;

class AdminReportController extends Controller
{
    public function todaysOrderPage()
    {
        return view('backend.pages.report.todays-order-page');
    }

    public function todaysOrderInfo(Request $request)
    {
        try {
            // Get today's date in Y-m-d format
            $today = Carbon::today();
            $orders = Order::with('customer')
                ->whereDate('created_at', $today)
                ->latest()
                ->get();

            $totalOrders = $orders->count();
            $totalPendingOrders = $orders->where('status', 'pending')->count();
            $totalApprovedOrders = $orders->where('status', 'approved')->count();
            $totalDeliveredOrders = $orders->where('status', 'delivered')->count();
            $totalCanceledOrders = $orders->where('status', 'canceled')->count();
            $totalPartiallyApproved = $orders->where('status', 'partially_approved')->count();
            $totalPartiallyDeliveredOrders = $orders->where('status', 'partially_delivered')->count();
            $totalSale = $orders->sum('payable_amount');

            return response()->json([
                'status' => 'success',
                'data' => $orders,
                'total_orders' => $totalOrders,
                'total_pending_orders' => $totalPendingOrders,
                'total_approved_orders' => $totalApprovedOrders,
                'total_delivered_orders' => $totalDeliveredOrders,
                'total_canceled_orders' => $totalCanceledOrders,
                'total_partially_approved_orders' => $totalPartiallyApproved,
                'total_partially_delivered_orders' => $totalPartiallyDeliveredOrders,
                'total_sales' => $totalSale,
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderBySearchPage(Request $request)
    {
       return view('backend.pages.report.search-order-page');
    }

    public function orderBySearch(Request $request)
    {
        try {
            $request->validate([
                'date'              => 'nullable|date',
                'start_date'        => 'nullable|date',
                'end_date'          => 'nullable|date',
                'client_start_date' => 'nullable|date',
                'client_end_date'   => 'nullable|date',
                'client_id'         => 'nullable|exists:users,id',
            ]);

            // Case 1: Single Date
            if ($request->has('date')) {
                $date = Carbon::parse($request->date)->format('Y-m-d');

                $orders = Order::with('customer')
                    ->whereDate('created_at', $date)
                    ->latest()
                    ->get();

            // Case 2: Date Range (orders)
            } elseif ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate   = Carbon::parse($request->end_date)->endOfDay();

                $orders = Order::with('customer')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->get();

            // ✅ Case 3: Client Orders (client_orders table)
            } elseif ($request->has('client_start_date') && $request->has('client_end_date') && $request->has('client_id')) {
                $startDate = Carbon::parse($request->client_start_date)->startOfDay();
                $endDate   = Carbon::parse($request->client_end_date)->endOfDay();

                $clientOrders = ClientOrder::with(['order.customer', 'client'])
                    ->where('client_id', $request->client_id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->get();

                // Normalize to orders (so frontend receives same structure)
                $orders = $clientOrders->map(function ($co) {
                    return $co->order;
                });

            } else {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Please provide a valid search input',
                ], 400);
            }

            // ---- Stats ----
            $totalOrders = $orders->count();
            $totalPendingOrders = $orders->where('status', 'pending')->count();
            $totalApprovedOrders = $orders->where('status', 'approved')->count();
            $totalDeliveredOrders = $orders->where('status', 'delivered')->count();
            $totalCanceledOrders = $orders->where('status', 'canceled')->count();
            $totalPartiallyApproved = $orders->where('status', 'partially_approved')->count();
            $totalPartiallyDeliveredOrders = $orders->where('status', 'partially_delivered')->count();
            $totalSale = $orders->sum('payable_amount');

            return response()->json([
                'status' => 'success',
                'data' => $orders,
                'total_orders' => $totalOrders,
                'total_pending_orders' => $totalPendingOrders,
                'total_approved_orders' => $totalApprovedOrders,
                'total_delivered_orders' => $totalDeliveredOrders,
                'total_canceled_orders' => $totalCanceledOrders,
                'total_partially_approved_orders' => $totalPartiallyApproved,
                'total_partially_delivered_orders' => $totalPartiallyDeliveredOrders,
                'total_sales' => $totalSale,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while searching for orders',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function currentStockPage()
    {
        return view('backend.pages.report.current-stock-page');
    }

    public function currentStockInfo(Request $request)
    {
        try {
            $products = Product::with('productImages','variants','category','brand','client')->latest()->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No orders found.'
                ], 404);
            }

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
        return view('backend.pages.report.product-stock-details');
    }

    public function getProductStockInfo(Request $request,$id)
    {
        try {
            $product = Product::with('productImages','client','variants','category', 'brand', 'country','county','city')->find($id);

            if (!$product) {
                ActivityLogger::log(
                    'retrieve_item_failed',
                    'Product not found.',
                    $request,
                    'products'
                );
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