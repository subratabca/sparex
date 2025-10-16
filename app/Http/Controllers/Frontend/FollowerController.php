<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; 
use Exception;
use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Follower;

class FollowerController extends Controller
{
    public function storeFollower(Request $request)
    {
        try {
            $request->validate([
                'client_id' => 'required|exists:users,id',
            ]);

            $customer_id = $request->header('id');
            $client_id = $request->input('client_id');

            $customer = User::find($customer_id);
            if (!$customer) {
                ActivityLogger::log(
                    'follow_failed',
                    'Unauthorized: Customer not found. Need to login.',
                    $request,
                    'followers'
                );
                return response()->json([
                    'status' => 'unauthorized',
                    'message' => 'Unauthorized. Need to login.',
                ], 401);
            }

            $existingFollower = Follower::where('client_id', $client_id)
            ->where('customer_id', $customer_id)
            ->first();

            if ($existingFollower) {
                $existingFollower->status = !$existingFollower->status;
                $existingFollower->save();

                $message = $existingFollower->status ? 'Following started' : 'Unfollowed';
                ActivityLogger::log(
                    'follow_success',
                    "Follow/Unfollow updated successfully. Status: {$message}",
                    $request,
                    'followers'
                );
            } else {
                Follower::create([
                    'client_id' => $client_id,
                    'customer_id' => $customer_id,
                    'status' => 1, 
                ]);
                $message = 'Following started';
                ActivityLogger::log(
                    'follow_success',
                    'Following started successfully.',
                    $request,
                    'followers'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'isFollowing' => $existingFollower ? $existingFollower->status : true,
            ], 200); 

        } catch (ValidationException $e) {
            ActivityLogger::log(
                'follow_failed',
                'Validation failed: ' . json_encode($e->errors()),
                $request,
                'followers'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log(
                'follow_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'followers'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Follower request failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function FollowedListPage()
    {
        return view('frontend.pages.followed-client.followed-client-list-page');
    }


    public function FollowedClientsList(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            $followedClients = Follower::with('client') 
            ->where('status', 1) 
            ->where('customer_id', $customer_id) 
            ->latest() 
            ->get();

            $totalClients = $followedClients->count();

            ActivityLogger::log(
                'retrieve_follower_success',
                'Follower list retrieved successfully.',
                $request,
                'followers'
            );
            return response()->json([
                'status' => 'success',
                'data' => $followedClients, 
                'totalClients' => $totalClients
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log(
                'retrieve_follower_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'followers'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving the followed clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}


