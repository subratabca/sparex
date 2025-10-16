<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Notifications\Customer\BannedCustomerNotification;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Order;
use App\Models\BannedCustomer;
use Exception;

class ClientBannedController extends Controller
{
    public function uploadEditorImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $manager = new ImageManager(new Driver());
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $img = $manager->read($image);
            $img->resize(100, 100)->save(public_path('upload/banned_customer_images/client/' . $imageName));
            $url = asset('upload/banned_customer_images/client/' . $imageName);
            return response()->json(['image_url' => $url], 200);
        }
    }

    public function deleteEditorImage(Request $request)
    {
        $imageUrl = $request->input('image_url');

        $imagePath = parse_url($imageUrl, PHP_URL_PATH);

        $fullImagePath = public_path($imagePath);

        if (File::exists($fullImagePath)) {
            File::delete($fullImagePath);
            return response()->json(['status' => 'success', 'message' => 'Image deleted successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'Image not found'], 404);
    }

    public function storeBanCustomerInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|min:20',
                'customer_id' => 'required|exists:users,id',
            ]);

            $client_id = $request->header('id');

            $existingCustomer = BannedCustomer::where('client_id', $client_id)
                ->where('customer_id', $validated['customer_id'])
                ->first();

            if ($existingCustomer) {
                ActivityLogger::log('banned_customer_failed', 'You have already banned this customer.', $request, 'banned_customers');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have already banned this customer.',
                ], 403);
            }

            $bannedCustomer = BannedCustomer::create([
                'client_id' => $client_id,
                'customer_id' => $validated['customer_id'],
                'message' => $validated['message'],
            ]);

            if ($bannedCustomer) {
                $bannedCustomer->load([
                    'client' => function($query) {
                        $query->select('id', 'firstName', 'lastName', 'email', 'mobile', 'image');
                    },
                    'customer' => function($query) {
                        $query->select('id', 'firstName', 'lastName', 'email', 'mobile', 'image');
                    }
                ]);

                $customer = User::find($validated['customer_id']);
                if ($customer) {
                    $pendingOrders = Order::where('customer_id', $customer->id)
                        ->whereNotIn('status', ['canceled', 'completed'])
                        ->get();

                    foreach ($pendingOrders as $order) {
                        $order->update([
                            'status' => 'canceled',
                            'cancel_date' => now()->format('Y-m-d'),
                            'cancel_time' => now()->format('H:i:s'),
                        ]);
                    }

                    $customer->notify(new BannedCustomerNotification($bannedCustomer, 'banned'));
                }

                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new BannedCustomerNotification($bannedCustomer, 'banned'));
                }

                ActivityLogger::log('banned_customer_success', 'Customer has been banned successfully.', $request, 'banned_customers');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Customer has been banned successfully, and their pending orders have been canceled.',
                    'data' => $bannedCustomer,
                ], 201);
            } else {
                ActivityLogger::log('banned_customer_failed', 'Failed to ban the customer.', $request, 'banned_customers');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to ban the customer.',
                ], 500);
            }
        } catch (ValidationException $e) {
            ActivityLogger::log('banned_customer_failed', 'Validation failed: ' . implode(', ', $e->errors()), $request, 'banned_customers');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('banned_customer_failed', 'An unexpected error occurred: ' . $e->getMessage(), $request, 'banned_customers');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bannedCustomerPage()
    {
        return view('client.pages.banned-customer.banned-customer-list-page');
    }

    public function getBanCustomerList(Request $request)
    {
        try {
            $client_id = $request->header('id');

            $bannedCustomers = BannedCustomer::with('customer')->where('client_id', $client_id)->latest()->get();

            ActivityLogger::log('retrieve_banned_success', 'Successfully retrieved the banned customer list.', $request, 'banned_customers');
            return response()->json([
                'status' => 'success',
                'data' => $bannedCustomers
            ], 200); 

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_banned_failed', 'An unexpected error occurred: ' . $e->getMessage(), $request, 'banned_customers');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving banned customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bannedCustomerDetailsPage()
    {
        return view('client.pages.banned-customer.banned-customer-details-page');
    }

    public function getBannedCustomerDetails(Request $request,$banned_id)
    {
        try {
            $bannedData = BannedCustomer::with([
                'customer.country',
                'customer.county',
                'customer.city',
                'client'
            ])->where('id', $banned_id)->first();

            if (!$bannedData) {
                ActivityLogger::log('retrieve_banned_failed','Banned customer not found.',$request,'banned_customers');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No banned customer found with this ID',
                ], 404);
            }

            ActivityLogger::log('retrieve_banned_success','Banned customer details retrieved successfully.',$request,'banned_customers');
            return response()->json([
                'status' => 'success',
                'message' => 'Banned customer details retrieved successfully',
                'data' => $bannedData
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_banned_success','Banned customer details retrieved successfully.',$request,'banned_customers');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving banned customer details',
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
            $client_id = $request->header('id');
            $ban_id = $request->input('id');
            $banCustomer = BannedCustomer::where('id',$ban_id)->where('client_id', $client_id)->first();

            if (!$banCustomer) {
                ActivityLogger::log('retrieve_banned_failed','Banned customer not found.',$request,'banned_customers');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No banned customer found with this ID',
                ], 404);
            }

            if (!empty($banCustomer->message)) {
                $this->deleteImagesFromHTML($banCustomer->message); 
            }

            $banCustomer->delete();
            $customer = User::find($banCustomer->customer_id);
            if ($customer) {
                $customer->notify(new BannedCustomerNotification($banCustomer, 'unbanned'));
            }
            
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new BannedCustomerNotification($banCustomer, 'unbanned'));
            }
            ActivityLogger::log('delete_banned_success','Customer removed from the banned list successfully.',$request,'banned_customers');
            return response()->json([
                'status' => 'success',
                'message' => 'Customer remove from banned list successfully.'
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log(
                'delete_banned_failed',
                'An unexpected error occurred: ' . $e->getMessage(),
                $request,
                'banned_customers'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}