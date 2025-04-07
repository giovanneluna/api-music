<?php

namespace App\Services\Musics;

use App\Models\Music;
use Illuminate\Support\Collection;

class GetTopMusicsService
{
  public function execute(int $limit = 5, string $sortDirection = 'desc'): Collection
  {
    return Music::query()
      ->orderBy('views', $sortDirection)
      ->take($limit)
      ->get();
  }
}
