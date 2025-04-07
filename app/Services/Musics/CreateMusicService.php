<?php

namespace App\Services\Musics;

use App\Models\Music;
use Illuminate\Support\Facades\Log;

class CreateMusicService
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

  public function execute(array $data): Music
  {
    Log::info('Criando música com dados:', $data);

    if (isset($data['youtube_id'])) {
      $existing = Music::where('youtube_id', $data['youtube_id'])->first();
      if ($existing) {
        Log::warning('Tentativa de criar música com ID duplicado:', ['youtube_id' => $data['youtube_id']]);
        throw new \Exception('Este vídeo já existe na base de dados.');
      }
    }

    if (isset($data['youtube_id']) && (!isset($data['title']) || !isset($data['views']) || !isset($data['thumbnail']))) {
      try {
        Log::info('Buscando informações do vídeo do YouTube:', ['youtube_id' => $data['youtube_id']]);
        $videoInfo = $this->youtubeApiService->getVideoInfo($data['youtube_id']);
        Log::info('Informações obtidas do YouTube:', $videoInfo);

        $formattedTitle = $this->formatMusicTitleService->format($videoInfo['title']);

        $data = array_merge($data, [
          'title' => $formattedTitle ?? $videoInfo['title'] ?? $data['title'] ?? '',
          'views' => $videoInfo['views'] ?? $data['views'] ?? 0,
          'likes' => $videoInfo['likes'] ?? $data['likes'] ?? 0,
          'thumbnail' => $videoInfo['thumbnail'] ?? $data['thumbnail'] ?? '',
        ]);
      } catch (\Exception $e) {
        Log::error('Erro ao obter informações do vídeo:', ['error' => $e->getMessage(), 'youtube_id' => $data['youtube_id']]);
        throw new \Exception('Erro ao obter informações do vídeo: ' . $e->getMessage());
      }
    } else if (isset($data['title'])) {
      $data['title'] = $this->formatMusicTitleService->format($data['title']);
    }

    Log::info('Criando novo registro de música com dados processados:', $data);
    return Music::create($data);
  }
}
