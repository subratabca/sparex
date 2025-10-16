<?php

namespace App\Helpers;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidationHelper
{
    public static function itemValidationRules($isUpdate = false, $isClient = false, $productId = null)
    {
        $rules = [
            'name' => ['required','string','max:50',
                Rule::unique('products')->when($isUpdate, function ($query) use ($productId) {
                    return $query->ignore($productId);
                })
            ],
            'weight' => 'required|numeric|min:0.01',
            'client_id' => $isClient ? 'prohibited' : 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id',
            'address1' => 'required|string|min:3|max:255',
            'address2' => 'nullable|string|min:3|max:255',
            'country_id' => 'required|integer|exists:countries,id',
            'county_id' => 'required|integer|exists:counties,id',
            'city_id' => 'required|integer|exists:cities,id',
            'zip_code' => 'required|string|max:50',
            'expire_date' => 'required|date',
            'description' => 'required|string|min:10',
            'collection_date' => 'required|date',
            'start_collection_time' => 'required',
            'end_collection_time' => 'required|after:start_collection_time',
            'has_variants' => 'required|boolean',
            'has_brand' => 'required|boolean',
            'is_free' => 'required|boolean',
            'is_free' => 'required|boolean',
        ];

        if (!$isUpdate) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            $rules['multi_images'] = 'required|array|max:2';
            $rules['multi_images.*'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            $rules['accept_tnc'] = 'required|boolean';
        } else {
            $rules['image'] = 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            $rules['multi_images'] = 'sometimes|array|max:2';
            $rules['multi_images.*'] = 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        if (request()->has_variants) {
            $rules['variants'] = 'required|array';
            $rules['variants.*.color'] = 'required|string|max:50';
            $rules['variants.*.size'] = 'required|string|max:50';
            $rules['variants.*.qty2'] = 'required|integer|min:1';

            // Check for duplicates within the request
            $rules['variants'] = function ($attribute, $value, $fail) {
                $colorSizeCombinations = [];
                foreach ($value as $variant) {
                    $combination = $variant['color'] . '|' . $variant['size'];
                    if (in_array($combination, $colorSizeCombinations)) {
                        $fail('Duplicate variant: A variant with the same color and size already exists in the request.');
                        return;
                    }
                    $colorSizeCombinations[] = $combination;
                }
            };
        } else {
            $rules['current_stock'] = 'required|integer|min:1';
        }

        if (request()->has_brand) {
            $rules['brand_id'] = 'required|integer|exists:brands,id';
        }

        if (!request()->is_free) {
            $rules['price'] = 'required|numeric|min:1|regex:/^\d+(\.\d{1,2})?$/';
        }

        if (!request()->is_free && request()->has_discount_price) {
            $rules['discount_price'] = [
                'required',
                'numeric',
                'min:1',
                'regex:/^\d+(\.\d{1,2})?$/',
                function ($attribute, $value, $fail) {
                    $price = request()->price;
                    if ($value >= $price) {
                        $fail('The discount price must be less than the regular price.');
                    }
                },
            ];
        }

        return $rules;
    }

    public static function aboutValidationRules($isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|min:3|max:100',
            'description' => 'required|string|min:10',
            'donator' => 'required|string|min:10',
            'donatee' => 'required|string|min:10',
            'image' => $isUpdate ? 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        return $rules;
    }

    public static function siteSettingValidationRules($isUpdate = false, $id = null)
    {
        $uniqueEmailRule = $isUpdate ? "unique:site_settings,email,{$id}" : 'unique:site_settings,email';

        return [
            'name' => 'required|string|min:3|max:50',
            'email' => "required|email|max:50|{$uniqueEmailRule}",
            'phone1' => 'required|string|min:10|max:15',
            'phone2' => 'nullable|string|min:10|max:15',
            'website_name' => 'required|string|min:3|max:50',
            'slogan' => 'required|string|min:3|max:100',
            'address' => 'required|string|min:3|max:50',
            'city' => 'required|string|min:3|max:50',
            'country' => 'required|string|min:3|max:50',
            'zip_code' => 'required|string|min:3|max:10',
            'facebook' => 'nullable|url|max:50',
            'linkedin' => 'nullable|url|max:50',
            'youtube' => 'nullable|url|max:50',
            'description' => 'required|string|min:20',
            'refund' => 'required|string|min:20',
            'terms' => 'required|string|min:20',
            'privacy' => 'required|string|min:20',
            'logo' => $isUpdate ? 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public static function categoryValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|min:3|max:50|unique:categories' . ($isUpdate ? ',name,' . request()->input('id') : ''),
            'max_request_by_customer' => 'required|integer|min:1|max:10',
            'image' => $isUpdate ? 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        return $rules;
    }

    public static function brandValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|min:3|max:50|unique:brands' . ($isUpdate ? ',name,' . request()->input('id') : ''),
            'image' => $isUpdate ? 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        return $rules;
    }

    public static function heroValidationRules($isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|min:3|max:100',
            'description' => 'required|string|min:10',
            'image' => $isUpdate ? 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        return $rules;
    }

    public static function profileValidationRules()
    {
        return [
            'firstName' => 'required|string|min:3|max:50',
            'lastName' => 'required|string|min:3|max:50',
            'mobile' => 'required|string|min:11|max:50',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public static function documentValidationRules()
    {
        $docValidationRules = [
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'mobile' => 'required|string|min:11|max:50',

            'address1' => 'required|string|min:3|max:50',
            'zip_code' => 'required|string|min:3|max:50',
            'country_id' => 'required|integer|exists:countries,id',
            'county_id' => 'required|integer|exists:counties,id',
            'city_id' => 'required|integer|exists:cities,id',

            'doc_image1' => 'required|image|mimes:jpeg,png,jpg,pdf,webp|max:2048',
            'doc_image2' => 'required|image|mimes:jpeg,png,jpg,pdf,webp|max:2048',
        ];

        return $docValidationRules;
    }

    public static function couponValidationRules($isUpdate = false)
    {
        $clientId = request()->header('id');
        $ignoreId = $isUpdate ? request()->input('id') : 'NULL';

        return [
            'coupon_name' => 'required|string|min:3|max:50|unique:coupons,coupon_name,'.$ignoreId.',id,client_id,'.$clientId,
            'coupon_discount' => 'required|numeric|min:0|max:100',
            'expire_date' => 'required|date|after:today',
        ];
    }

    public static function deliveryChargeValidationRules()
    {
        $rules = [
            'inside_city_2kg' => 'required|numeric|min:0',
            'inside_city_5kg' => 'required|numeric|min:0',
            'inside_city_10kg' => 'required|numeric|min:0',
            'inside_city_above_10kg' => 'required|numeric|min:0',
            'outside_city_2kg' => 'required|numeric|min:0',
            'outside_city_5kg' => 'required|numeric|min:0',
            'outside_city_10kg' => 'required|numeric|min:0',
            'outside_city_above_10kg' => 'required|numeric|min:0',
        ];

        return $rules;
    }

    public static function validateDeliveryCharge($data)
    {
        $validator = Validator::make($data, self::deliveryChargeValidationRules());

        $validator->after(function ($validator) use ($data) {
            // Inside city checks
            if (!($data['inside_city_2kg'] < $data['inside_city_5kg'] &&
                  $data['inside_city_2kg'] < $data['inside_city_10kg'] &&
                  $data['inside_city_2kg'] < $data['inside_city_above_10kg'])) {
                $validator->errors()->add('inside_city_2kg', 'Inside city 2kg price must be less than 5kg, 10kg, and above 10kg.');
            }
            if (!($data['inside_city_5kg'] < $data['inside_city_10kg'] &&
                  $data['inside_city_5kg'] < $data['inside_city_above_10kg'])) {
                $validator->errors()->add('inside_city_5kg', 'Inside city 5kg price must be less than 10kg and above 10kg.');
            }
            if (!($data['inside_city_10kg'] < $data['inside_city_above_10kg'])) {
                $validator->errors()->add('inside_city_10kg', 'Inside city 10kg price must be less than above 10kg.');
            }

            // Outside city checks
            if (!($data['outside_city_2kg'] < $data['outside_city_5kg'] &&
                  $data['outside_city_2kg'] < $data['outside_city_10kg'] &&
                  $data['outside_city_2kg'] < $data['outside_city_above_10kg'])) {
                $validator->errors()->add('outside_city_2kg', 'Outside city 2kg price must be less than 5kg, 10kg, and above 10kg.');
            }
            if (!($data['outside_city_5kg'] < $data['outside_city_10kg'] &&
                  $data['outside_city_5kg'] < $data['outside_city_above_10kg'])) {
                $validator->errors()->add('outside_city_5kg', 'Outside city 5kg price must be less than 10kg and above 10kg.');
            }
            if (!($data['outside_city_10kg'] < $data['outside_city_above_10kg'])) {
                $validator->errors()->add('outside_city_10kg', 'Outside city 10kg price must be less than above 10kg.');
            }
        });

        return $validator;
    }
}
