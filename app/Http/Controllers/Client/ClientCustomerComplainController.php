<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Notifications\ComplainAgainstCustomerNotification;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\CustomerComplaint;
use Carbon\Carbon;

class ClientCustomerComplainController extends Controller
{
    public function uploadEditorImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $manager = new ImageManager(new Driver());
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $img = $manager->read($image);
            $img->resize(100, 100)->save(public_path('upload/customer-complain_images/client/' . $imageName));
            $url = asset('upload/customer-complain_images/client/' . $imageName);
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


    public function StoreCustomerComplain(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|min:20|max:500',
                'customer_id' => 'required|exists:users,id',
            ]);

            $user_id = $request->header('id');
            $sender_role = User::where('id', $user_id)->value('role');

            if ($sender_role !== 'client') {
                ActivityLogger::log('complaint_failed', 'Only clients can create complaints.', $request, 'customer_complains');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Only clients can create complaints.',
                ], 403);
            }

            $existingComplaint = CustomerComplain::where('client_id', $user_id)
            ->where('customer_id', $validated['customer_id'])
            ->where('status', 'pending')
            ->first();

            if ($existingComplaint) {
                ActivityLogger::log('complaint_failed', 'An unresolved complaint already exists for this customer.', $request, 'customer_complains');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'An unresolved complaint already exists. You cannot complain again until it is resolved.',
                ], 403);
            }

            $currentDateTime = Carbon::now();
            $cmp_date = $currentDateTime->format('d F Y');
            $cmp_time = $currentDateTime->format('h:i:s A');

            $complain = CustomerComplain::create([
                'client_id' => $user_id,
                'customer_id' => $validated['customer_id'],
                'sender_role' => 'client',
                'status' => 'pending',
                'message' => $validated['message'],
                'cmp_date' => $cmp_date,
                'cmp_time' => $cmp_time,
            ]);


            if ($complain) {
                ActivityLogger::log('complaint_success', 'A new complaint against customer has been created.', $request, 'customer_complains');
                $admin = User::where('role', 'admin')->first();

                if ($admin) {
                    $admin->notify(new ComplainAgainstCustomerNotification($complain));
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Complaint against customer has been created successfully.',
                        'data' => $complain,
                    ], 201);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Admin not found.',
                    ], 404);
                }
            } else {
                ActivityLogger::log('complaint_failed', 'Failed to create the complaint.', $request, 'customer_complains');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to create complaint.',
                ], 500);
            }
        } catch (ValidationException $e) {
            ActivityLogger::log('complaint_failed', 'Validation errors occurred: ' . json_encode($e->errors()), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('complaint_failed', 'An unexpected error occurred: ' . $e->getMessage(), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function CustomerComplainPage()
    {
        return view('client.pages.customer-complain.customer-complain-list');
    }


    public function CustomerComplainList(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $customerComplain = CustomerComplaint::with('client','customer')->where('client_id', $client_id)->get();  
            ActivityLogger::log('retrieve_complaint_success', 'Complaints retrieved successfully .', $request, 'customer_complains');
            return response()->json([
                'status' => 'success',
                'data' => $customerComplain
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_complaint_failed', 'An error occurred while retrieving complaints: ' . $e->getMessage(), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving customer complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function CustomerComplainDetailsPage()
    {
        return view('client.pages.customer-complain.customer-complain-details');
    }


    public function CustomerComplainDetailsInfo($complain_id)
    {
        try {
            $customerComplain = CustomerComplain::with('client','customer','customerComplainConversations')->where('id',$complain_id)->first(); 
            ActivityLogger::log('access_complaint_details_success', 'Complain details accessed successfully.', $request, 'customer_complains');
            return response()->json([
                'status' => 'success',
                'data' => $customerComplain
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('access_complaint_details_failed', 'An error occurred while retrieving complain details: ' . $e->getMessage(), $request, 'customer_complains');
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
            ActivityLogger::log('complaint_delete_success', 'Complain deleted successfully.', $request, 'customer_complains');

            return response()->json([
                'status' => 'success',
                'message' => 'Complain deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log('complaint_delete_failed', 'Validation failed. Complain not found. Error: ' . $e->getMessage(), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'Complain not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log('complaint_delete_failed', 'An unexpected error occurred: ' . $e->getMessage(), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}