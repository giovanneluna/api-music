<?php

namespace App\Services\Musics;

class FormatViewsService
{
  public function format(int $number): string
  {
    if ($number === null) {
      return '0';
    }

    if ($number >= 1_000_000_000) {
      return number_format($number / 1_000_000_000, 1) . 'B';
    }

    if ($number >= 1_000_000) {
      return number_format($number / 1_000_000, 1) . 'M';
    }

    if ($number >= 1_000) {
      return number_format($number / 1_000, 1) . 'K';
    }

    return (string)$number;
  }
}
