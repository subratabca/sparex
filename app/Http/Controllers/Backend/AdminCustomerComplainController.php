<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Notifications\ReviewComplainAgainstCustomer;
use App\Notifications\SolvedCustomerComplain;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\CustomerComplaint;
use App\Models\CustomerComplainConversion;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class AdminCustomerComplainController extends Controller
{
    public function CustomerComplainPage()
    {
        return view('backend.pages.customer-complain.customer-complain-list');
    }


    public function CustomerComplainList(Request $request)
    {
        try {
            $complain = CustomerComplaint::with('client','customer')->get(); 
            ActivityLogger::log(
                'retrieve_complaint_success',
                'Complain list successfully retrieved.',
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'success',
                'data' => $complain
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log(
                'retrieve_complaint_failed',
                'An error occurred while retrieving complaints: ' . $e->getMessage(),
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function CustomerComplainDetailsPage()
    {
        return view('backend.pages.customer.customer-complain-details');
    }


    public function CustomerComplainDetailsInfo($complain_id)
    {
        try {
            $complain = CustomerComplain::with('client','customer','customerComplainConversations')->where('id',$complain_id)->first(); 

            ActivityLogger::log(
                'access_complaint_details_success',
                'Complain details accessed successfully.',
                $request,
                'customer_complains'
            );

            return response()->json([
                'status' => 'success',
                'data' => $complain
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log(
                'access_complaint_details_failed',
                'An error occurred while accessing complaint details: ' . $e->getMessage(),
                $request,
                'customer_complains'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ComplainSendToCustomer($complain_id)
    {
        try {
            $complain = CustomerComplain::with('client', 'customer')->findOrFail($complain_id);

            if ($complain->status === 'pending') {
                $currentDateTime = Carbon::now();
                $customer_cmp_date = $currentDateTime->format('d F Y');
                $customer_cmp_time = $currentDateTime->format('h:i:s A');

                $result = $complain->update([
                    'status' => 'under-review',
                    'customer_cmp_date' => $customer_cmp_date,
                    'customer_cmp_time' => $customer_cmp_time,
                ]);

                $customer = $complain->customer; 
                $client = $complain->client;   

                if ($customer) {
                    $pendingOrders = Order::where('user_id', $customer->id)
                    ->whereNotIn('status', ['canceled', 'completed'])
                    ->get();

                    foreach ($pendingOrders as $order) {
                        $order->update([
                            'status' => 'cancel',
                            'cancel_date' => $currentDateTime->format('Y-m-d'),
                            'cancel_time' => $currentDateTime->format('H:i:s'),
                        ]);

                        $food = $order->food;
                        if ($food) {
                            $food->update([
                                'status' => 'published',
                            ]);
                        }
                    }
                }

                if ($customer && $customer->role === 'user') {
                    $customer->notify(new ReviewComplainAgainstCustomer($complain));
                }

                if ($client && $client->role === 'client') {
                    $client->notify(new ReviewComplainAgainstCustomer($complain));
                }

                ActivityLogger::log(
                    'complaint_status_success',
                    'Complain sent successfully to customer.',
                    $request,
                    'customer_complains'
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Complain sent successfully to customer.',
                    'data' => $complain
                ], 200);
            } else {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Complain is not in pending status.',
                    $request,
                    'customer_complains'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complain is not in pending status.'
                ], 400);
            }
        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'Complain not found: ' . $e->getMessage(),
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Complain not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'An error occurred while sending the complain: ' . $e->getMessage(),
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Status update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function uploadEditorImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $manager = new ImageManager(new Driver());
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $img = $manager->read($image);
            $img->resize(100, 100)->save(public_path('upload/customer-complain_images/admin/' . $imageName));
            $url = asset('/upload/customer-complain_images/admin/' . $imageName);
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


    public function CustomerComplainSolved(Request $request)
    {
        try {

            $validated = $request->validate([
                'reply_message' => 'required|string|min:20|max:500',
                'complain_id' => 'required|integer|exists:customer_complains,id',
            ]);

            $user_id = $request->header('id');
            $sender_role = User::where('id', $user_id)->value('role');

            if ($sender_role !== 'admin') {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Only admin can reply to complaints.',
                    $request,
                    'customer_complains'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Only admin can reply to complaints.',
                ], 403);
            }

            $customerComplain = CustomerComplain::find($validated['complain_id']);
            if (!$customerComplain) {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Complaint not found.',
                    $request,
                    'customer_complains'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint not found.',
                ], 404);
            }


            $lastConversation = $customerComplain->customerComplainConversations()
            ->orderBy('created_at', 'desc')
            ->first();

            if ($lastConversation && $lastConversation->sender_role !== 'customer') {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Cannot reply until the customer has responded.',
                    $request,
                    'customer_complains'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You cannot reply until the customer has responded.',
                ], 403);
            }

            $complainConversation = CustomerComplainConversion::create([
                'customer_complain_id' => $customerComplain->id,
                'sender_id' => $user_id,
                'reply_message' => $validated['reply_message'],
                'sender_role' => $sender_role,
            ]);

            if ($complainConversation) {
                $customerComplain->update(['status' => 'solved']);

                $customer = $customerComplain->customer;
                $client = $customerComplain->client;

                if ($customer && $customer->role === 'user') {
                    $customer->notify(new SolvedCustomerComplain($customerComplain));
                }

                if ($client && $client->role === 'client') {
                    $client->notify(new SolvedCustomerComplain($customerComplain));
                }

                ActivityLogger::log(
                    'complaint_status_success',
                    'Complain solved successfully.',
                    $request,
                    'customer_complain_conversions'
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Complaint solved successfully.',
                ], 201);
            }else{
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Failed to create complain feedback.',
                    $request,
                    'customer_complain_conversions'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to create complain feedback.',
                ], 500);
            }

        } catch (ValidationException $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'Validation error: ' . json_encode($e->errors()),
                $request,
                'customer_complain_conversions'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'customer_complain_conversions'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request.',
                'error' => $e->getMessage(),
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
            $complain_id = $request->input('id');
            $complain = CustomerComplain::findOrFail($complain_id);

            if ($complain->customerComplainConversations->isNotEmpty()) { 
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
            ActivityLogger::log(
                'complaint_delete_success',
                'Customer complain deleted successfully.',
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Complain deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'complaint_delete_failed',
                'Complain not found. Error: ' . $e->getMessage(),
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Complain not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_delete_failed',
                'An unexpected error occurred while deleting customer complain: ' . $e->getMessage(),
                $request,
                'customer_complains'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}