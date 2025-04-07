<?php

namespace App\Services\Musics;

use App\Models\Music;
use App\Models\Suggestion;

class CreateMusicFromSuggestionService
{
  protected YouTubeApiService $youtubeApiService;

  public function __construct(YouTubeApiService $youtubeApiService)
  {
    $this->youtubeApiService = $youtubeApiService;
  }

  public function execute(Suggestion $suggestion): ?Music
  {
    try {
      $youtubeId = $suggestion->youtube_id;
      $videoInfo = $this->youtubeApiService->getVideoInfo($youtubeId);

      $music = Music::create([
        'title' => $videoInfo['title'],
        'views' => $videoInfo['views'],
        'likes' => $videoInfo['likes'],
        'youtube_id' => $youtubeId,
        'thumbnail' => $videoInfo['thumbnail'],
      ]);

      return $music;
    } catch (\Exception $e) {
      report($e);
      return null;
    }
  }
}
