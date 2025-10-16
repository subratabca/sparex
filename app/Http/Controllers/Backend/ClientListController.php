<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Notifications\Client\ClientAccountActivationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException; 
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientOrder;
use App\Models\Complaint;
use Exception;

class ClientListController extends Controller
{
    public function updateClientAccount(Request $request, $client_id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|boolean',
            ]);

            $client = User::withLocation()->findOrFail($client_id);
            if (!$client->isClient()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The specified user is not a client',
                ], 422);
            }

            $client->status = $validated['status'];
            $client->save();

            $message = $client->status 
                ? 'Client account has been successfully activated!'
                : 'Client account has been successfully deactivated!';

            $client->notify(new ClientAccountActivationNotification($client));

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
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
                'message' => 'An error occurred while updating client account'
            ], 500);
        }
    }

    public function clientPage()
    {
        return view('backend.pages.client.client-list');
    }

    public function getClientList(Request $request)
    {
        try {
            $clients = User::withCount(['clientOrders'])
                ->where('role', 'client')
                ->get()
                ->map(function ($client) {
                    $productIds = Product::where('client_id', $client->id)
                        ->where('status', '!=', 'pending')
                        ->pluck('id');

                    $totalProducts = $productIds->count();
                    
                    $totalCustomers = OrderItem::where('client_id', $client->id)
                        ->whereHas('order', function($query) {
                            $query->whereNotNull('customer_id');
                        })
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->distinct('orders.customer_id')
                        ->count('orders.customer_id');

                    $totalOrders = $client->client_orders_count;
                    $totalComplaints = Complaint::whereIn('product_id', $productIds)->count();

                    return [
                        'id' => $client->id,
                        'firstName' => $client->firstName,
                        'lastName' => $client->lastName,
                        'image' => $client->image ?: null,
                        'status' => (int) $client->status,
                        'total_products' => $totalProducts,
                        'total_customers' => $totalCustomers,
                        'total_orders' => $totalOrders,
                        'total_complaints' => $totalComplaints,
                    ];
                });

            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No clients found.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Client list retrieved successfully',
                'data' => $clients
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clientDetailsPage(Request $request)
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
        
        return view('backend.pages.client.client-details');
    }

    public function getClientDetails($client_id)
    {
        try {
            $client = User::withCount(['clientOrders'])
                ->withLocation() 
                ->where('role', 'client')
                ->where('id', $client_id)
                ->first();

            if (!$client) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No client found with this ID',
                ], 404);
            }

            $productIds = Product::where('client_id', $client->id)
                ->where('status', '!=', 'pending')
                ->pluck('id');

            $totalProducts = $productIds->count();

            $totalCustomers = OrderItem::where('client_id', $client->id)
                ->whereHas('order', function($query) {
                    $query->whereNotNull('customer_id');
                })
                ->distinct('order_id')
                ->count('order_id');

            $totalOrders = $client->client_orders_count;
            $totalComplaints = Complaint::whereIn('product_id', $productIds)->count();

            $client->total_products = $totalProducts;
            $client->total_customers = $totalCustomers;
            $client->total_orders = $totalOrders;
            $client->total_complaints = $totalComplaints;
            return response()->json([
                'status' => 'success',
                'data' => $client
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving the customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function productListPageByClient()
    {
        return view('backend.pages.client.product-list-by-client');
    }

    public function getProductListByClient($client_id)
    {
        try {
            $products = Product::with('client','category')->where('client_id', $client_id)->latest()->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No products found.'
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

    public function orderListPageByClient()
    {
        return view('backend.pages.client.order-list-by-client');
    }
    
    public function getOrderListByClient($client_id)
    {
        try {
            $orders = ClientOrder::with('order.customer','client')->where('client_id', $client_id)->latest()->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No orders found.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complaintListPageByClient()
    {
        return view('backend.pages.client.complain-list-by-client');
    }

    public function getComplaintListByClient($client_id)
    {
        try {

            $complaints = Complaint::whereHas('product', function ($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->with([
                    'order:id,order_number,order_date,order_time',
                    'product:id,name,image,client_id',
                    'product.client:id,firstName,lastName',
                    'customer:id,firstName,lastName,email,mobile,image'
                ])
                ->latest()
                ->get();

            if ($complaints->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No complaints found.'
                ], 404);
            }

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

    public function CustomerListPageByClient()
    {
        return view('backend.pages.client.customer-list-by-client');
    }

    public function getCustomerListByClient($client_id)
    {
        try {
            $customerIds = OrderItem::where('client_id', $client_id)
                ->with('order')
                ->get()
                ->pluck('order.customer_id')
                ->unique()
                ->values()
                ->toArray();

            $customers = User::whereIn('id', $customerIds)
                ->where('role', 'customer')
                ->latest()
                ->get();

            if ($customers->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No customers found.'
                ], 404);
            }

            $customers = $customers->map(function ($customer) use ($client_id) {
                $orderCount = Order::where('customer_id', $customer->id)
                    ->whereHas('orderItems', function($query) use ($client_id) {
                        $query->where('client_id', $client_id);
                    })
                    ->count();

                return [
                    'id' => $customer->id,
                    'firstName' => $customer->firstName,
                    'lastName' => $customer->lastName,
                    'email' => $customer->email,
                    'mobile' => $customer->mobile,
                    'image' => $customer->image,
                    'created_at' => $customer->created_at,
                    'order_count' => $orderCount,
                ];
            });

            return response()->json([
                'status' => 'success',
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
            DB::beginTransaction();

            $client_id = $request->input('client_id');
            $client = User::where('role', 'client')->findOrFail($client_id);

            // Delete client products and related data
            if ($client->products) {
                foreach ($client->products as $product) {

                    // Delete product variants
                    $product->productVariants()->delete();

                    // Delete stock movements
                    $product->stockMovements()->delete();

                    // Delete product shares
                    $product->productShares()->delete();

                    // Delete wishlists
                    $product->wishlists()->delete();

                    // Delete product images
                    foreach ($product->productImages as $productImage) {
                        $imageFile = base_path('public/upload/product/multiple/') . $productImage->image;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                        $productImage->delete();
                    }

                    // Delete main product images
                    foreach (['large', 'medium', 'small'] as $size) {
                        $imagePath = base_path("public/upload/product/$size/") . $product->image;
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }

                    // Delete orders related to the product
                    if ($product->order) {
                        $order = $product->order;

                        // Delete complaint conversations and images
                        if ($order->complaint) {
                            $complaint = $order->complaint;
                            foreach ($complaint->conversations as $conversation) {
                                if (!empty($conversation->reply_message)) {
                                    $this->deleteImagesFromHTML($conversation->reply_message);
                                }
                                $conversation->delete();
                            }

                            if (!empty($complaint->message)) {
                                $this->deleteImagesFromHTML($complaint->message);
                            }

                            $complaint->delete();
                        }

                        // Delete order items
                        $order->orderItems()->delete();

                        // Delete order itself
                        $order->delete();
                    }

                    $product->delete();
                }
            }

            // Delete client orders (if separate from above)
            $client->orders()->each(function ($order) {
                $order->orderItems()->delete();
                $order->delete();
            });

            // Delete ClientOrder records
            ClientOrder::where('client_id', $client->id)->delete();

            // Delete Customer Complaints
            if ($client->clientComplains) {
                foreach ($client->clientComplains as $complain) {
                    if ($complain->customerComplainConversations) {
                        foreach ($complain->customerComplainConversations as $conversation) {
                            if (!empty($conversation->reply_message)) {
                                $this->deleteImagesFromHTML($conversation->reply_message);
                            }
                            $conversation->delete();
                        }
                    }

                    if (!empty($complain->message)) {
                        $this->deleteImagesFromHTML($complain->message);
                    }

                    $complain->delete();
                }
            }

            // Delete banned customers
            $client->bannedByClients()->each(function ($banRecord) {
                if (!empty($banRecord->message)) {
                    $this->deleteImagesFromHTML($banRecord->message);
                }
                $banRecord->delete();
            });

            // Delete followers
            $client->followers()->delete();

            // Delete coupons created by the client
            $client->coupons()->delete();

            // Delete client profile images
            $profilePaths = [
                'large' => base_path('public/upload/client-profile/large/'),
                'medium' => base_path('public/upload/client-profile/medium/'),
                'small' => base_path('public/upload/client-profile/small/')
            ];
            if (!empty($client->image)) {
                foreach ($profilePaths as $path) {
                    $imagePath = $path . $client->image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }

            // Delete client documents
            $docPaths = [
                'large' => base_path('public/upload/client-document/large/'),
                'medium' => base_path('public/upload/client-document/medium/'),
                'small' => base_path('public/upload/client-document/small/')
            ];
            if (!empty($client->doc_image1)) {
                foreach ($docPaths as $path) {
                    $doc1Path = $path . $client->doc_image1;
                    if (file_exists($doc1Path)) {
                        unlink($doc1Path);
                    }
                }
            }
            if (!empty($client->doc_image2)) {
                foreach ($docPaths as $path) {
                    $doc2Path = $path . $client->doc_image2;
                    if (file_exists($doc2Path)) {
                        unlink($doc2Path);
                    }
                }
            }

            // Finally, delete the client
            $client->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Client and all related data deleted successfully.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Client not found',
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