<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ListingsController extends Controller
{
    public function createListing(Request $request) {
        $listing = Listing::create([
            'user_id' => Auth::user()->id,
            'app_name' => $request->appName,
            'short_description' => $request->shortDescription,
            'youtube_url' => $request->youtubeURL,
            'introduction' => $request->introduction,
            'price' => $request->price,
            'price_currency' => $request->priceCurrency,
            'old_price' => $request->oldPrice,
            'type' => $request->type,
            'url' => $request->url,
            'website_url' => $request->websiteURL,
            'ends_on' => Carbon::now()->addDays($request->endsIn),
        ]);

        $listing->icon_url = $this->uploadFile($request->iconFile);
        $listing->image_1 = $this->uploadFile($request->image1);
        $listing->image_2 = $this->uploadFile($request->image2);
        $listing->image_3 = $this->uploadFile($request->image3);
        $listing->save();

        return $listing;
    }

    public function getAllListings(Request $request,) {
        $search = $request->query('q');
        if ($search) {
            $allListings = Listing::where('app_name', 'LIKE', "%$search%")
                        ->orWhere('short_description', 'LIKE', "%$search%")
                        ->orWhere('introduction', 'LIKE', "%$search%")
                        ->get();
            $allListings = ListingResource::collection($allListings);
        } else {
            $allListings = ListingResource::collection(Listing::all());
        }
        return $allListings;
    }

    public function getListing(Request $request, $id) {
            $listing = Listing::find($id);
        return new ListingResource($listing);
    }

    private function uploadFile($file) {
        if (!$file) return null;
        $path = Storage::disk('s3')->put('uploads', $file);
        $url = Storage::disk('s3')->url($path);
        return $url;
    }
}
