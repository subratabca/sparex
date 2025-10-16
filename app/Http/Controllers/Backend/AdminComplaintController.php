<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Notifications\ProductComplaint\ForwardProductComplaintNotification;
use App\Notifications\ProductComplaint\SolvedProductComplaintNotification;
use App\Notifications\ProductComplaint\ComplaintInvestigationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Complaint;
use App\Models\ComplaintConversation;
use Exception;

class AdminComplaintController extends Controller
{
    public function complaintPage()
    {
        return view('backend.pages.complaint.complaint-list');
    }

    public function getComplaints(Request $request)
    {
        try {
            $complaints = Complaint::with([
                'orderItem.order:id,order_number',
                'orderItem.product:id,name,image',
                'orderItem.variant:id,color,size',
                'customer:id,firstName,lastName'
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

        return view('backend.pages.complaint.complaint-details');
    }

    public function getComplaintDetails(Request $request, $complaint_id)
    {
        try {
            $complaint = Complaint::with([
                'orderItem.order:id,order_number',
                'orderItem.product:id,name,image,category_id,brand_id,client_id',
                'orderItem.product.category:id,name',
                'orderItem.product.brand:id,name',
                'orderItem.product.client:id,firstName,lastName',
                'orderItem.variant:id,color,size',
                'customer:id,firstName,lastName',
                'conversations'
            ])
                ->where('id', $complaint_id)
                ->first();

            if (!$complaint) {
                ActivityLogger::log(
                    'access_complaint_details_failed',
                    'Complaint details not found.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint information not found'
                ], 404);
            }

            ActivityLogger::log(
                'access_complaint_details_success',
                'Complaint details accessed successfully.',
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'success',
                'data' => $complaint
            ], 200);
        } catch (Exception $e) {
            ActivityLogger::log(
                'access_complaint_details_failed',
                'An error occurred while retrieving complaint details: ' . $e->getMessage(),
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving complaint information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complaintForwardedToClient(Request $request, $complaint_id)
    {
        try {
            // ✅ Validate the complaint ID
            $request->merge(['complaint_id' => $complaint_id]);
            $request->validate([
                'complaint_id' => 'required|integer|exists:complaints,id'
            ]);

            // ✅ Load complaint with all necessary relationships
            $complaint = Complaint::with([
                'orderItem.order',
                'orderItem.product',
                'orderItem.product.client',
                'orderItem.product.brand',
                'orderItem.product.category',
                'orderItem.variant',
                'customer',
                'conversations'
            ])->findOrFail($complaint_id);

            // ✅ Ensure the complaint is still pending
            if ($complaint->status !== 'pending') {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Complaint is not in pending status.',
                    $request,
                    'complaints'
                );

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint is not in pending status.'
                ], 400);
            }

            // ✅ Update complaint status to "under_review"
            $updated = $complaint->update([
                'status' => 'under_review',
                'clnt_cmp_date' => now()->format('Y-m-d'),
                'clnt_cmp_time' => now()->format('H:i:s'),
            ]);

            if (!$updated) {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Failed to forward complaint to client.',
                    $request,
                    'complaints'
                );

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to forward complaint to client.'
                ], 500);
            }

            // ✅ Notify related users (customer & client)
            if ($complaint->customer && $complaint->customer->role === 'customer') {
                $complaint->customer->notify(
                    new ForwardProductComplaintNotification($complaint, 'customer')
                );
            }

            $client = $complaint->orderItem->product->client ?? null;
            if ($client && $client->role === 'client') {
                $client->notify(
                    new ForwardProductComplaintNotification($complaint, 'client')
                );
            }

