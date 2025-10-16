<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Complaint;
use Exception;

class DashboardController extends Controller
{
    public function DashboardPage()
    {
        return view('frontend.pages.dashboard.dashboard-page');
    }


    public function TotalInfo(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            $totalOrders = Order::where('customer_id', $customer_id)->count();

            $totalComplaints = Complaint::where('customer_id', $customer_id)->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Orders and complaints found successfully.',
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