<?php

namespace App\Services\Suggestions;

use Illuminate\Support\Facades\Http;

class GetVideoInfoService
{
    private const YOUTUBE_API_KEY = 'AIzaSyA8dWM62kVaGZy89nHiTVoINB5cu3SHpqY';

    public function execute(string $videoId): array
    {
        try {
            $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
                'id' => $videoId,
                'key' => self::YOUTUBE_API_KEY,
                'part' => 'snippet,statistics',
            ]);

            if ($response->failed()) {
                throw new \Exception('Falha ao acessar a API do YouTube: ' . $response->status());
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
                'titulo' => $title,
                'visualizacoes' => $viewCount,
                'likes' => $likeCount,
                'youtube_id' => $videoId,
                'thumb' => $thumbnail,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao obter informações do vídeo: ' . $e->getMessage());
        }
    }
}
