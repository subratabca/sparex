<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ClientOrder;
use App\Models\Complaint;


class ClientDashboardController extends Controller
{
    public function DashboardPage()
    {
        return view('client.pages.dashboard.dashboard-page');
    }

    public function TotalInfo(Request $request)
    {
        try {
            $clientId = $request->header('id');

            $totalProducts = Product::where('client_id', $clientId)->count();
            $totalOrders = ClientOrder::where('client_id', $clientId)->count();
            $totalCustomers = ClientOrder::where('client_id', $clientId)
                ->join('orders', 'client_orders.order_id', '=', 'orders.id')
                ->select(DB::raw('COUNT(DISTINCT orders.customer_id) as count'))
                ->value('count');

            $totalComplaints = Complaint::whereHas('orderItem.product', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })->count();

            return response()->json([
                'status' => 'success',
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'totalCustomers' => $totalCustomers,
                'totalComplaints' => $totalComplaints,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving totals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function Logout()
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ], 200)->withCookie(cookie()->forget('token'));
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while logging out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
