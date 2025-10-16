<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use App\Helpers\LocationHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\User;


class AdminProfileController extends Controller
{
    public function ProfilePage()
    {
        return view('backend.pages.profile.profile-page');
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
                    'message' => 'Admin not found'
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

    public function oldUpdateProfile(Request $request)
    {
        try{
            $request->validate([
                'firstName' => 'required|string|min:3|max:50',
                'lastName' => 'required|string|min:3|max:50',
                'mobile' => 'required|string|min:11|max:50',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $email=$request->header('email');
            $id=$request->header('id');

            $user = User::find($id);
            if (!$user) {
                ActivityLogger::log('Profile Update Failed','User not found',$id,'admin','users');
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User not found'
                ], 404);
            }

            $firstName=$request->input('firstName');
            $lastName=$request->input('lastName');
            $mobile=$request->input('mobile');

            if ($request->hasFile('image')) {
                $large_image_path = base_path('public/upload/admin-profile/large/');
                $medium_image_path = base_path('public/upload/admin-profile/medium/');
                $small_image_path = base_path('public/upload/admin-profile/small/');

                if (!empty($user->image)) {
                    foreach (['large', 'medium', 'small'] as $size) {
                        $path = base_path("public/upload/admin-profile/{$size}/" . $user->image);
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }

                $image = $request->file('image');
                $manager = new ImageManager(new Driver());
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $img = $manager->read($image);

                $img->resize(100, 100, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); 
                })
                ->save($large_image_path . $imageName);

                $img->resize(800, 80, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($medium_image_path . $imageName);

                $img->resize(60, 60, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($small_image_path . $imageName);

                $user->image = $imageName;
            } 

            $user->update([
                'firstName'=>$firstName,
                'lastName'=>$lastName,
                'mobile'=>$mobile,
            ]);

            ActivityLogger::log('Profile Update Success','Profile updated successfully',$id,'admin','users');
            return response()->json([
                'status' => 'success',
                'message' => 'Profile update successful',
            ],200);

        }catch (ValidationException $e) {
            ActivityLogger::log('Profile Update Failed','Validation errors while updating profile',$id,'admin','users');
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation errors',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('Profile Update Failed','Error occurred while updating profile' . ': ' . $e->getMessage(),$id,'admin','users');
            return response()->json([
                'status' => 'fail',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function PasswordPage()
    {
        return view('backend.pages.profile.password-change-page');
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
        $client = Client::findOrFail($client_id);

        $filePath = base_path("public/upload/client-document/large/{$client->doc_image2}");
        if (!file_exists($filePath)) {
            abort(404, "Document 2 not found.");
        }

        $fileExtension = pathinfo($client->doc_image2, PATHINFO_EXTENSION);
        $fileName = "Document2-{$client->firstName}.{$fileExtension}";
        return response()->download($filePath, $fileName);
    }
}
