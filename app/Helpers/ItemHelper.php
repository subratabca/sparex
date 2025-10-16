<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\About;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Hero;
use App\Models\User;
use App\Models\Coupon;
use App\Models\DeliveryCharge;

class ItemHelper
{
    public static function prepareItemData($request, $imagePath = null, $isUpdate = false)
    {
        $client_id = $request->input('client_id') ?? $request->header('id');

        $data = [
            'client_id' => $client_id,
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'name' => $request->input('name'),
            'weight' => $request->input('weight'),
            'current_stock' => $request->input('current_stock', 0),
            'price' => $request->input('price', 0.00),
            'discount_price' => $request->input('discount_price', 0.00),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'country_id' => $request->input('country_id'),
            'county_id' => $request->input('county_id'),
            'city_id' => $request->input('city_id'),
            'zip_code' => $request->input('zip_code'),
            'expire_date' => $request->input('expire_date'),
            'collection_date' => $request->input('collection_date'),
            'start_collection_time' => $request->input('start_collection_time'),
            'end_collection_time' => $request->input('end_collection_time'),
            'description' => $request->input('description'),
            'image' => $imagePath,
            'has_variants' => $request->input('has_variants', false),
            'has_brand' => $request->input('has_brand', false),
            'is_free' => $request->input('is_free', false),
            'has_discount_price' => $request->input('has_discount_price', false),
        ];

        if (!$isUpdate) {
            $data['accept_tnc'] = $request->input('accept_tnc');
        }

        return $data;
    }

    public static function storeOrUpdateItem($data, $item = null)
    {
        if ($item) {
            $item->update($data);
            return $item;
        }
        return Product::create($data);
    }

    // public static function saveMultiImages($images, $productId)
    // {
    //     $multiImagePaths = ImageHelper::processAndSaveImage($images, 'multi_images', true);
    //     foreach ($multiImagePaths as $imagePath) {
    //         ProductImage::create([
    //             'product_id' => $productId,
    //             'image' => $imagePath,
    //         ]);
    //     }
    // }

    public static function saveVariants($variants, $productId)
    {
        $existingVariants = ProductVariant::where('product_id', $productId)->get();
        $requestColorSizes = collect($variants)->map(function ($variant) {
            return $variant['color'] . '|' . $variant['size'];
        })->toArray();

        foreach ($existingVariants as $existingVariant) {
            $colorSize = $existingVariant->color . '|' . $existingVariant->size;
            if (!in_array($colorSize, $requestColorSizes)) {
                $existingVariant->delete();
            }
        }

        foreach ($variants as $variant) {
            $existingVariant = ProductVariant::where('product_id', $productId)
                ->where('color', $variant['color'])
                ->where('size', $variant['size'])
                ->first();

            if ($existingVariant) {
                // Calculate stock difference for movement tracking
                $stockDifference = $variant['qty2'] - $existingVariant->current_stock;
                
                $existingVariant->update([
                    'current_stock' => $variant['qty2']
                ]);

                if ($stockDifference != 0) {
                    StockMovement::create([
                        'product_id' => $productId,
                        'variant_id' => $existingVariant->id,
                        'client_id' => $existingVariant->product->client_id,
                        'quantity' => $stockDifference,
                        'movement_type' => $stockDifference > 0 
                            ? StockMovement::TYPE_ADJUSTMENT 
                            : StockMovement::TYPE_DAMAGED,
                        'notes' => 'Stock adjustment during update'
                    ]);
                }
            } else {
                $newVariant = ProductVariant::create([
                    'product_id' => $productId,
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'current_stock' => $variant['qty2'],
                ]);

                StockMovement::create([
                    'product_id' => $productId,
                    'variant_id' => $newVariant->id,
                    'client_id' => $newVariant->product->client_id,
                    'quantity' => $newVariant->current_stock,
                    'movement_type' => StockMovement::TYPE_UPLOAD,
                    'notes' => 'Initial product stock upload with variant'
                ]);
            }
        }
    }

