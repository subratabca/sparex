<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ProductComplaint\NewProductComplaintNotification;
use App\Notifications\ProductComplaint\ProductComplaintConversationNotification;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Models\Complaint;
use App\Models\ComplaintConversation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class ComplaintController extends Controller
{
    public function productComplaintPage($order_item_id)
    {
        return view('frontend.pages.complaint.product-complaint-page', compact('order_item_id'));
    }

    public function uploadEditorImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $manager = new ImageManager(new Driver());
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $img = $manager->read($image);
            $img->resize(100, 100)->save(public_path('upload/complain_images/customer/' . $imageName));
            $url = asset('upload/complain_images/customer/' . $imageName);
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

    public function storeProductComplaint(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|min:20|max:10000',
                'order_item_id' => 'required|integer|exists:order_items,id',
            ]);

            $customer_id = $request->header('id');

            if (!$customer_id) {
                ActivityLogger::log(
                    'complaint_failed',
                    'Unauthorized, Need to login.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized, Need to login.',
                ], 401);
            }

            $itemID = $validated['order_item_id'];

            // ✅ Ensure no duplicate complaint for same order_item_id
            $existingComplaint = Complaint::where('order_item_id', $itemID)
                ->where('customer_id', $customer_id)
                ->first();

            if ($existingComplaint) {
                ActivityLogger::log(
                    'complaint_failed',
                    'A complaint already exists for this order item.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have already submitted a complaint for this order item.',
                ], 400);
            }

            $messageContent = $validated['message'];

            // ✅ Create new complaint
            $complaint = Complaint::create([
                'order_item_id' => $itemID,
                'customer_id' => $customer_id,
                'message' => $messageContent,
                'cmp_date' => now()->format('Y-m-d'),
                'cmp_time' => now()->format('H:i:s'),
            ]);

            if ($complaint) {
                // ✅ Preload all relations needed for the email
                $complaint->load([
                    'orderItem.order',
                    'orderItem.product.client',
                    'orderItem.product.category',
                    'orderItem.product.brand',
                    'orderItem.variant',
                    'customer'
                ]);

                // ✅ Notify admin
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    $admin->notify(new NewProductComplaintNotification($complaint));
                }

                ActivityLogger::log(
                    'complaint_success',
                    'A new complaint has been created successfully.',
                    $request,
                    'complaints'
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Complaint has been sent successfully.',
                    'data' => $complaint,
                ], 201);
            } else {
                ActivityLogger::log(
                    'complaint_failed',
                    'Failed to create a new complaint.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to create complaint.',
                ], 500);
            }

        } catch (ValidationException $e) {
            ActivityLogger::log(
                'complaint_failed',
                'Validation errors occurred: ' . json_encode($e->errors()),
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_failed',
                'An unexpected error occurred: ' . $e->getMessage(),
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function complaintPage()
    {
        return view('frontend.pages.complaint.complaint-list-page');
    }

    public function getComplaints(Request $request)
    {
        try {
            $customer_id = $request->header('id');
            
            $complaints = Complaint::with([
                'orderItem.product.client',
                'orderItem.order',
                'conversations'
            ])
            ->where('customer_id', $customer_id)
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

        return view('frontend.pages.complaint.complaint-details-page');
    }

    public function getComplaintDetails(Request $request, $complaint_id)
    {
        try {
            // Fetch complaint with related models based on actual relationships
            $complaint = Complaint::with([
                'orderItem.product.category',
                'orderItem.product.brand',
                'orderItem.product.client',
                'orderItem.order',
                'orderItem.variant',
                'customer',
                'conversations.sender'
            ])->find($complaint_id);

            if (!$complaint) {
                ActivityLogger::log('access_complaint_details_failed', 'Complaint details not found.', $request, 'complaints');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint information not found'
                ], 404);
            }

            // Read user’s notification if exists
            $customer_id = $request->header('id');
            if ($customer_id) {
                $customer = User::find($customer_id);
                if ($customer) {
                    $notification = $customer->notifications()
                        ->where('data->complaint_id', $complaint_id)
                        ->whereNull('read_at')
                        ->first();

                    if ($notification) {
                        $notification->markAsRead();
                    }
                }
            }

            // Prepare structured response data
            $responseData = [
                'id' => $complaint->id,
                'status' => $complaint->status,
                'message' => $complaint->message,
                'cmp_date' => $complaint->cmp_date,
                'cmp_time' => $complaint->cmp_time,
                'customer' => $complaint->customer,
                'order_item' => [
                    'id' => $complaint->orderItem->id,
                    'quantity' => $complaint->orderItem->quantity,
                    'unit_price' => $complaint->orderItem->unit_price,
                    'total_price' => $complaint->orderItem->total_price,
                    'status' => $complaint->orderItem->status,
                    'variant' => $complaint->orderItem->variant,
                    'order' => $complaint->orderItem->order,
                    'product' => $complaint->orderItem->product,
                ],
                'conversations' => $complaint->conversations->map(function ($conversation) {
                    return [
                        'id' => $conversation->id,
                        'reply_message' => $conversation->reply_message,
                        'sender_role' => $conversation->sender_role,
                        'created_at' => $conversation->created_at->toISOString(),
                    ];
                }),
            ];

            ActivityLogger::log('access_complaint_details_success', 'Complaint details accessed successfully.', $request, 'complaints');
            return response()->json([
                'status' => 'success',
                'data' => $responseData
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('access_complaint_details_failed', 'An error occurred while retrieving complaint details: ' . $e->getMessage(), $request, 'complaints');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complaint information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complaintReplyPage($complain_id)
    {
        return view('frontend.pages.complaint.complaint-reply-page');
    }

    public function storeComplaintReply(Request $request)
    {
        try {
            $validated = $request->validate([
                'reply_message' => 'required|string|min:20|max:500',
                'complaint_id'  => 'required|integer|exists:complaints,id',
            ]);

            $customer_id = $request->header('id');
            $complaint_id = $validated['complaint_id'];

            $complaint = Complaint::where('id', $complaint_id)
            ->where('customer_id', $customer_id)
            ->first();

            if (!$complaint) {
                ActivityLogger::log('complaint_reply_failed', 'Complaint not found or unauthorized.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint not found or unauthorized.',
                ], 404);
            }

            $lastConversation = ComplaintConversation::where('complaint_id', $complaint_id)
            ->orderBy('created_at', 'desc')
            ->first();

            if (!$lastConversation || $lastConversation->sender_role !== 'client') {
                ActivityLogger::log('complaint_reply_failed', 'Cannot reply until the client has responded.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You cannot reply until the client has responded.',
                ], 403);
            }

            $customerReplyCount = ComplaintConversation::where('complaint_id', $complaint_id)
            ->where('sender_id', $customer_id)
            ->where('sender_role', 'customer')
            ->count();

            if ($customerReplyCount >= 3) {
                ActivityLogger::log('complaint_reply_failed', 'Maximum number of replies reached for this complaint.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have reached the maximum number of replies for this complaint.',
                ], 403);
            }

            $complaintConversation = ComplaintConversation::create([
                'complaint_id' => $complaint_id,
                'sender_id' => $customer_id,
                'reply_message' => $validated['reply_message'],
                'sender_role' => 'customer',
            ]);

            if (!$complaintConversation) {
                ActivityLogger::log('complaint_reply_failed', 'Failed to create complaint feedback.', $request, 'complaint_conversations');
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Failed to create complaint feedback.',
                ], 500);
            }

            ActivityLogger::log('complaint_reply_success', 'Complaint reply sent successfully.', $request, 'complaint_conversations');

            $mailSender = User::findOrFail($customer_id);
            $admin = User::where('role', 'admin')->first();
            $client = optional(optional($complaint->orderItem)->product)->client;

            if ($admin) {
                $admin->notify(new ProductComplaintConversationNotification($complaint, $mailSender, 'admin'));
            }

            if ($client) {
                $client->notify(new ProductComplaintConversationNotification($complaint, $mailSender, 'customer'));
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Complaint feedback has been sent successfully.',
                'data'    => $complaintConversation,
            ], 201);

        } catch (ValidationException $e) {
            ActivityLogger::log('complaint_reply_failed', 'Validation errors: ' . json_encode($e->errors()), $request, 'complaint_conversations');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            ActivityLogger::log('complaint_reply_failed', 'Unexpected error: ' . $e->getMessage(), $request, 'complaint_conversations');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}