<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Notifications\Customer\CustomerAccountActivationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException; 
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\Complaint;
use App\Models\CustomerComplaint;
use App\Models\Follower;
use App\Models\Cart;
use Exception;

class CustomerListController extends Controller
{
    public function updateCustomerAccount(Request $request, $customer_id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|boolean',
            ]);

            $customer = User::withLocation()->findOrFail($customer_id);
            if (!$customer->isCustomer()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The specified user is not a customer',
                ], 422);
            }

            $customer->status = $validated['status'];
            $customer->save();

            $message = $customer->status 
                ? 'Customer account has been successfully activated!'
                : 'Customer account has been successfully deactivated!';

            $customer->notify(new CustomerAccountActivationNotification($customer));

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating customer account'
            ], 500);
        }
    }

    public function customerPage()
    {
        return view('backend.pages.customer.customer-list');
    }

    public function getCustomerList(Request $request)
    {
        try {
            $customers = User::withCount(['orders', 'productComplaints', 'receivedComplaints'])
                ->where('role', 'customer')
                ->get()
                ->map(function ($customer) {
                    $clientIds = OrderItem::whereHas('order', function($query) use ($customer) {
                            $query->where('customer_id', $customer->id);
                        })
                        ->distinct('client_id')
                        ->pluck('client_id');

                    $totalClients = $clientIds->count();
                    $totalOrders = $customer->orders_count;
                    $totalProductComplaints = $customer->product_complaints_count; 
                    $totalReceivedComplaints = $customer->received_complaints_count;

                    return [
                        'id' => $customer->id,
                        'firstName' => $customer->firstName,
                        'lastName' => $customer->lastName,
                        'image' => $customer->image ?: null,
                        'status' => (int) $customer->status,
                        'total_clients' => $totalClients,
                        'total_orders' => $totalOrders,
                        'total_product_complaints' => $totalProductComplaints,
                        'total_received_omplaints' => $totalReceivedComplaints, 
                    ];
                });

            if ($customers->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No customers found.'
                ], 404);
            }

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

    public function customerDetailsPage(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', $email)->first();

        $notification_id = $request->query('notification_id');
        if ($notification_id) {
            $notification = $user->notifications()->where('id', $notification_id)->first();

            if ($notification && is_null($notification->read_at)) {
                $notification->markAsRead();
            }
        }
        
        return view('backend.pages.customer.customer-details');
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
                'is_email_verified' => $customer->is_email_verified,
                'image' => $customer->image ?: null,
                'image' => $customer->image ?: null,
                'doc_image1' => $customer->doc_image1 ?: null,
                'doc_image2' => $customer->doc_image2 ?: null,
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
        return view('backend.pages.customer.order-list-by-customer');
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
        return view('backend.pages.customer.complain-list-by-customer');
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

    public function clientListPageByCustomer()
    {
        return view('backend.pages.customer.client-list-by-customer');
    }

    public function getClientListByCustomer($customer_id)
    {
        try {
            $customer = User::findOrFail($customer_id);
            $clientIds = OrderItem::whereHas('order', function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })
            ->pluck('client_id')
            ->unique()
            ->toArray();

            $clients = User::withCount('products')
                ->whereIn('id', $clientIds)
                ->where('role', 'client')
                ->get(['id', 'image', 'firstName', 'lastName', 'email', 'mobile', 'status']);

            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No clients found.'
                ], 404);
            }

            $clientData = $clients->map(function ($client) use ($customer) {
                return [
                    'id' => $client->id,
                    'image' => $client->image,
                    'firstName' => $client->firstName,
                    'lastName' => $client->lastName,
                    'email' => $client->email,
                    'mobile' => $client->mobile,
                    'status' => $client->status,
                    'products_count' => $client->products_count,
                    'customer_firstName' => $customer->firstName,
                    'customer_lastName' => $customer->lastName,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Client list retrieved successfully',
                'data' => $clientData
            ],200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function CustomerComplainListPageByCustomer()
    {
        return view('backend.pages.customer.customer-complain-list-by-customer');
    }

    public function CustomerComplainListInfoByCustomer($customer_id)
    {
        try {
            $customerComplain = CustomerComplain::with('client','customer')->where('customer_id', $customer_id)->get(); 
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

    private function deleteImagesFromHTML($htmlContent)
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $htmlContent, $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $imageUrl) {
                $imagePath = ltrim(parse_url($imageUrl, PHP_URL_PATH), '/');
                $fullImagePath = public_path($imagePath);
                if (File::exists($fullImagePath)) {
                    File::delete($fullImagePath);
                }
            }
        }
    }

    public function delete(Request $request)
    {
        try { 
            $customer_id = $request->input('customer_id');  
            $customer = User::where('role', 'customer')->findOrFail($customer_id); 

            DB::beginTransaction();

            foreach ($customer->orders as $order) {
                $order->orderItems()->delete();
                
                $order->clientOrders()->delete();
                
                if ($order->shippingAddress) {
                    $order->shippingAddress()->delete();
                }

                $order->delete();  
            }

            if ($customer->productComplaints->isNotEmpty()) {
                foreach ($customer->productComplaints as $complaint) {
                    if ($complaint->conversations->isNotEmpty()) {
                        foreach ($complaint->conversations as $conversation) {
                            if (!empty($conversation->reply_message)) {
                                $this->deleteImagesFromHTML($conversation->reply_message);
                            }
                            $conversation->delete();
                        }
                    }

                    if (!empty($complaint->message)) {
                        $this->deleteImagesFromHTML($complaint->message);
                    }
                    $complaint->delete();
                }
            }

            if ($customer->receivedComplaints->isNotEmpty()) {
                foreach ($customer->receivedComplaints as $receivedComplaint) {
                    if ($receivedComplaint->customerComplaintConversations->isNotEmpty()) {
                        foreach ($receivedComplaint->customerComplaintConversations as $conversation) {
                            if (!empty($conversation->reply_message)) {
                                $this->deleteImagesFromHTML($conversation->reply_message);
                            }
                            $conversation->delete();
                        }
                    }

                    if (!empty($receivedComplaint->message)) {
                        $this->deleteImagesFromHTML($receivedComplaint->message);
                    }

                    $receivedComplaint->delete();
                }
            }


            $customer->bannedCustomers()->delete();
            $customer->bannedByClients()->delete();
            $customer->followers()->delete();
            $customer->productShares()->delete();

            Cart::where('customer_id', $customer_id)->delete();

            // Delete document images
            $customer_document_paths = [
                'large' => public_path('upload/customer-document/large/'),
                'medium' => public_path('upload/customer-document/medium/'),
                'small' => public_path('upload/customer-document/small/')
            ];

            if (!empty($customer->doc_image1)) {
                foreach ($customer_document_paths as $path) {
                    $docImage1Path = $path . $customer->doc_image1;
                    if (File::exists($docImage1Path)) {
                        File::delete($docImage1Path); 
                    }
                }
            }

            if (!empty($customer->doc_image2)) {
                foreach ($customer_document_paths as $path) {
                    $docImage2Path = $path . $customer->doc_image2;
                    if (File::exists($docImage2Path)) {
                        File::delete($docImage2Path); 
                    }
                }
            }

            // Delete profile image
            $customer_profile_paths = [
                'large' => public_path('upload/customer-profile/large/'),
                'medium' => public_path('upload/customer-profile/medium/'),
                'small' => public_path('upload/customer-profile/small/')
            ];

            if (!empty($customer->image)) {
                foreach ($customer_profile_paths as $path) {
                    $imagePath = $path . $customer->image;
                    if (File::exists($imagePath)) {
                        File::delete($imagePath); 
                    }
                }
            }

            // Finally delete the customer
            $customer->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer and all related data deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}