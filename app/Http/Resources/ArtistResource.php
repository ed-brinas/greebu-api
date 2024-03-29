<?php

namespace App\Http\Resources;

use App\Models\ArtistType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        $this->profile->loadCount('followers', 'following');
        return [
            'id'            => $this->id,
            'artist_name'   => $this->profile->business_name,
            'artist_type'   => (new ArtistTypeResource($this->artistType))->title,
            'avatar'        => $this->profile->avatar,
            'ratings'       => $this->avgRating,
            'reviews'       => count($this->reviews),
            'bio'           => $this->profile->bio,
            'song_requests' => $this->song_requests_count ?? 0,
            'genres'        => new GenreCollection($this->genres),
            'song'          => 'https://res.cloudinary.com/daorvtlls/video/upload/v1687411869/merrow-rock-skyline-pigeon-elton-john_h0chm4.mp3',
            'follower'      => $this->profile->followers_count,
            'following'     => $this->profile->following_count,
        ];
        return parent::toArray($request);
    }
}