            // ✅ Log success
            ActivityLogger::log(
                'complaint_status_success',
                'Complaint forwarded successfully to client.',
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Complaint forwarded successfully to client.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'Complaint not found: ' . $e->getMessage(),
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'Complaint not found',
                'error' => $e->getMessage()
            ], 404);

        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'An error occurred while forwarding complaint: ' . $e->getMessage(),
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'Status update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function complaintForwardedToClient2222(Request $request, $complaint_id)
    {
        try {
            // ✅ Validate incoming request
            $request->merge(['complaint_id' => $complaint_id]);
            $request->validate([
                'complaint_id' => 'required|integer|exists:complaints,id'
            ]);

            // ✅ Load all relevant relations efficiently
            $complaint = Complaint::with([
                'orderItem.order',         // load related order via orderItem
                'orderItem.product',       // product related to that order item
                'orderItem.product.client',// client who owns the product
                'orderItem.product.brand',
                'orderItem.product.category',
                'productVariant',          // variant if exists
                'customer',                // the customer who made complaint
                'conversations'            // complaint conversation history
            ])->findOrFail($complaint_id);

            // ✅ Ensure the complaint is still pending
            if ($complaint->status !== 'pending') {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Complaint is not in pending status.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint is not in pending status.'
                ], 400);
            }

            // ✅ Update complaint status to 'under_review'
            $updated = $complaint->update([
                'status' => 'under_review',
                'clnt_cmp_date' => now()->format('Y-m-d'),
                'clnt_cmp_time' => now()->format('H:i:s'),
            ]);

            if (!$updated) {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Failed to forward complaint to client.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to forward complaint to client.'
                ], 500);
            }

            // ✅ Notify the related users
            if ($complaint->customer && $complaint->customer->role === 'customer') {
                $complaint->customer->notify(new ForwardProductComplaintNotification($complaint, 'customer'));
            }

            $client = $complaint->orderItem->product->client ?? null;
            if ($client && $client->role === 'client') {
                $client->notify(new ForwardProductComplaintNotification($complaint, 'client'));
            }

            // ✅ Log successful forwarding
            ActivityLogger::log(
                'complaint_status_success',
                'Complaint forwarded successfully to client.',
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Complaint forwarded successfully to client.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'Complaint not found: ' . $e->getMessage(),
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Complaint not found',
                'error' => $e->getMessage()
            ], 404);

        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'An error occurred while forwarding complaint: ' . $e->getMessage(),
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Status update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complaintForwardedToClient11111(Request $request, $complaint_id)
    {
        try {
            $request->merge(['complaint_id' => $complaint_id]);
            $request->validate([
                'complaint_id' => 'required|integer|exists:complaints,id'
            ]);

            $complaint = Complaint::with([
                'order.orderItems' => function($query) use ($complaint_id) {
                    $complaint = Complaint::find($complaint_id);
                    $query->where('product_id', $complaint->product_id);
                    if ($complaint->product_variant_id) {
                        $query->where('product_variant_id', $complaint->product_variant_id);
                    }
                },
                'product.client', 
                'product.category',
                'product.brand',
                'customer',
                'variant',
                'conversations'
            ])->findOrFail($complaint_id);

            if ($complaint->status !== 'pending') {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Complaint is not in pending status.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint is not in pending status.'
                ], 400);
            }

            $updated = $complaint->update([
                'status' => 'under_review',
                'clnt_cmp_date' => now()->format('Y-m-d'),
                'clnt_cmp_time' => now()->format('H:i:s'),
            ]);

            if (!$updated) {
                ActivityLogger::log(
                    'complaint_status_failed',
                    'Failed to forward complaint to client.',
                    $request,
                    'complaints'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to forward complaint to client.',
                ], 500);
            }

            if ($complaint->customer->role === 'customer') {
                $complaint->customer->notify(new ForwardProductComplaintNotification($complaint, 'customer'));
            }

            if ($complaint->product->client->role === 'client') {
                $complaint->product->client->notify(new ForwardProductComplaintNotification($complaint, 'client'));
            }

            ActivityLogger::log(
                'complaint_status_success',
                'Complaint forwarded successfully to client.',
                $request,
                'complaints'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Complaint forwarded successfully to client.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'Complain not found: ' . $e->getMessage(),
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Complaint not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_status_failed',
                'An error occurred while sending complaint to client: ' . $e->getMessage(),
                $request,
                'complaints'
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
            $img->resize(100, 100)->save(public_path('upload/complain_images/admin/' . $imageName));
            $url = asset('/upload/complain_images/admin/' . $imageName);
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

    public function complaintSolved(Request $request)
    {
        try {
            $validated = $request->validate([
                'reply_message' => 'required|string|min:20|max:500',
                'complaint_id' => 'required|exists:complaints,id',
            ]);

            $admin_id = $request->header('id');
            $sender_role = User::where('id', $admin_id)->value('role');

            if ($sender_role !== 'admin') {
                ActivityLogger::log('complaint_status_failed', 'Only admins can reply to complaints.', $request, 'complaints');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Only admins can reply to complaints.',
                ], 403);
            }

            $complaint = Complaint::find($validated['complaint_id']);
            if (!$complaint) {
                ActivityLogger::log('complaint_status_failed', 'Complaint not found.', $request, 'complaints');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint not found.',
                ], 404);
            }


            $complaintConversation = ComplaintConversation::create([
                'complaint_id' => $complaint->id,
                'sender_id' => $admin_id,
                'reply_message' => $validated['reply_message'],
                'sender_role' => $sender_role,
            ]);

            if ($complaintConversation && $complaint->status === 'under_review') {
                $result = $complaint->update([
                    'status' => 'solved',
                ]);

                $customer = $complaint->customer;             
                $client = $complaint->product->client;        

                if ($customer->role === 'customer') {
                    $customer->notify(new SolvedProductComplaintNotification($complaint,'customer'));
                }

                if ($client->role === 'client') {
                    $client->notify(new SolvedProductComplaintNotification($complaint,'client'));
                }

                ActivityLogger::log('complaint_status_success', 'Complaint solved successfully.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Complaint solved successfully.',
                ], 201);

            } else {
                ActivityLogger::log('complaint_status_failed', 'Failed to solve the complaint.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to solved complaint.',
                ], 500);
            }

        } catch (ValidationException $e) {
            ActivityLogger::log('complaint_status_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'complaint_conversations');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('complaint_status_failed', 'Error: ' . $e->getMessage(), $request, 'complaint_conversations');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function complaintInvestigation(Request $request)
    {
        try {
            $validated = $request->validate([
                'reply_message' => 'required|string|min:20|max:500',
                'complaint_id' => 'required|exists:complaints,id',
            ]);

            $admin_id = $request->header('id');
            $sender_role = User::where('id', $admin_id)->value('role');

            if ($sender_role !== 'admin') {
                ActivityLogger::log('complaint_status_failed', 'Only admins can reply to complaints.', $request, 'complaints');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Only admins can reply to complaints.',
                ], 403);
            }

            $complaint = Complaint::find($validated['complaint_id']);
            if (!$complaint) {
                ActivityLogger::log('complaint_status_failed', 'Complaint not found.', $request, 'complaints');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Complaint not found.',
                ], 404);
            }


            $complaintConversation = ComplaintConversation::create([
                'complaint_id' => $complaint->id,
                'sender_id' => $admin_id,
                'reply_message' => $validated['reply_message'],
                'sender_role' => $sender_role,
            ]);

            if ($complaintConversation && $complaint->status === 'under_review') {
                $result = $complaint->update([
                    'status' => 'further_investigation',
                ]);

                $customer = $complaint->customer;             
                $client = $complaint->product->client;        

                if ($customer->role === 'customer') {
                    $customer->notify(new ComplaintInvestigationNotification($complaint,'customer')); 
                }

                if ($client->role === 'client') {
                    $client->notify(new ComplaintInvestigationNotification($complaint,'client')); 
                }

                ActivityLogger::log('complaint_status_success', 'Complaint is now under further investigation.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Complaint is now under further investigation.',
                ], 201);

            } else {
                ActivityLogger::log('complaint_status_failed', 'Failed to investigate the complaint.', $request, 'complaint_conversations');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to create complaint investigation.',
                ], 500);
            }

        } catch (ValidationException $e) {
            ActivityLogger::log('complaint_status_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'complaint_conversations');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('complaint_status_failed', 'Error: ' . $e->getMessage(), $request, 'complaint_conversations');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
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
            $admin_id = $request->header('id');
            $complaint_id = $request->input('id');

            $complaint = Complaint::with('conversations')->findOrFail($complaint_id);

            if (!empty($complaint->message)) {
                $this->deleteImagesFromHTML($complaint->message);
            }

            foreach ($complaint->conversations as $conversation) {
                if (!empty($conversation->reply_message)) {
                    $this->deleteImagesFromHTML($conversation->reply_message);
                }
            }

            $complaint->conversations()->delete();
            $complaint->delete();
            ActivityLogger::log(
                'complaint_delete_success',
                'Complaint deleted successfully.',
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Complaint, conversations, and related images deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'complaint_delete_failed',
                'Complain not found.',
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Complaint not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log(
                'complaint_delete_failed',
                'An unexpected error occurred while deleting the complaint: ' . $e->getMessage(),
                $request,
                'complaints'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}