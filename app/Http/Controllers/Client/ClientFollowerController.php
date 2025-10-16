<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\Follower;

class ClientFollowerController extends Controller
{

    public function FollowersListPage()
    {
        return view('client.pages.follower-customer.follower-customer-list-page');
    }

    public function FollowersList(Request $request)
    {
        try {
            $client_id = $request->header('id');

            $followers = Follower::with('customer')->where('status', 1)->where('client_id', $client_id)->latest()->get();
            $totalFollowers = $followers->count();

            ActivityLogger::log(
                'retrieve_follower_success',
                'Follower list retrieved successfully.',
                $request,
                'client_followers'
            );

            return response()->json([
                'status' => 'success',
                'data' => $followers,
                'totalFollowers' => $totalFollowers
            ], 200); 

        } catch (Exception $e) {
            ActivityLogger::log(
                'retrieve_follower_failed',
                'An error occurred: ' . $e->getMessage(),
                $request,
                'client_followers'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving followers',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $client_follower_id = $request->input('id');

            $follower = Follower::where('id',$client_follower_id)->where('client_id', $client_id)->first();
            $follower->delete();

            ActivityLogger::log(
                'delete_follower_success',
                'Follower deleted successfully.',
                $request,
                'client_followers'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Follower deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log(
                'delete_follower_failed',
                'Follower not found for deletion.',
                $request,
                'client_followers'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Follower not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log(
                'delete_follower_failed',
                'An unexpected error occurred: ' . $e->getMessage(),
                $request,
                'client_followers'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}


