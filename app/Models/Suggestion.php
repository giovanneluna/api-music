<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Suggestion extends Model
{
    use HasFactory;

    protected $table = 'suggestions';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'url',
        'youtube_id',
        'title',
        'status',
        'user_id',
        'music_id',
        'reason'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function music(): BelongsTo
    {
        return $this->belongsTo(Music::class, 'music_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
