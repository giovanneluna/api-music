<?php

namespace App\Models;

use App\Services\Musics\FormatViewsService;
use App\Services\Musics\FormatLikesService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    use HasFactory;

    protected $table = 'musics';

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
        return app(FormatViewsService::class)->format($this->views);
    }

    public function getLikesFormattedAttribute(): string
    {
        if ($this->likes === null) {
            return '0';
        }
        return app(FormatLikesService::class)->format($this->likes);
    }

    public function suggestions()
    {
        return $this->hasMany(Suggestion::class, 'music_id');
    }
}
