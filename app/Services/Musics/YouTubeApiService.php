<?php

namespace App\Services\Musics;

use Illuminate\Support\Facades\Http;

class YouTubeApiService
{
  private const YOUTUBE_API_KEY = 'AIzaSyA8dWM62kVaGZy89nHiTVoINB5cu3SHpqY';

  public function getVideoInfo(string $youtubeId): array
  {
    try {
      $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
        'id' => $youtubeId,
        'key' => self::YOUTUBE_API_KEY,
        'part' => 'snippet,statistics',
      ]);

      if ($response->failed()) {
        $statusCode = $response->status();
        $errorData = $response->json();
        $errorMessage = 'Falha ao acessar a API do YouTube';

        if (isset($errorData['error']['message'])) {
          $errorMessage .= ': ' . $errorData['error']['message'];
        } else {
          $errorMessage .= ': Código ' . $statusCode;
        }

        if ($statusCode == 403 && isset($errorData['error']['errors'][0]['reason']) && $errorData['error']['errors'][0]['reason'] == 'quotaExceeded') {
          throw new \Exception('Quota da API do YouTube excedida. Tente novamente mais tarde.');
        }

        throw new \Exception($errorMessage);
      }

      $data = $response->json();

      if (empty($data['items'])) {
        throw new \Exception('Vídeo não encontrado ou indisponível');
      }

      $videoData = $data['items'][0];
      $snippet = $videoData['snippet'];
      $statistics = $videoData['statistics'];

      $title = $snippet['title'];
      $viewCount = isset($statistics['viewCount']) ? (int)$statistics['viewCount'] : 0;
      $likeCount = isset($statistics['likeCount']) ? (int)$statistics['likeCount'] : 0;

      $thumbnail = $snippet['thumbnails']['high']['url'] ??
        ($snippet['thumbnails']['medium']['url'] ??
          $snippet['thumbnails']['default']['url']);

      return [
        'title' => $title,
        'views' => $viewCount,
        'likes' => $likeCount,
        'youtube_id' => $youtubeId,
        'thumbnail' => $thumbnail,
      ];
    } catch (\Exception $e) {
      throw new \Exception('Erro ao obter informações do vídeo: ' . $e->getMessage());
    }
  }
}
