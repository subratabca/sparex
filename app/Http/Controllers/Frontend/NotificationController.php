<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Wishlist;

class NotificationController extends Controller
{
  public function NotificationPage()
  {
      return view('frontend.pages.notification.notification-page');
  }

  public function LimitedNotificationList(Request $request) 
  {
      try {
          $email = $request->header('email');

          if (!$email) {
              return response()->json([
                  'status' => 'failed',
                  'message' => 'Unauthorized! Need to login.'
              ], 401);
          }

          $user = User::where('email', $email)->first();
          if ($user) {
              $unreadNotifications = $user->unreadNotifications()->latest()->take(4)->get();
              $unreadCount = $unreadNotifications->count();
              $readNotifications = $user->readNotifications()->latest()->take(4 - $unreadCount)->get();

              return response()->json([
                  'status' => 'success',
                  'message' => 'Request Successful',
                  'data' => $user,
                  'unreadNotifications' => $unreadNotifications,
                  'readNotifications' => $readNotifications,
              ], 200);
          } 

      } catch (Exception $e) {
          return response()->json([
              'status' => 'failed',
              'message' => $e->getMessage()
          ], 500);
      }
  }

  public function getNotificationList(Request $request) 
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
              $unreadNotifications = $user->unreadNotifications()->latest()->get();
              $unreadCount = $unreadNotifications->count();
              $readNotifications = $user->readNotifications()->latest()->get();

              return response()->json([
                  'status' => 'success',
                  'message' => 'Request Successful',
                  'data' => $user,
                  'unreadNotifications' => $unreadNotifications,
                  'readNotifications' => $readNotifications,
              ], 200);
          } 
          
      } catch (Exception $e) {
          return response()->json([
              'status' => 'failed',
              'message' => $e->getMessage()
          ], 500);
      }
  }

  public function MarkAsRead(Request $request)
  {
      try {
          $email = $request->header('email');
          if (!$email) {
              return response()->json([
                  'status' => 'error',
                  'message' => 'Email header is missing.'
              ], 400);
          }

          $user = User::where('email', $email)->first();
          if (!$user) {
              return response()->json([
                  'status' => 'error',
                  'message' => 'User not found.'
              ], 404);
          }

          $user->unreadNotifications->markAsRead();
          $unreadCount = $user->unreadNotifications->count();

          return response()->json([
              'status' => 'success',
              'message' => 'All notifications marked as read.',
              'unreadCount' => $unreadCount,
          ], 200);

      } catch (\Exception $e) {
          return response()->json([
              'status' => 'error',
              'message' => 'An error occurred while marking notifications as read.',
              'error' => $e->getMessage(),
          ], 500);
      }
  }

  public function deleteNotification(Request $request, $notificationId)
  {
      try {
          $email = $request->header('email');
          $user = User::where('email', $email)->first();

          if (!$user) {
              return response()->json([
                  'status' => 'failed',
                  'message' => 'User not found.',
              ], 404);
          }

          $notification = $user->notifications()->find($notificationId);

          if ($notification) {
              $notification->delete();

              return response()->json([
                  'status' => 'success',
                  'message' => 'Notification deleted successfully.',
              ], 200);
          } else {
              return response()->json([
                  'status' => 'failed',
                  'message' => 'Notification not found.',
              ], 404);
          }
      } catch (Exception $e) {
          return response()->json([
              'status' => 'failed',
              'message' => 'An error occurred while deleting the notification: ' . $e->getMessage(),
          ], 500);
      }
  }

  public function customerDetailsPage(Request $request)
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
      
      return view('frontend.pages.notification.customer-details');
  }

  public function getCustomerDetails($customer_id)
  {
      try {
          $customer = User::withCount(['orders', 'productComplaints', 'receivedComplaints'])
              ->withLocation()
              ->where('role', 'customer')
              ->where('id', $customer_id)
              ->first();

          if (!$customer) {
              return response()->json([
                  'status' => 'failed',
                  'message' => 'No customer found with this ID',
              ], 404);
          }

          $totalClients = OrderItem::whereHas('order', function($query) use ($customer) {
                  $query->where('customer_id', $customer->id);
              })
              ->distinct('client_id')
              ->count('client_id');

          // Prepare the response data
          $customerData = [
              'id' => $customer->id,
              'firstName' => $customer->firstName,
              'lastName' => $customer->lastName,
              'email' => $customer->email,
              'mobile' => $customer->mobile,
              'is_email_verified' => $customer->is_email_verified,
              'image' => $customer->image ?: null,
              'doc_image1' => $customer->doc_image1 ?: null,
              'doc_image2' => $customer->doc_image2 ?: null,
              'status' => (int) $customer->status,
              'address1' => $customer->address1,
              'address2' => $customer->address2,
              'zip_code' => $customer->zip_code,
              'country' => $customer->country,
              'county' => $customer->county,
              'city' => $customer->city,
              'total_clients' => $totalClients,
              'total_orders' => $customer->orders_count,
              'total_product_complaints' => $customer->product_complaints_count,
              'total_received_complaints' => $customer->received_complaints_count,
              'created_at' => $customer->created_at,
              'updated_at' => $customer->updated_at,
          ];

          return response()->json([
              'status' => 'success',
              'message' => 'Customer details retrieved successfully',
              'data' => $customerData
          ], 200);

      } catch (Exception $e) {
          return response()->json([
              'status' => 'failed',
              'message' => 'An error occurred while retrieving customer details',
              'error' => $e->getMessage()
          ], 500);
      }
  }


}