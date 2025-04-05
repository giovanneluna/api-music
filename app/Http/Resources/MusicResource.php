<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'youtube_id' => $this->youtube_id,
            'views' => $this->views,
            'views_formatted' => $this->views_formatted,
            'thumbnail' => $this->thumbnail,
            'created_at' => $this->created_at,
        ];
    }
}
