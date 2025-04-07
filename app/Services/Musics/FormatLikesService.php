<?php

namespace App\Services\Musics;

class FormatLikesService
{
  public function format(int $number): string
  {
    if ($number === null) {
      return '0';
    }

    return app(FormatViewsService::class)->format($number);
  }
}
