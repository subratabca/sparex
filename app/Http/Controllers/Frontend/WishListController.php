<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use Exception;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;

class WishListController extends Controller
{
    public function storeWishList(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:products,id',
            ]);

            $customer_id = $request->header('id');
            $product_id = $request->input('id');

            $customer = User::find($customer_id);
            if (!$customer) {
                ActivityLogger::log('wishlist_creation_failed', 'Unauthorized. Need to login.', $request, 'wishlists');
                return response()->json([
                    'status' => 'unauthorized',
                    'message' => 'Customer not found. Need to login.',
                ], 401);
            }

            $existingWishlistItem = Wishlist::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->first();
            if ($existingWishlistItem) {
                ActivityLogger::log('wishlist_creation_failed', 'This item is already in your wishlist.', $request, 'wishlists');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This item is already in your wishlist.',
                ], 409);
            }

            $wishlistCount = Wishlist::where('customer_id', $customer_id)->count();
            if ($wishlistCount >= 3) {
                ActivityLogger::log('wishlist_creation_failed', 'You can add a maximum of 3 items to your wishlist.', $request, 'wishlists');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You can add a maximum of 3 items to your wishlist.',
                ], 403);
            }

            $wishlist = Wishlist::create([
                'customer_id' => $customer_id,
                'product_id' => $product_id,
            ]);

            if ($wishlist) {
                ActivityLogger::log('wishlist_creation_success', 'Item added to wishlist successfully.', $request, 'wishlists');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Item added to wishlist successfully.',
                    'data' => $wishlist,
                ], 201);
            } else {
                ActivityLogger::log('wishlist_creation_failed', 'Failed to add to wishlist.', $request, 'wishlists');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to add to wishlist.',
                ], 500);
            }

        } catch (ValidationException $e) {
            ActivityLogger::log('wishlist_creation_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'wishlists');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log('wishlist_creation_failed', 'An error occurred while processing the request: ' . $e->getMessage(), $request, 'wishlists');
            return response()->json([
                'status' => 'failed',
                'message' => 'Product request failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function count(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            if (!$customer_id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized! Need to login.',
                ], 401);
            }
            
            $count = Wishlist::where('customer_id', $customer_id)->count();

            return response()->json([
                'status' => 'success',
                'count' => $count,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving wishlist count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function wishListPage()
    {
        return view('frontend.pages.wishlist.wishlist-page');
    }

    public function getWishListInfo(Request $request)
    {
        try {
            $customer_id = $request->header('id');

            $wishlist = Wishlist::with(['product' => function ($query) {
                $query->where('status', 'published');
            }, 'product.client'])
                ->where('customer_id', $customer_id)
                ->get();

            ActivityLogger::log('retrieve_wishlist_success', 'Wishlist items retrieved successfully.', $request, 'wishlists');
            return response()->json([
                'status' => 'success',
                'data' => $wishlist
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_wishlist_failed', 'An error occurred while retrieving the wishlist: ' . $e->getMessage(), $request, 'wishlists');
            return response()->json([
                'status' => 'failed',
                'message' => 'Wishlist information not found',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            $wishlist = Wishlist::findOrFail($id);
            $wishlist->delete();

            ActivityLogger::log('wishlist_delete_success', 'Item deleted from wishlist successfully.', $request, 'wishlists');
            return response()->json([
                'status' => 'success',
                'message' => 'Item deleted from wishlist successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log('wishlist_delete_failed', 'Wishlist item not found: ' . $e->getMessage(), $request, 'wishlists');
            return response()->json([
                'status' => 'failed',
                'message' => 'Wishlist information not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            ActivityLogger::log('wishlist_delete_failed', 'An error occurred while deleting the wishlist item: ' . $e->getMessage(), $request, 'wishlists');
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}