<?php

namespace App\Services\Musics;

use App\Models\Music;

class RefreshVideoDataService
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

    public function execute(Music $music): Music
    {
        try {
            $videoInfo = $this->youtubeApiService->getVideoInfo($music->youtube_id);

            $formattedTitle = $this->formatMusicTitleService->format($videoInfo['title']);

            $music->update([
                'title' => $formattedTitle,
                'views' => $videoInfo['views'],
                'likes' => $videoInfo['likes'],
                'thumbnail' => $videoInfo['thumbnail'],
            ]);

            return $music->refresh();
        } catch (\Exception $e) {
            report($e);
            return $music;
        }
    }
}
