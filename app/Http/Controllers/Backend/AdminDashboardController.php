<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Complaint;

class AdminDashboardController extends Controller
{
    public function DashboardPage()
    {
        return view('backend.pages.dashboard.dashboard-page');
    }


    public function TotalInfo(Request $request)
    {
        try {
            $totalClients = User::where('role', 'client')->count();
            $totalCustomers = User::where('role', 'customer')->count();
            $totalProducts = Product::count();
            $totalOrders = Order::count();
            $totalComplaints = Complaint::count();

            return response()->json([
                'status' => 'success',
                'totalClients' => $totalClients,
                'totalCustomers' => $totalCustomers,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
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
