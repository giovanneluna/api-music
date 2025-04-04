<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Models\Music;
use Illuminate\Http\Request;

class SuggestionService
{
    private const YOUTUBE_PATTERNS = [
        '/youtube\.com\/watch\?v=([^&]+)/',
        '/youtu\.be\/([^?]+)/',
        '/youtube\.com\/embed\/([^?]+)/',
    ];

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

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
            $isAdmin = $request->user()->is_admin;

            $suggestion = $request->user()->suggestions()->create([
                'url' => $url,
                'youtube_id' => $youtube_id,
                'title' => $videoInfo['titulo'],
                'status' => $isAdmin ? Suggestion::STATUS_APPROVED : Suggestion::STATUS_PENDING,
            ]);

            if ($isAdmin) {
                $music = Music::create([
                    'title' => $videoInfo['titulo'],
                    'views' => $videoInfo['visualizacoes'],
                    'youtube_id' => $youtube_id,
                    'thumbnail' => $videoInfo['thumb'],
                ]);

                $suggestion->music_id = $music->id;
                $suggestion->reason = 'Aprovação automática por administrador';
                $suggestion->save();

                return [
                    'success' => true,
                    'message' => 'Música adicionada com sucesso',
                    'data' => $suggestion->load('music'),
                    'status_code' => 201
                ];
            }

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

        if ($suggestion->status !== Suggestion::STATUS_PENDING) {
            return [
                'success' => false,
                'message' => 'Sugestão já foi processada',
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

            $suggestion->status = Suggestion::STATUS_APPROVED;
            $suggestion->music_id = $music->id;
            $suggestion->reason = $request->motivo;
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

    private function extractVideoId(string $url): ?string
    {
        foreach (self::YOUTUBE_PATTERNS as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function getVideoInfo(string $videoId): array
    {
        $url = "https://www.youtube.com/watch?v={$videoId}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => self::USER_AGENT,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);
            throw new \Exception("Erro ao acessar o YouTube: " . curl_error($ch));
        }

        curl_close($ch);

        if (!preg_match('/<title>(.+?) - YouTube<\/title>/', $response, $titleMatches)) {
            throw new \Exception("Não foi possível encontrar o título do vídeo");
        }

        $title = html_entity_decode($titleMatches[1], ENT_QUOTES);

        $views = $this->extractViewCount($response);

        if (empty($title)) {
            throw new \Exception("Vídeo não encontrado ou indisponível");
        }

        return [
            'titulo' => $title,
            'visualizacoes' => $views,
            'youtube_id' => $videoId,
            'thumb' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
        ];
    }

    private function extractViewCount(string $pageContent): int
    {
        if (preg_match('/"viewCount":\s*"(\d+)"/', $pageContent, $viewMatches)) {
            return (int)$viewMatches[1];
        }

        if (preg_match('/\"viewCount\"\s*:\s*{.*?\"simpleText\"\s*:\s*\"([\d,\.]+)\"/', $pageContent, $viewMatches)) {
            return (int)str_replace(['.', ','], '', $viewMatches[1]);
        }

        return 0;
    }
}
