<?php

namespace App\Services\Musics;

use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPaginatedMusicService
{
  public function execute(Request $request): LengthAwarePaginator
  {
    $perPage = $request->input('per_page', 15);
    $sortBy = $request->input('sort_by', 'created_at');
    $sortDirection = $request->input('sort_direction', 'desc');
    $excludeIds = $request->input('exclude_ids', '');

    $query = Music::query();

    if (in_array($sortBy, ['views', 'likes', 'created_at', 'title'])) {
      $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
    } else {
      $query->orderBy('created_at', 'desc');
    }

    if (!empty($excludeIds)) {
      $excludeIdsArray = is_array($excludeIds) ? $excludeIds : explode(',', $excludeIds);
      $query->whereNotIn('id', $excludeIdsArray);
    }

    return $query->paginate($perPage);
  }
}
