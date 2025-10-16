<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Contact;

class AdminContactMessageController extends Controller
{
    public function ContactPage()
    {
        return view('backend.pages.contact-message.contact-message-list');
    }

    public function index(Request $request)
    {
        try {
            $messages = Contact::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving foods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function DetailsPage(){
        return view('backend.pages.contact-message.details');
    }

    public function show($id)
    {
        try {
            $message = Contact::where('id', $id)->first();
            return response()->json([
                'status' => 'success',
                'data' => $message
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');

        try {
            $deleted = Contact::where('id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message deleted successfully.'
                ], 200);
            } 
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to delete data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}