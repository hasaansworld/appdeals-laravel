<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'approved' => $this->approved,
            'nameId' => $this->name_id,
            'appIcon' => $this->icon_url,
            'appName' => $this->app_name,
            'shortDescription' => $this->short_description,
            'youtubeURL' => $this->youtube_url,
            'image1' => $this->image_1,
            'image2' => $this->image_2,
            'image3' => $this->image_3,
            'introduction' => $this->introduction,
            'price' => $this->price,
            'priceCurrency' => $this->price_currency,
            'oldPrice' => $this->old_price,
            'type' => $this->type,
            'url' => $this->url,
            'websiteURL' => $this->website_url,
            'endsOn' => $this->ends_on,
        ];
    }
}
