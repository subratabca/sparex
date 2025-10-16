<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\Complaint;
use App\Models\CustomerComplaint;
use App\Models\BannedCustomer;
use Exception;

class ClientCustomerListController extends Controller
{
    public function customerPage()
    {
        return view('client.pages.customer.customer-list');
    }

    public function getCustomerList(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $orderIds = ClientOrder::where('client_id', $client_id)
                ->pluck('order_id')
                ->unique()
                ->toArray();

            $customerIds = Order::whereIn('id', $orderIds)
                ->pluck('customer_id')
                ->unique()
                ->toArray();

            $customers = User::whereIn('id', $customerIds)
                ->withCount(['orders', 'productComplaints', 'receivedComplaints'])
                ->get()
                ->map(function ($customer) use ($client_id) {
                    $isBanned = BannedCustomer::where('client_id', $client_id)
                        ->where('customer_id', $customer->id)
                        ->exists();

                    return [
                        'id' => $customer->id,
                        'firstName' => $customer->firstName,
                        'lastName' => $customer->lastName,
                        'image' => $customer->image ?: null,
                        'status' => (int) $customer->status,
                        'total_orders' => $customer->orders_count,
                        'total_product_complaints' => $customer->product_complaints_count,
                        'total_received_complaints' => $customer->received_complaints_count,
                        'is_banned' => $isBanned
                    ];
                });

            // if ($customers->isEmpty()) {
            //     return response()->json([
            //         'status' => 'failed',
            //         'message' => 'No customers found.'
            //     ], 404);
            // }

            return response()->json([
                'status' => 'success',
                'message' => 'Customer list retrieved successfully',
                'data' => $customers
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function customerDetailsPage()
    {
        return view('client.pages.customer.customer-details');
    }


    public function getCustomerDetails($customer_id)
    {
        try {
            $customer = User::withCount(['orders', 'productComplaints', 'receivedComplaints'])
                ->withLocation()
                ->where('role', 'customer')
                ->where('id', $customer_id)
                ->first();

            if (!$customer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No customer found with this ID',
                ], 404);
            }

            $totalClients = OrderItem::whereHas('order', function($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->distinct('client_id')
                ->count('client_id');

            // Prepare the response data
            $customerData = [
                'id' => $customer->id,
                'firstName' => $customer->firstName,
                'lastName' => $customer->lastName,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'image' => $customer->image ?: null,
                'status' => (int) $customer->status,
                'address1' => $customer->address1,
                'address2' => $customer->address2,
                'zip_code' => $customer->zip_code,
                'country' => $customer->country,
                'county' => $customer->county,
                'city' => $customer->city,
                'total_clients' => $totalClients,
                'total_orders' => $customer->orders_count,
                'total_product_complaints' => $customer->product_complaints_count,
                'total_received_complaints' => $customer->received_complaints_count,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Customer details retrieved successfully',
                'data' => $customerData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer details',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function orderListPageByCustomer()
    {
        return view('client.pages.customer.order-list-by-customer');
    }

    public function getOrderListByCustomer($customerId)
    {
        try {
            $orders = Order::with(['customer','orderItems.client'])
                ->where('customer_id', $customerId)
                ->latest()
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No orders found for this customer.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function complaintListPageByCustomer()
    {
        return view('client.pages.customer.complain-list-by-customer');
    }

    public function getComplaintListByCustomer($customer_id)
    {
        try {
            $complaints = Complaint::where('customer_id', $customer_id)
                ->with([
                    'order:id,order_number',
                    'product:id,name,client_id',
                    'product.client:id,firstName,lastName',
                    'customer:id,firstName,lastName'
                ])->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $complaints
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function CustomerComplainListPageByCustomer()
    {
        return view('client.pages.customer.customer-complain-list-by-customer');
    }


    public function CustomerComplainListInfoByCustomer(Request $request,$customer_id)
    {
        try {
            $client_id = $request->header('id');
            $customerComplain = CustomerComplain::with('client','customer')->where('client_id', $client_id)->where('customer_id', $customer_id)->get(); 
            return response()->json([
                'status' => 'success',
                'data' => $customerComplain
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}