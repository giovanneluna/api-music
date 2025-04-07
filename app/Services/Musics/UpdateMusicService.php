<?php

namespace App\Services\Musics;

use App\Models\Music;

class UpdateMusicService
{
  protected YouTubeApiService $youtubeApiService;
  protected FormatMusicTitleService $formatMusicTitleService;

  public function __construct(
    YouTubeApiService $youtubeApiService,
    FormatMusicTitleService $formatMusicTitleService
  ) {
    $this->youtubeApiService = $youtubeApiService;
    $this->formatMusicTitleService = $formatMusicTitleService;
  }

  public function execute(Music $music, array $data): Music
  {
    if (isset($data['youtube_id']) && $data['youtube_id'] !== $music->youtube_id) {
      try {
        $videoInfo = $this->youtubeApiService->getVideoInfo($data['youtube_id']);

        $formattedTitle = $this->formatMusicTitleService->format($videoInfo['title']);

        $data = array_merge($data, [
          'title' => $formattedTitle ?? $videoInfo['title'] ?? $data['title'] ?? $music->title,
          'views' => $videoInfo['views'] ?? $data['views'] ?? $music->views,
          'likes' => $videoInfo['likes'] ?? $data['likes'] ?? $music->likes,
          'thumbnail' => $videoInfo['thumbnail'] ?? $data['thumbnail'] ?? $music->thumbnail,
        ]);
      } catch (\Exception $e) {
        report($e);
      }
    } else if (isset($data['title'])) {
      $data['title'] = $this->formatMusicTitleService->format($data['title']);
    }

    $music->update($data);
    return $music;
  }
}
