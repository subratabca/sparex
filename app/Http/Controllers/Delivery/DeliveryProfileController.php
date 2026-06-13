<?php
namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use App\Helpers\LocationHelper;
use Illuminate\Validation\ValidationException;
use App\Notifications\Delivery\DeliverytDocumentNotification;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\DeliveryVehicle;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class DeliveryProfileController extends Controller
{
    public function profilePage()
    { 
        return view('delivery.pages.profile.profile-page');
    }

    public function getProfile(Request $request)
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

    public function passwordPage()
    {
        return view('delivery.pages.profile.password-change-page');
    }

    public function updatePassword(Request $request)
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

    public function Logout()
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ], 200)->withCookie(cookie()->forget('token'));
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while logging out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deliveryDetailsPage(Request $request)
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

    public function getDeliveryDetails($client_id)
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

    public function documentPage()
    {
        return view('delivery.pages.profile.document-page');
    }

    public function storeVehicle(Request $request)
    {
        try {
            $deliveryId = $request->header('id');
            
            // Check if vehicle already exists
            $existingVehicle = DeliveryVehicle::where('delivery_id', $deliveryId)->first();
            if ($existingVehicle) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vehicle information already exists. Use update instead.'
                ], 400);
            }
            
            // Validate request
            $validated = ValidationHelper::deliveryVehicleValidationRules(false, $deliveryId);
            $request->validate($validated);
            
            // Process image
            $imageName = ImageHelper::processDeliveryVehicleImage($request);
            
            // Prepare data
            $vehicleData = ItemHelper::prepareDeliveryVehicleData($request, $deliveryId, $imageName);
            
            // Create vehicle
            $vehicle = DeliveryVehicle::create($vehicleData);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle information saved successfully',
                'data' => ItemHelper::formatVehicleResponse($vehicle)
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save vehicle information'
            ], 500);
        }
    }

    public function updateVehicle(Request $request)
    {
        try {
            $deliveryId = $request->header('id');
            
            // Find existing vehicle
            $vehicle = DeliveryVehicle::where('delivery_id', $deliveryId)->firstOrFail();
            
            // Validate request
            $validated = ValidationHelper::deliveryVehicleValidationRules(true, $deliveryId, $vehicle->id);
            $request->validate($validated);
            
            // Process image
            $oldImage = $vehicle->image;
            $imageName = ImageHelper::processDeliveryVehicleImage($request, $oldImage);
            
            // Prepare data
            $vehicleData = ItemHelper::prepareDeliveryVehicleData($request, $deliveryId, $imageName);
            
            // Update vehicle
            $vehicle->update($vehicleData);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle information updated successfully',
                'data' => ItemHelper::formatVehicleResponse($vehicle)
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found. Please add vehicle information first.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update vehicle information'
            ], 500);
        }
    }

    public function vehiclePage(Request $request)
    {
        $id = $request->header('id');
        return view('delivery.pages.profile.vehicle-page', ['deliveryId' => $id]);
    }

    public function getVehicleInfo(Request $request)
    {
        try {
            $deliveryId = $request->header('id');
            
            $vehicle = DeliveryVehicle::where('delivery_id', $deliveryId)->first();
            
            if (!$vehicle) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No vehicle information found'
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $vehicle
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch vehicle information'
            ], 500);
        }
    }

}
