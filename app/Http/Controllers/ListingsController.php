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
            ->where('active', true)
            ->where('approved', true)
            ->where('ends_on', '>', Carbon::now())
            ->latest()
            ->get();
            $allListings = ListingResource::collection($allListings);
        } else {
            $allListings = ListingResource::collection(
                Listing::where('approved', true)
                    ->where('active', true)
                    ->where('ends_on', '>', Carbon::now())
                    ->latest()
                    ->get()
            );
        }
        return $allListings;
    }

    public function getRandomListings(Request $request) {
        $query = Listing::where('approved', true)
                ->where('active', true)
                ->where('ends_on', '>', Carbon::now());
        if ($request->query('exclude')) {
            $query = $query->whereNot('name_id', $request->query('exclude'));
        }
        $listings = $query->inRandomOrder()->take(6)->get();
        $randomListings = ListingResource::collection($listings);
        return $randomListings;
    }

    public function getListingsCount() {
        $count = Listing::where('approved', true)->count();
        return $count;
    }

    public function getListing(Request $request, $name_id) {
        $user = Auth::user();
        $listing = Listing::where('name_id', $name_id)
            ->where(function ($query) use($user) {
                $query->where('approved', true)
                ->where('active', true)
                ->orWhere('user_id', !is_null($user) ? $user->id : 0);
            })
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

    public function getUsersListings() {
        $user = Auth::user();
        $usersListings = Listing::where('user_id', $user->id)->latest()->get();
        return ListingResource::collection($usersListings);
    }

    public function updateListing(Request $request, $name_id) {
        $user = Auth::user();
        $listing = Listing::where('name_id', $name_id)->first();
        if (!$listing) {
            abort(404, "Not found");
        }
        if ($listing->user_id !== $user->id) {
            return response("Forbidden", 403);
        }

        $files = $request->allFiles();
        $exclude = ['icon_file', 'image_1', 'image_2', 'image_3', 'ends_in'];

        $data = $request->except($exclude);

        foreach ($files as $key => $file) {
            Log::info("File:", $key);
        }

        $listing->update($data);
        return response('Successfully updated');
    }

    private function uploadFile($file) {
        if (!$file) return null;
        $local = app()->environment() === 'local';
        $path = Storage::disk('s3')->put($local ? 'local/uploads' : 'uploads', $file);
        $url = Storage::disk('s3')->url($path);
        return $url;
    }
}
