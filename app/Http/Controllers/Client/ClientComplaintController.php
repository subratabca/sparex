<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\File;
use App\Notifications\ProductComplaint\ProductComplaintConversationNotification;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Complaint;
use App\Models\OrderItem;
use App\Models\ComplaintConversation;
use Exception;


class ClientComplaintController extends Controller
{
    public function complaintPage()
    {
        return view('client.pages.complaint.complaint-list');
    }

    public function getComplaints(Request $request)
    {
        try {
            $client_id = $request->header('id');

            // Fetch complaints for products belonging to the client
            $complaints = Complaint::whereHas('orderItem.product', function ($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->with([
                    'orderItem.order',
                    'orderItem.product',
                    'customer'
                ])
                ->latest()
                ->get();

            ActivityLogger::log('retrieve_complaint_success', 'Complaints retrieved successfully.', $request, 'complaints');

            return response()->json([
                'status' => 'success',
                'data' => $complaints
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_complaint_failed', 'An error occurred while retrieving complaints: ' . $e->getMessage(), $request, 'complaints');

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complaints',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complaintDetailsPage(Request $request)
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

        return view('client.pages.complaint.complaint-details');
    }

    public function getComplaintDetails(Request $request, $complaint_id)
    {
        try {
            $complaint = Complaint::with(['customer', 'conversations', 'orderItem.variant', 'orderItem.product.category', 'orderItem.product.brand', 'orderItem.product.client', 'orderItem.order'])
                ->where('id', $complaint_id)
                ->first();

            if (!$complaint) {
                ActivityLogger::log('access_complaint_details_failed', 'Complain details not found.', $request, 'complaints');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint information not found'
                ], 404);
            }

            // Get the associated order item
            $orderItem = $complaint->orderItem;

            // Prepare response
            $responseData = $complaint->toArray();
            $responseData['order_item'] = $orderItem ? $orderItem->toArray() : null;

            ActivityLogger::log('access_complaint_details_success', 'Complaint details accessed successfully.', $request, 'complaints');

            return response()->json([
                'status' => 'success',
                'data' => $responseData
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('access_complaint_details_failed', 'Error retrieving complaint details: ' . $e->getMessage(), $request, 'complaints');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complaint information',
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
            $img->resize(100, 100)->save(public_path('upload/complain_images/client/' . $imageName));
            $url = asset('upload/complain_images/client/' . $imageName);
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

    public function storeComplaintFeedbackInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'reply_message' => 'required|string|min:10|max:500',
                'complaint_id'  => 'required|integer|exists:complaints,id',
            ]);

            $client_id = $request->header('id');

            if (!$client_id) {
                ActivityLogger::log('complaint_reply_failed', 'Client ID not provided.', $request, 'complaint_conversations');
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Client not authenticated.',
                ], 403);
            }

            $user = User::find($client_id);

            if (!$user || $user->role !== 'client') {
                ActivityLogger::log('complaint_reply_failed', 'Only clients can reply to complaints.', $request, 'complaint_conversations');
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Only clients can reply to complaints.',
                ], 403);
            }

            $complaint = Complaint::with([
                'orderItem.order',
                'orderItem.product',
                'orderItem.variant',
                'orderItem.client',
                'customer',
                'conversations'
            ])->find($validated['complaint_id']);

            if (!$complaint) {
                ActivityLogger::log('complaint_reply_failed', 'Complaint not found or unauthorized.', $request, 'complaint_conversations');
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Complaint not found.',
                ], 404);
            }

            $lastConversation = ComplaintConversation::where('complaint_id', $complaint->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastConversation && $lastConversation->sender_role !== 'customer') {
                ActivityLogger::log('complaint_reply_failed', 'Client cannot reply until customer has responded.', $request, 'complaint_conversations');
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'You cannot reply until the customer has responded.',
                ], 403);
            }

            $complaintConversation = ComplaintConversation::create([
                'complaint_id'  => $complaint->id,
                'sender_id'     => $client_id,
                'reply_message' => $validated['reply_message'],
                'sender_role'   => 'client',
            ]);

            if (!$complaintConversation) {
                ActivityLogger::log('complaint_reply_failed', 'Failed to create complaint feedback.', $request, 'complaint_conversations');
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Failed to create complaint feedback.',
                ], 500);
            }

            ActivityLogger::log('complaint_reply_success', 'Complaint reply sent successfully.', $request, 'complaint_conversations');

            $mailSender = $user;
            $admin = User::where('role', 'admin')->first();
            $customer = $complaint->customer;

            if ($admin) {
                $admin->notify(new ProductComplaintConversationNotification($complaint, $mailSender, 'admin'));
            }

            if ($customer) {
                $customer->notify(new ProductComplaintConversationNotification($complaint, $mailSender, 'customer'));
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Complaint feedback has been sent successfully.',
                'data'    => $complaintConversation,
            ], 201);

        } catch (ValidationException $e) {
            ActivityLogger::log('complaint_reply_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'complaint_conversations');
            return response()->json([
                'status'  => 'failed',
                'message' => 'Validation Failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            ActivityLogger::log('complaint_reply_failed', 'Unexpected error: ' . $e->getMessage(), $request, 'complaint_conversations');
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while processing the request.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}