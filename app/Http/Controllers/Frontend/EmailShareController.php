<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ItemShareMail; 
use Exception;
use App\Models\Food;
use App\Models\ProductShare;

class EmailShareController extends Controller
{

    public function shareToEmail(Request $request)
    {
        try {
            $request->validate([
                'itemID' => 'required|exists:food,id',
                'emails' => 'required|json',
            ]);

            $id = $request->input('itemID');
            $customerId = $request->header('id');

            $emails = json_decode($request->input('emails'), true); 

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid email format.',
                ], 422);
            }

            $invalidEmails = [];
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = $email;
                }
            }

            if (count($invalidEmails) > 0) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid email addresses: ' . implode(', ', $invalidEmails),
                ], 422);
            }

            $food = Food::with('foodImages')->findOrFail($id);
            $emails = array_unique($emails);
            $recipientEmails = implode(',', $emails);

            try {
                Mail::to($emails)->send(new ItemShareMail($food));
                ProductShare::create([
                    'customer_id'    => $customerId,
                    'food_id'        => $food->id,
                    'recipient_email'=> $recipientEmails,
                    'shared_via'     => 'email',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Item shared successfully to the provided email addresses.',
                ], 200);

            } catch (Exception $e) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to send email to recipients.',
                    'error' => $e->getMessage(),
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while sharing to Email.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function EmailShareListPage()
    {
        return view('frontend.pages.email-share.email-share-page');
    }

    public function EmailShareList(Request $request)
    {
        try {
            $id = $request->header('id');
            $productShareList = ProductShare::with('customer','food')->where('customer_id',$id)->latest()->get();
            return response()->json([
                'status' => 'success',
                'data' => $productShareList
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_order_failed', 'System error: ' . $e->getMessage(), $request, 'orders');
            return response()->json([
                'status' => 'failed',
                'message' => 'Product share list information not found',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            $productShare = ProductShare::findOrFail($id); 
            $productShare->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Data deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Product share info not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}



