<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SuggestionService
{
    private const YOUTUBE_PATTERNS = [
        '/youtube\.com\/watch\?v=([^&]+)/',
        '/youtu\.be\/([^?]+)/',
        '/youtube\.com\/embed\/([^?]+)/',
    ];

    private const YOUTUBE_API_KEY = 'AIzaSyA8dWM62kVaGZy89nHiTVoINB5cu3SHpqY';

    public function processSuggestion(Request $request): array
    {
        $url = $request->url;
        $youtube_id = $this->extractVideoId($url);

        if (!$youtube_id) {
            return [
                'success' => false,
                'message' => 'URL do YouTube inválida',
                'status_code' => 422
            ];
        }

        $existingSuggestionByUser = $request->user()
            ->suggestions()
            ->where('youtube_id', $youtube_id)
            ->first();

        if ($existingSuggestionByUser) {
            return [
                'success' => false,
                'message' => 'Você já sugeriu este vídeo anteriormente',
                'status_code' => 422
            ];
        }

        $existingSuggestion = Suggestion::where('youtube_id', $youtube_id)->first();
        if ($existingSuggestion) {
            return [
                'success' => false,
                'message' => 'Este vídeo já foi sugerido por outro usuário',
                'status_code' => 422
            ];
        }

        try {
            $videoInfo = $this->getVideoInfo($youtube_id);

            if ($request->user()->is_admin) {
                return [
                    'success' => false,
                    'message' => 'Administradores devem adicionar músicas diretamente',
                    'status_code' => 422
                ];
            }

            $suggestion = $request->user()->suggestions()->create([
                'url' => $url,
                'youtube_id' => $youtube_id,
                'title' => $videoInfo['titulo'],
                'status' => Suggestion::STATUS_PENDING,
            ]);

            return [
                'success' => true,
                'message' => 'Sugestão enviada com sucesso',
                'data' => $suggestion,
                'status_code' => 201
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status_code' => 422
            ];
        }
    }

    public function updateStatus(Request $request, Suggestion $suggestion, string $status): array
    {
        if (!$request->user()->is_admin) {
            return [
                'success' => false,
                'message' => 'Não autorizado',
                'status_code' => 403
            ];
        }

        if ($suggestion->status !== Suggestion::STATUS_PENDING && $suggestion->status === $status) {
            return [
                'success' => false,
                'message' => 'Sugestão já foi processada com este status',
                'status_code' => 422
            ];
        }

        if ($status === Suggestion::STATUS_APPROVED) {
            return $this->approve($request, $suggestion);
        } else if ($status === Suggestion::STATUS_REJECTED) {
            return $this->reject($request, $suggestion);
        } else {
            return [
                'success' => false,
                'message' => 'Status inválido',
                'status_code' => 422
            ];
        }
    }

    private function approve(Request $request, Suggestion $suggestion): array
    {
        try {
            if (!$suggestion->music_id) {
                $music = Music::where('youtube_id', $suggestion->youtube_id)->first();

                if (!$music) {
                    $videoInfo = $this->getVideoInfo($suggestion->youtube_id);

                    $music = Music::create([
                        'title' => $videoInfo['titulo'],
                        'views' => $videoInfo['visualizacoes'],
                        'youtube_id' => $suggestion->youtube_id,
                        'thumbnail' => $videoInfo['thumb'],
                    ]);
                }

                $suggestion->music_id = $music->id;
            }

            $suggestion->status = Suggestion::STATUS_APPROVED;
            $suggestion->reason = $request->motivo ?? $suggestion->reason ?? 'Aprovada pelo administrador';
            $suggestion->save();

            return [
                'success' => true,
                'message' => 'Sugestão aprovada com sucesso',
                'data' => $suggestion->load('music'),
                'status_code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao aprovar sugestão: ' . $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    private function reject(Request $request, Suggestion $suggestion): array
    {
        $suggestion->status = Suggestion::STATUS_REJECTED;
        $suggestion->reason = $request->motivo;
        $suggestion->save();

        return [
            'success' => true,
            'message' => 'Sugestão rejeitada com sucesso',
            'data' => $suggestion,
            'status_code' => 200
        ];
    }

    public function extractVideoId(string $url): ?string
    {
        foreach (self::YOUTUBE_PATTERNS as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getVideoInfo(string $videoId): array
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

            $thumbnail = $snippet['thumbnails']['high']['url'] ??
                ($snippet['thumbnails']['medium']['url'] ??
                    $snippet['thumbnails']['default']['url']);

            return [
                'titulo' => $title,
                'visualizacoes' => $viewCount,
                'youtube_id' => $videoId,
                'thumb' => $thumbnail,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao obter informações do vídeo: ' . $e->getMessage());
        }
    }
}