    public static function prepareAboutData($request, $imagePath = null)
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'donator' => $request->input('donator'),
            'donatee' => $request->input('donatee'),
            'image' => $imagePath,
        ];
    }

    public static function storeOrUpdateAbout($data, $about = null)
    {
        if ($about) {
            $about->update($data);
        } else {
            $about = About::create($data);
        }
        return $about;
    }

    public static function prepareSiteSettingData($request, $logoPath = null)
    {
        return [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone1' => $request->input('phone1'),
            'phone2' => $request->input('phone2'),
            'website_name' => $request->input('website_name'),
            'slogan' => $request->input('slogan'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'country' => $request->input('country'),
            'zip_code' => $request->input('zip_code'),
            'facebook' => $request->input('facebook'),
            'linkedin' => $request->input('linkedin'),
            'youtube' => $request->input('youtube'),
            'description' => $request->input('description'),
            'refund' => $request->input('refund'),
            'terms' => $request->input('terms'),
            'privacy' => $request->input('privacy'),
            'logo' => $logoPath,
        ];
    }

    public static function storeOrUpdateSiteSetting($data, $siteSetting = null)
    {
        if ($siteSetting) {
            $siteSetting->update($data);
            return $siteSetting;
        }

        return SiteSetting::create($data);
    }

    public static function findSiteSettingById($id)
    {
        return SiteSetting::findOrFail($id);
    }

    public static function prepareCategoryData($request, $imagePath = null)
    {
        return [
            'name' => ucfirst($request->input('name')),
            'max_request_by_customer' => $request->input('max_request_by_customer'),
            'image' => $imagePath,
        ];
    }

    public static function storeOrUpdateCategory($data, $category = null)
    {
        if ($category) {
            $category->update($data);
            return $category;
        }

        return Category::create($data);
    }

    public static function findCategoryById($id)
    {
        return Category::findOrFail($id);
    }

    public static function prepareBrandData($request, $imagePath = null)
    {
        return [
            'name' => ucfirst($request->input('name')),
            'image' => $imagePath,
        ];
    }

    public static function storeOrUpdateBrand($data, $brand = null)
    {
        if ($brand) {
            $brand->update($data);
            return $brand;
        }

        return Brand::create($data);
    }

    public static function findBrandById($id)
    {
        return Brand::findOrFail($id);
    }

    public static function prepareHeroData($request, $imagePath = null)
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image' => $imagePath,
        ];
    }

    public static function storeOrUpdateHero($data, $hero = null)
    {
        if ($hero) {
            $hero->update($data);
            return $hero;
        } else {
            return Hero::create($data);
        }
    }

    public static function prepareProfileData($request, $imagePath = null)
    {
        return [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'mobile' => $request->input('mobile'),
            'image' => $imagePath,
        ];
    }

    public static function storeOrUpdateProfile($data, $user = null)
    {
        $user->update($data);
        return $user;
    }

    public static function prepareDocumentData($request, $geoData, $docImage1 = null, $docImage2 = null)
    {
        return [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'mobile' => $request->input('mobile'),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'zip_code' => $request->input('zip_code'),
            'country_id' => $request->input('country_id'),
            'county_id' => $request->input('county_id'),
            'city_id' => $request->input('city_id'),
            'latitude' => $geoData['latitude'],
            'longitude' => $geoData['longitude'],
            'doc_image1' => $docImage1,
            'doc_image2' => $docImage2,
        ];
    }

    public static function storeOrUpdateDocument($userId, $data)
    {
        $user = User::find($userId);

        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }

    public static function prepareCouponData($request, $isUpdate = false)
    {
        $client_id = $request->input('client_id') ?? $request->header('id');

        $data = [
            'client_id' => $client_id,
            'coupon_name' => $request->input('coupon_name'),
            'coupon_discount' => $request->input('coupon_discount'),
            'expire_date' => $request->input('expire_date'),
            'status' => $request->input('status', true), // Default to active
        ];

        return $data;
    }

    public static function storeOrUpdateCoupon($data, $coupon = null)
    {
        if ($coupon) {
            $coupon->update($data);
            return $coupon;
        }
        return Coupon::create($data);
    }

    public static function prepareDeliveryChargeData($request, $isUpdate = false)
    {
        $client_id = $request->input('client_id');

        $data = [
            'client_id' => $client_id,
            'inside_city_2kg' => $request->input('inside_city_2kg'),
            'inside_city_5kg' => $request->input('inside_city_5kg'),
            'inside_city_10kg' => $request->input('inside_city_10kg'),
            'inside_city_above_10kg' => $request->input('inside_city_above_10kg'),
            'outside_city_2kg' => $request->input('outside_city_2kg'),
            'outside_city_5kg' => $request->input('outside_city_5kg'),
            'outside_city_10kg' => $request->input('outside_city_10kg'),
            'outside_city_above_10kg' => $request->input('outside_city_above_10kg'),
        ];

        return $data;
    }

    public static function storeOrUpdateDeliveryCharge($data, $deliveryCharge = null)
    {
        if ($deliveryCharge) {
            $deliveryCharge->update($data);
            return $deliveryCharge;
        }
        return DeliveryCharge::create($data);
    }
}

