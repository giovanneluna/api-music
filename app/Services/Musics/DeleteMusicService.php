<?php

namespace App\Services\Musics;

use App\Models\Music;

class DeleteMusicService
{
  public function execute(Music $music): bool
  {
    try {
      return $music->delete();
    } catch (\Exception $e) {
      report($e);
      return false;
    }
  }
}
