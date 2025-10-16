<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use Share;

class SocialShareController extends Controller
{
    public function index()
    {

        $items = Food::all();
        $item = $items->first();

        $shareButtons = null;

        if ($item) {
            $imageUrl = asset('upload/food/large/' . ($item->image ?? 'no_image.jpg'));
            $shareUrl = url('/food-details/' . $item->id);
            $shareText = "Check out this food item: " . $item->name;

            // Generate social share buttons
            $shareButtons = Share::page($shareUrl, $shareText)
                ->facebook()
                ->twitter()
                ->whatsapp()
                ->linkedin();
        }

        return view('frontend.pages.socialshare', compact('shareButtons', 'items', 'item'));
    }
}

