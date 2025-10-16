<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Food;

class ClientNotificationController extends Controller
{
  public function NotificationPage(){
      return view('client.pages.notification.notification-list');
  }

  public function LimitedNotificationList(Request $request) 
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

  public function NotificationList(Request $request) 
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

}