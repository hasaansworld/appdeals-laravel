<?php

namespace App\Http\Controllers;

use App\Models\Listings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ListingsController extends Controller
{
    public function createListing(Request $request) {
        $listing = Listings::create([
            'user_id' => Auth::user()->id,
            'app_name' => $request->appName,
            'short_description' => $request->shortDescription,
            'youtube_url' => $request->youtubeURL,
            'introduction' => $request->introduction,
            'price' => $request->price,
            'price_currency' => $request->priceCurrency,
            'old_price' => $request->oldPrice,
            'ends_on' => Carbon::now()->addDays($request->endsIn),
        ]);

        $listing->icon_url = $this->uploadFile($request->iconFile);
        $listing->image_1 = $this->uploadFile($request->image1);
        $listing->image_2 = $this->uploadFile($request->image2);
        $listing->image_3 = $this->uploadFile($request->image3);
        $listing->save();

        return $listing;
    }

    private function uploadFile($file) {
        if (!$file) return null;
        $path = Storage::disk('s3')->put('uploads', $file);
        $url = Storage::disk('s3')->url($path);
        return $url;
    }
}
