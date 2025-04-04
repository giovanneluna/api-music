<?php

namespace App\Models;

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

    public function suggestions()
    {
        return $this->hasMany(Suggestion::class, 'music_id');
    }

}
