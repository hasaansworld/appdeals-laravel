<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ListingsController extends Controller
{
    public function createListing(Request $request) {
        $nameId = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', strtolower($request->appName)));
        $existingRecordsCount = Listing::where('name_id', $nameId)->count();
        if ($existingRecordsCount > 0) {
            $suffix = 2;
            while (Listing::where('name_id', $nameId . $suffix)->exists()) {
                $suffix++;
            }
            $nameId .= $suffix;
        }

        $listing = Listing::create([
            'user_id' => Auth::user()->id,
            'name_id' => $nameId,
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
            'approved' => Auth::user()->email === 'hasaanahmed.pk@gmail.com',
        ]);

        $listing->icon_url = $this->uploadFile($request->iconFile);
        $listing->image_1 = $this->uploadFile($request->image1);
        $listing->image_2 = $this->uploadFile($request->image2);
        $listing->image_3 = $this->uploadFile($request->image3);
        $listing->save();

        return $listing;
    }

    public function getAllListings(Request $request) {
        $search = $request->query('q');
        if ($search) {
            $allListings = Listing::where(function ($query) use($search) {
                $query->where('app_name', 'LIKE', "%$search%")
                    ->orWhere('short_description', 'LIKE', "%$search%")
                    ->orWhere('introduction', 'LIKE', "%$search%");
            })
            ->where('approved', true)
            ->where('ends_on', '>', Carbon::now())
            ->get();
            $allListings = ListingResource::collection($allListings);
        } else {
            $allListings = ListingResource::collection(
                Listing::where('approved', true)
                    ->where('ends_on', '>', Carbon::now())
                    ->get()
            );
        }
        return $allListings;
    }

    public function getRandomListings() {
        $randomListings = ListingResource::collection(
            Listing::where('approved', true)
                    ->where('ends_on', '>', Carbon::now())->inRandomOrder()->take(6)->get()
        );
        return $randomListings;
    }

    public function getListingsCount() {
        $count = Listing::where('approved', true)->count();
        return $count;
    }

    public function getListing(Request $request, $name_id) {
        $listing = Listing::where('name_id', $name_id)
            ->where('approved', true)
            ->where('ends_on', '>', Carbon::now())
            ->first();
        return new ListingResource($listing);
    }

    public function canSubmit() {
        $user = Auth::user();
        if ($user->email === 'hasaanahmed.pk@gmail.com') return ['userCanSubmit' => true];
        $createdCount = Listing::where('user_id', $user->id)->where('created_at', '>', Carbon::now()->subDay())->count();
        return ['userCanSubmit' => $createdCount === 0];
    }

    private function uploadFile($file) {
        if (!$file) return null;
        $path = Storage::disk('s3')->put('uploads', $file);
        $url = Storage::disk('s3')->url($path);
        return $url;
    }
}
