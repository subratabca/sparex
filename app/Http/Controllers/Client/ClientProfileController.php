<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use App\Helpers\LocationHelper;
use Illuminate\Validation\ValidationException;
use App\Notifications\Client\ClientDocumentNotification;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Complaint;
use Exception;
use DB;


class ClientProfileController extends Controller
{
    public function ProfilePage()
    { 
        return view('client.pages.profile.profile-page');
    }

    public function Profile(Request $request)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                ActivityLogger::log('profile_accessed_failed', 'Unauthorized request: Missing email in request', $request, 'users');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email header is missing'
                ], 400);
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $unreadNotifications = $user->unreadNotifications;
                $readNotifications = $user->readNotifications;

                ActivityLogger::log('profile_accessed_success', 'Profile accessed successfully for email: ' . $email, $request, 'users');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request Successful',
                    'data' => $user,
                    'unreadNotifications' => $unreadNotifications,
                    'readNotifications' => $readNotifications,
                ], 200);
            } else {
                ActivityLogger::log('profile_accessed_failed', 'User not found with email: ' . $email, $request, 'users');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client not found'
                ], 404);
            }


        } catch (Exception $e) {
            ActivityLogger::log('profile_accessed_failed', 'Error occurred while accessing profile: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $id = $request->header('id');
            $request->validate(ValidationHelper::profileValidationRules());
            $user = User::findOrFail($id);

            $imagePath = $request->hasFile('image') 
            ? ImageHelper::processAndSaveProfileImage(
                $request->file('image'),
                config('image.profile')[$user->role],
                $user->image
            )
            : $user->image;

            $profileData = ItemHelper::prepareProfileData($request, $imagePath);
            ItemHelper::storeOrUpdateProfile($profileData, $user);

            ActivityLogger::log('profile_update_success', 'Profile updated successfully for user ID: ' . $id, $request, 'users');
            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
            ], 200);
        } catch (ValidationException $e) {
            ActivityLogger::log('profile_update_failed', 'Validation errors occurred: ' . json_encode($e->errors()), $request, 'users');
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation errors.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('profile_update_failed', 'An error occurred while updating profile: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'fail',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function PasswordPage()
    {
        return view('client.pages.profile.password-change-page');
    }

    public function UpdatePassword(Request $request)
    {
        try {
            $request->validate([
                'oldpassword' => 'required|string|min:6',
                'newpassword' => 'required|string|min:6|confirmed', 
            ]);

            $email = $request->header('email');
            $user = User::where('email', $email)->first();

            if (!$user) {
                ActivityLogger::log(
                    'password_update_failed',
                    'User not found with email: ' . $email,
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User not found'
                ], 404);
            }

            $oldPassword = $request->input('oldpassword');
            $hashedPassword = $user->password;

            if (Hash::check($oldPassword, $hashedPassword)) {
                $newPassword = Hash::make($request->input('newpassword'));
                $user->password = $newPassword;
                $user->save();

                ActivityLogger::log(
                    'password_update_success',
                    'Password updated successfully',
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully'
                ], 200);
            } else {
                ActivityLogger::log(
                    'password_update_failed',
                    'Incorrect old password provided',
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Old password is incorrect'
                ], 400);
            }
        } catch (ValidationException $e) {
            ActivityLogger::log(
                'password_update_failed',
                'Validation error: ' . json_encode($e->errors()),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log(
                'password_update_failed',
                'Unexpected error occurred: ' . $e->getMessage(),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'fail',
                'message' => 'An error occurred: ' . $e->getMessage()
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

        return view('client.pages.profile.client-details');
    }

    public function ClientDetailsInfo($client_id)
    {
        try {
            $client = User::where('id', $client_id)
            ->where('role', 'client')
            ->withCount(['foods' => function ($query) {
                $query->where('status', '!=', 'pending');
            }])
            ->withCount(['ordersBasedOnRole as total_orders'])
            ->withCount(['foods as total_complaints' => function ($query) {
                $query->whereHas('order.complain');
            }])
            ->withCount(['ordersBasedOnRole as total_customers' => function ($query) {
                $query->select(DB::raw('count(distinct user_id)'));
            }]) 
            ->first();

            if (!$client) { 
                ActivityLogger::log(
                    'view_client_details_failed',
                    'No client found with the provided ID.',
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No client found with this ID',
                ], 404);
            }

            ActivityLogger::log(
                'view_client_details_success',
                'Client details successfully retrieved.',
                $request,
                'users'
            );
            return response()->json([
                'status' => 'success',
                'data' => $client
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log(
                'view_client_details_failed',
                'An error occurred while retrieving client details: ' . $e->getMessage(),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving the customer',
                'error' => $e->getMessage()
            ], 500);
        }
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

    public function DocumentPage()
    {
        return view('client.pages.profile.client-document-page');
    }

    public function storeDocumentInfo(Request $request)
    {
        try {
            $request->validate(ValidationHelper::documentValidationRules());
            $id = $request->header('id');
            $user = User::find($id);

            if (!$user) {
                ActivityLogger::log('Client Document Submission Failed', 'Client not found', $id, 'user', 'users');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found.',
                ], 404);
            }

            $geoData = $this->formatAndFetchCoordinates($request);

            if (!$geoData) {
                ActivityLogger::log('document_upload_failed', 'Unable to fetch coordinates for address', $request, 'users');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unable to fetch coordinates for the provided address.',
                ], 400);
            }

            $oldDocImage1 = $user->doc_image1;
            $oldDocImage2 = $user->doc_image2;

            $docImage1 = $request->hasFile('doc_image1')
            ? ImageHelper::processAndSaveDocumentImage($request->file('doc_image1'), 'client', 'document', $oldDocImage1, 'doc1')
            : $oldDocImage1;

            $docImage2 = $request->hasFile('doc_image2')
            ? ImageHelper::processAndSaveDocumentImage($request->file('doc_image2'), 'client', 'document', $oldDocImage2, 'doc2')
            : $oldDocImage2;

            $docData = ItemHelper::prepareDocumentData($request, $geoData, $docImage1, $docImage2);
            $client = ItemHelper::storeOrUpdateDocument($id, $docData);

            if ($client) {
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new ClientDocumentNotification($client));

                ActivityLogger::log('document_upload_success', 'Client documents uploaded successfully', $request, 'users');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Document information saved successfully.',
                ], 200);
            }
        } catch (ValidationException $e) {
            ActivityLogger::log('document_upload_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('document_upload_failed', 'Unexpected error: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while saving document information.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatAndFetchCoordinates(Request $request)
    {
        $formattedAddress = LocationHelper::formatAddress($request);
        $geoData = LocationHelper::getCoordinatesFromAddress($formattedAddress);

        if (!$geoData) {
            throw new Exception('Unable to fetch coordinates for the provided address.');
        }

        return $geoData;
    }

    public function downloadDocImage1($client_id): BinaryFileResponse
    {
        $client = User::findOrFail($client_id);

        $filePath = base_path("public/upload/client-document/large/{$client->doc_image1}");
        if (!file_exists($filePath)) {
            abort(404, "Document 1 not found.");
        }

        $fileExtension = pathinfo($client->doc_image1, PATHINFO_EXTENSION);
        $fileName = "Document1-{$client->firstName}.{$fileExtension}";
        return response()->download($filePath, $fileName);
    }

    public function downloadDocImage2($client_id): BinaryFileResponse
    {
        $client = User::findOrFail($client_id);

        $filePath = base_path("public/upload/client-document/large/{$client->doc_image2}");
        if (!file_exists($filePath)) {
            abort(404, "Document 2 not found.");
        }

        $fileExtension = pathinfo($client->doc_image2, PATHINFO_EXTENSION);
        $fileName = "Document2-{$client->firstName}.{$fileExtension}";
        return response()->download($filePath, $fileName);
    }

}
