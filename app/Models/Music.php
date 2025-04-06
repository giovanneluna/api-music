<?php

namespace App\Models;

use App\Services\MusicService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    use HasFactory;

    protected $table = 'music';

    protected $fillable = [
        'title',
        'views',
        'likes',
        'youtube_id',
        'thumbnail',
    ];

    protected $casts = [
        'views' => 'integer',
        'likes' => 'integer',
    ];

    protected $appends = [
        'views_formatted',
        'likes_formatted',
    ];

    public function getViewsFormattedAttribute(): string
    {
        return app(MusicService::class)->formatViews($this->views);
    }

    public function getLikesFormattedAttribute(): string
    {
        if ($this->likes === null) {
            return '0';
        }
        return app(MusicService::class)->formatLikes($this->likes);
    }

    public function suggestions()
    {
        return $this->hasMany(Suggestion::class, 'music_id');
    }
}
