<?php

namespace App\Services\Musics;

use App\Models\Music;

class GetMusicWithFormattedViewsService
{
  public function execute(Music $music): Music
  {
    return $music;
  }
}
