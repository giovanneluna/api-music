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
        'youtube_id',
        'thumbnail',
    ];

    protected $casts = [
        'views' => 'integer',
    ];

    protected $appends = [
        'views_formatted',
    ];

    public function getViewsFormattedAttribute(): string
    {
        return app(MusicService::class)->formatViews($this->views);
    }

    public function suggestions()
    {
        return $this->hasMany(Suggestion::class, 'music_id');
    }
}
