<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\NewsletterSubscriber;
use App\Models\About;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Country;
use App\Models\County;
use App\Models\City;
use App\Models\ZipCode;

class PagesController extends Controller
{
    public function aboutPage()
    {
        return view('frontend.pages.about-page');
    }

    public function aboutPageInfo()
    {
        try {
            $data = About::first();

            if ($data) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request Successful',
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No data found',
                    'data' => []
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage() 
            ], 500);
        }
    }

    public function contactPage()
    {
        return view('frontend.pages.contact-us-page');
    }

    public function storeContactInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:3|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'message' => 'required|string',
            ]);

            $contact = Contact::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'message' => $validated['message'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Message has been sent successfully.',
                'data' => $contact,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing the request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function productDetailsPage($id)
    {
        $product = Product::findOrFail($id);
        return view('frontend.pages.product-details-page', compact('product'));
    }

    public function getProductDetails($id)
    {
        try {
            $data = Product::with('client', 'category', 'variants', 'productImages')->findOrFail($id);

            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));
            $relatedData = Product::where('expire_date', '>=', $currentDate)
                                ->where('status', 'published')
                                ->where('id', '!=', $id)
                                ->inRandomOrder()
                                ->limit(7)
                                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
                'data' => $data,
                'relatedData' => $relatedData
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Product Not Found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    public function getClients(Request $request)
    {
        try {
            $clients = User::where('role','client')->where('is_email_verified',1)->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $clients
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getBrands(Request $request)
    {
        try {
            $brand = Brand::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $brand
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategories(Request $request)
    {
        try {
            $category = Category::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $category
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getCountries()
    {
        try {
            $countries = Country::all(['id', 'name']); 
            return response()->json([
                'data' => $countries
            ], 200); 

        }  catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to fetch countries. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCountiesByCountry($countryId)
    {
        try {
            $counties = County::where('country_id', $countryId)->get(['id', 'name']);
            
            return response()->json([
                'data' => $counties
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to fetch counties. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCiiesByCounty($countyId)
    {
        try {
            $cities = City::where('county_id', $countyId)->get(['id', 'name']);
            
            return response()->json([
                'data' => $cities
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to fetch cities. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getZipCodeByCity($cityId)
    {
        try {
            //$zipcode = ZipCode::where('city_id', $cityId)->first();
            $zipcode = City::where('id', $cityId)->first();
            
            return response()->json([
                'data' => $zipcode
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to fetch zip codes. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storNnewsletterSubscriptionInfo(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|max:50',
            ]);

            $subscriber = NewsletterSubscriber::where('email', $request->input('email'))->first();

            if (!$subscriber) {
                $subscriber = NewsletterSubscriber::create([
                    'email' => $request->input('email'),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Subscription successful.'
                ], 201); 

            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'You are already subscribed with this email.'
                ], 200); 
            }

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422); // Status code 422 for unprocessable entity
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred during subscription.',
                'error' => $e->getMessage(),
            ], 500); // Status code 500 for server error
        }
    }
}


      