<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use App\Helpers\LocationHelper;
use App\Helpers\ActivityLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Notifications\Product\ProductUploadNotification;
use Illuminate\Validation\ValidationException; 
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StockMovement;


class ClientProductController extends Controller
{
    public function ProductPage()
    {
        return view('client.pages.product.product-list');
    }

    public function index(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $products = Product::with('productImages','variants','category','brand')->where('client_id',$client_id)->latest()->get();
            ActivityLogger::log(
                'retrieve_item_success',
                'Successfully retrieved items.',
                $request,
                'products'
            );

            return response()->json([
                'status' => 'success',
                'data' => $products
            ], 200); 

        } catch (Exception $e) {
            ActivityLogger::log(
                'retrieve_item_failed',
                'Failed to retrieve items. Error: ' . $e->getMessage(),
                $request,
                'products'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function CreatePage(Request $request)
    {
        $client_id = $request->header('id');
        $client = User::find($client_id);

        if (!$client) {
            return redirect()->back()->with('error', 'Client not found.');
        }

        return view('client.pages.product.create', compact('client'));
    }

    private function formatAndFetchCoordinates(Request $request)
    {
        try {
            $formattedAddress = LocationHelper::formatAddress($request);
            $geoData = LocationHelper::getCoordinatesFromAddress($formattedAddress);

            if (!$geoData) {
                ActivityLogger::log(
                    'retrieve_item_failed', 
                    'Unable to fetch coordinates for the provided address.', 
                    $request, 
                    'products'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unable to fetch coordinates for the provided address.',
                ], 422);
            }

            ActivityLogger::log(
                'retrieve_item_success', 
                'Coordinates fetched successfully.', 
                $request, 
                'products'
            );

            return $geoData;
        } catch (Exception $e) {
            ActivityLogger::log(
                'retrieve_item_failed', 
                'Error occurred while fetching coordinates: ' . $e->getMessage(), 
                $request, 
                'products'
            );

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while fetching coordinates.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(ValidationHelper::itemValidationRules(false, true));
            $geoData = $this->formatAndFetchCoordinates($request);

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'item')
                : null;

            $productData = ItemHelper::prepareItemData($request, $imagePath);
            $productData['latitude'] = $geoData['latitude'];
            $productData['longitude'] = $geoData['longitude'];
            $product = ItemHelper::storeOrUpdateItem($productData);

            // Record initial stock movement for variant products
            if ($request->has_variants && $request->variants) {
                ItemHelper::saveVariants($request->variants, $product->id);

                // Update product stock from variants
                $product->refresh();
                $product->current_stock = $product->variants->sum('current_stock');
                $product->save();
            }

            // Record initial stock movement for non-variant products
            if (!$request->has_variants) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'client_id' => $product->client_id,
                    'quantity' => $product->current_stock,
                    'movement_type' => StockMovement::TYPE_UPLOAD,
                    'notes' => 'Initial product stock upload without variant'
                ]);
            }

            if ($request->hasFile('multi_images') && count($request->file('multi_images')) > 0) {
                ImageHelper::saveMultiImages($request->file('multi_images'), $product->id);
            }

            ActivityLogger::log(
                'item_creation_success',
                'Product created successfully.',
                $request,
                'products'
            );

            DB::commit();
            $user = User::where('role', 'admin')->first();
            $user->notify(new ProductUploadNotification($product));

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully.',
                'data' => $product
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            ActivityLogger::log(
                'item_creation_failed',
                'Validation failed during product creation. Errors: ' . json_encode($e->errors()),
                $request,
                'products'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            ActivityLogger::log(
                'item_creation_failed',
                'Product creation failed due to an error. Error: ' . $e->getMessage(),
                $request,
                'products'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function DetailsPage(Request $request)
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

        return view('client.pages.product.product-details');
    }

    public function show(Request $request,$id)
    {
        try {
            $product = Product::with('productImages','client','variants','category', 'brand', 'country','county','city')->find($id);

            if (!$product) {
                ActivityLogger::log(
                    'retrieve_item_failed',
                    'Product not found.',
                    $request,
                    'products'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Product not found'
                ], 404);
            }

            ActivityLogger::log(
                'retrieve_item_success',
                'Item info found successfully.',
                $request,
                'products'
            );
            return response()->json([
                'status' => 'success',
                'data' => $product
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log(
                'retrieve_item_failed',
                'Error occurred while retrieving product details: ' . $e->getMessage(),
                $request,
                'products'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function EditPage()
    {
        return view('client.pages.product.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $product_id = $request->input('id');
            $product = Product::findOrFail($product_id);
            $originalStock = $product->current_stock; 
            
            $request->validate(ValidationHelper::itemValidationRules(true, true, $product_id));
            $geoData = $this->formatAndFetchCoordinates($request);

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'item', false, $product->image)
                : $product->image;

            $productData = ItemHelper::prepareItemData($request, $imagePath, true);
            $productData['latitude'] = $geoData['latitude'];
            $productData['longitude'] = $geoData['longitude'];
            $updatedProduct = ItemHelper::storeOrUpdateItem($productData, $product);

            // Handle stock movement for non-variant products
            if ($request->has_variants && $request->variants) {
                ItemHelper::saveVariants($request->variants, $product->id);

                // Update product stock from variants
                $updatedProduct->refresh();
                $updatedProduct->current_stock = $updatedProduct->variants->sum('current_stock');
                $updatedProduct->save();
            }

            // Handle stock movement for non-variant products
            if (!$updatedProduct->has_variants) {
                $stockDifference = $updatedProduct->current_stock - $originalStock;
                if ($stockDifference != 0) {
                    StockMovement::create([
                        'product_id' => $updatedProduct->id,
                        'client_id' => $updatedProduct->client_id,
                        'quantity' => $stockDifference,
                        'movement_type' => $stockDifference > 0 
                            ? StockMovement::TYPE_ADJUSTMENT 
                            : StockMovement::TYPE_DAMAGED,
                        'notes' => 'Stock adjustment during update'
                    ]);
                }
            }
            
            ActivityLogger::log(
                'item_update_success',
                'Item updated successfully.',
                $request,
                'products'
            );
            
            DB::commit(); 
            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully.',
                'data' => $updatedProduct
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack(); 
            ActivityLogger::log(
                'item_update_failed',
                'Validation failed: ' . json_encode($e->errors()),
                $request,
                'products'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack(); 
            ActivityLogger::log(
                'item_update_failed',
                'Error occurred while updating product details: ' . $e->getMessage(),
                $request,
                'products'
            );
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function EditMultiImgPage()
    {
        return view('client.pages.product.edit-multi-img');
    }

    public function updateMultiImg(Request $request)
    {
        try {
            $request->validate([
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $id = $request->input('id');
            $productImage = ProductImage::findOrFail($id);

            if ($request->hasFile('image')) {
                ImageHelper::deleteOldImages($productImage->image, 'multi_images');
                $uploadPath = ImageHelper::processAndSaveImage($request->file('image'), 'multi_images'); 
            } else {
                $uploadPath = $productImage->image;
            }

            $productImage->update([
                'image' => $uploadPath
            ]);

            ActivityLogger::log(
                'item_multi_img_update_success',
                'Product multi image updated successfully',
                $request,
                'product_images'
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Product multi image updated successfully.'
            ], 200);

        } catch (ValidationException $e) {
           ActivityLogger::log(
                'item_multi_img_update_failed',
                'Validation failed: ' . json_encode($e->errors()),
                $request,
                'product_images'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::log(
                'item_multi_img_update_failed',
                'Error occurred: ' . $e->getMessage(),
                $request,
                'product_images'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Food update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $product_id = $request->input('id');
        DB::beginTransaction();
        try {
            $product = Product::with([
                'productImages',
                'orderItems.order.shippingAddress',
                'orderItems.order.clientOrders',
                'complaints.conversations'
            ])->findOrFail($product_id);

            // Delete images
            ImageHelper::deleteOldImages($product->image, 'item');
            ImageHelper::deleteMultipleImages($product->productImages, 'multi_images');

            // Handle order items and related data
            $product->orderItems()->each(function($orderItem) {
                $order = $orderItem->order;
                $orderItem->delete();

                // Delete order if it has no remaining items
                if($order->orderItems()->count() === 0) {
                    
                    // Delete related order data
                    $order->shippingAddress()->delete();
                    $order->clientOrders()->delete();
                    $order->delete();
                }
            });

            // Handle product-specific complaints and conversations
            foreach ($product->complaints as $complaint) {
                // First delete conversations and their messages
                foreach ($complaint->conversations as $conversation) {
                    if (!empty($conversation->reply_message)) {
                        ImageHelper::deleteImagesFromHTML($conversation->reply_message);
                    }
                    $conversation->delete();
                }
                
                if (!empty($complaint->message)) {
                    ImageHelper::deleteImagesFromHTML($complaint->message);
                }
                
                $complaint->delete();
            }

            $product->variants()->delete();
            $product->productShares()->delete();
            $product->stockMovements()->delete();
            $product->delete();

            DB::commit();

            ActivityLogger::log('item_delete_success', 'Product and related data deleted', $request, 'products');
            return response()->json([
                'status' => 'success',
                'message' => 'Product and all related data deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            ActivityLogger::log('item_delete_failed', 'Product not found', $request, 'products');
            return response()->json([
                'status' => 'failed',
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            ActivityLogger::log('item_delete_failed', 'Deletion error: ' . $e->getMessage(), $request, 'products');
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteVariant(Request $request)
    {
        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');

        try {
            $variant = ProductVariant::where('product_id', $productId)
                ->where('id', $variantId)
                ->firstOrFail();

            // Check total variants for this product
            $variantCount = ProductVariant::where('product_id', $productId)->count();
            
            // Prevent deletion if only one variant remains
            if ($variantCount <= 1) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Cannot delete the last remaining variant'
                ], 400);
            }

            $variant->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Variant deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            ActivityLogger::log('variant_delete_failed', 'Variant not found.', $request, 'product_variants');
            return response()->json([
                'status' => 'failed',
                'message' => 'Variant not found',
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