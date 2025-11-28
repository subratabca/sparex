<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use App\Helpers\LocationHelper;
use Illuminate\Validation\ValidationException;
use App\Notifications\Customer\CustomerDocumentNotification;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Helpers\ActivityLogger;
use App\Models\User;
use Exception;


class ProfileController extends Controller
{
    public function profilePage()
    {
        return view('frontend.pages.profile.profile-page');
    }

    public function getProfile(Request $request)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized! Need to login.'
                ], 400);
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $unreadNotifications = $user->unreadNotifications;
                $readNotifications = $user->readNotifications;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Request Successful',
                    'data' => $user,
                    'unreadNotifications' => $unreadNotifications,
                    'readNotifications' => $readNotifications,
                ], 200);
            }else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Customer not found'
                ], 404);
            }

        } catch (Exception $e) {
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
        return view('frontend.pages.profile.password-change-page');
    }

    public function updatePassword(Request $request)
    {
        try {
            $validated = $request->validate([
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

    public function documentPage()
    {
        return view('frontend.pages.profile.customer-document-page');
    }

    public function storeDocumentInfo(Request $request)
    {
        try {
            $request->validate(ValidationHelper::documentValidationRules());
            $id = $request->header('id');
            $user = User::find($id);

            if (!$user) {
                ActivityLogger::log('document_upload_failed', 'User not found', $request, 'users');
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
            ? ImageHelper::processAndSaveDocumentImage($request->file('doc_image1'), 'customer', 'document', $oldDocImage1, 'doc1')
            : $oldDocImage1;

            $docImage2 = $request->hasFile('doc_image2')
            ? ImageHelper::processAndSaveDocumentImage($request->file('doc_image2'), 'customer', 'document', $oldDocImage2, 'doc2')
            : $oldDocImage2;

            $docData = ItemHelper::prepareDocumentData($request, $geoData, $docImage1, $docImage2);
            $customer = ItemHelper::storeOrUpdateDocument($id, $docData);

            if ($customer) {
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new CustomerDocumentNotification($customer));

                ActivityLogger::log('document_upload_success', 'Customer documents uploaded successfully', $request, 'users');
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
        } catch (\Exception $e) {
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

    public function downloadDocImage1($customer_id): BinaryFileResponse
    {
        $customer = User::findOrFail($customer_id);

        $filePath = base_path("public/upload/customer-document/large/{$customer->doc_image1}");
        if (!file_exists($filePath)) {
            abort(404, "Document 1 not found.");
        }

        $fileExtension = pathinfo($customer->doc_image1, PATHINFO_EXTENSION);
        $fileName = "Document1-{$customer->firstName}.{$fileExtension}";
        return response()->download($filePath, $fileName);
    }

    public function downloadDocImage2($customer_id): BinaryFileResponse
    {
        $customer = User::findOrFail($customer_id);

        $filePath = base_path("public/upload/customer-document/large/{$customer->doc_image2}");
        if (!file_exists($filePath)) {
            abort(404, "Document 2 not found.");
        }

        $fileExtension = pathinfo($customer->doc_image2, PATHINFO_EXTENSION);
        $fileName = "Document2-{$customer->firstName}.{$fileExtension}";
        return response()->download($filePath, $fileName);
    }
}
