<?php

namespace App\Services\Musics;

class FormatMusicTitleService
{
  public function format(string $title): string
  {
    $dupla = 'Tião Carreiro e Pardinho';
    $newTitle = $title;

    $duplaEscaped = preg_quote($dupla, '/');

    $padrao1 = "/^" . $duplaEscaped . "\s*[-–—:]\s*(.+)$/i";
    $padrao2 = "/^(.+)\s*[-–—:]\s*" . $duplaEscaped . "$/i";

    if (preg_match($padrao1, $title, $matches)) {
      $newTitle = trim($matches[1]);
    } elseif (preg_match($padrao2, $title, $matches)) {
      $newTitle = trim($matches[1]);
    }

    $newTitle = preg_replace('/\s*[-–—:]\s*(Pagode|Terra Roxa|Sertanejo|Official|Oficial|Video|Audio|Clip|MV)\s*$/i', '', $newTitle);

    return $newTitle;
  }
}
