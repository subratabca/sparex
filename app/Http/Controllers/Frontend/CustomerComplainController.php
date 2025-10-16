<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Notifications\CustomerComplainFeedbackNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Helpers\ActivityLogger;
use Exception;
use App\Models\ActivityLog;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CustomerComplaint;
use App\Models\CustomerComplainConversion;

class CustomerComplainController extends Controller
{

    public function CustomerComplainPage(){
        return view('frontend.pages.customer-complain.customer-complain-page');
    }


    public function CustomerComplainList(Request $request)
    {
        try {
            $customer_id = $request->header('id');
            $complains = CustomerComplaint::with(['customer', 'client', 'customerComplainConversations'])->where('customer_id', $customer_id)->where('status','under-review')->latest()->get();

            ActivityLogger::log('retrieve_complaint_success', 'Complaint retrieved successfully.', $request, 'customer_complains');
            return response()->json([
                'status' => 'success',
                'data' => $complains
            ], 200); 

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_complaint_failed', 'Error while retrieving customer complaint list: ' . $e->getMessage(), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function CustomerComplainDetailsPage(Request $request)
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

        return view('frontend.pages.customer-complain.customer-complain-details-page');
    }


    public function CustomerComplainDetailsInfo(Request $request, $complain_id)
    {
        try {
            $user_id = $request->header('id');
            $user = User::findOrFail($user_id);

            $complain = CustomerComplain::with(['customer', 'client', 'customerComplainConversations'])
            ->where('id', $complain_id)
            ->first();

            if (!$complain) {
                ActivityLogger::log('access_complaint_details_failed', 'Customer complaint not found.', $request, 'customer_complains');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complain information not found'
                ], 404);
            }

            if ($user) {
                $notification = $user->notifications()
                ->where('notifiable_id', $user_id)
                ->where('data->complain_id', $complain_id)  
                ->first();

                if ($notification && is_null($notification->read_at)) {
                    $notification->markAsRead();
                }
            }

            ActivityLogger::log('access_complaint_details_success', 'Customer complain details accessed successfully', $request, 'customer_complains');
            return response()->json([
                'status' => 'success',
                'data' => $complain
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('access_complaint_details_failed', 'Error occurred while accessing complain details: ' . $e->getMessage(), $request, 'customer_complains');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complain information',
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
            $img->resize(100, 100)->save(public_path('upload/customer-complain_images/customer/' . $imageName));
            $url = asset('upload/customer-complain_images/customer/' . $imageName);
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

    public function CustomerComplainAppealPage($complain_id)
    {
        return view('frontend.pages.customer-complain.customer-complain-appeal-page');
    }


    public function StoreCustomerComplainAppealInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'reply_message' => 'required|string|min:20',
                'complain_id' => 'required|exists:customer_complains,id',
            ]);

            $customer_id = $request->header('id');
            $complain_id = $validated['complain_id'];

            $lastConversation = CustomerComplainConversion::where('customer_complain_id', $complain_id)
            ->orderBy('created_at', 'desc')
            ->first();

            if ($lastConversation) {
                if ($lastConversation->sender_role !== 'admin') {
                    ActivityLogger::log('appeal_failed', 'You cannot reply until the admin has responded.', $request, 'customer_complain_conversions');
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'You cannot reply until the admin has responded.',
                    ], 403);
                }
            }

            $customerReplyCount = CustomerComplainConversion::where('customer_complain_id', $complain_id)
            ->where('sender_id', $customer_id)
            ->where('sender_role', 'customer')
            ->count();

            if ($customerReplyCount >= 1) {
                ActivityLogger::log('appeal_failed', 'You have reached the maximum number of replies for this complaint.', $request, 'customer_complain_conversions');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have reached the maximum number of replies for this complaint.',
                ], 403);
            }

            $complainConversation = CustomerComplainConversion::create([
                'customer_complain_id' => $complain_id,
                'sender_id' => $customer_id,
                'reply_message' => $validated['reply_message'],
                'sender_role' => 'customer'
            ]);

            if ($complainConversation) {
                ActivityLogger::log('appeal_success', 'Customer submitted a complaint appeal.', $request, 'customer_complain_conversions');
                $admin = User::where('role', 'admin')->first();

                if ($admin) {
                    $admin->notify(new CustomerComplainFeedbackNotification($complainConversation));
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Complain appeal placed successfully.'
                    ], 201);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Admin or Client not found.',
                    ], 404);
                }
            } else {
                ActivityLogger::log('appeal_failed', 'Failed to create complaint appeal', $request, 'customer_complain_conversions');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to create complain appeal.',
                ], 500);
            }

        } catch (ValidationException $e) {
            ActivityLogger::log('appeal_failed', 'Validation Failed', $request, 'customer_complain_conversions');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            ActivityLogger::log('appeal_failed', 'An error occurred while processing the request: ' . $e->getMessage(), $request, 'customer_complain_conversions');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}