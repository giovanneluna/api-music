<?php

namespace App\Services;

use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Suggestion;

class MusicService
{
    private const YOUTUBE_API_KEY = 'AIzaSyA8dWM62kVaGZy89nHiTVoINB5cu3SHpqY';

    public function formatViews(int $number): string
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

    public function formatLikes(int $number): string
    {
        if ($number === null) {
            return '0';
        }

        return $this->formatViews($number);
    }

    public function formatMusicTitle(string $title): string
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

    public function getPaginatedMusic(Request $request): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $excludeIds = $request->input('exclude_ids', '');

        $query = Music::query();

        if (in_array($sortBy, ['views', 'likes', 'created_at', 'title'])) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (!empty($excludeIds)) {
            $excludeIdsArray = is_array($excludeIds) ? $excludeIds : explode(',', $excludeIds);
            $query->whereNotIn('id', $excludeIdsArray);
        }

        return $query->paginate($perPage);
    }

    public function formatCollection(Collection $collection): Collection
    {
        return $collection;
    }

    public function getTopMusics(int $limit = 5, string $sortDirection = 'desc'): Collection
    {
        return Music::query()
            ->orderBy('views', $sortDirection)
            ->take($limit)
            ->get();
    }

    public function getMusicWithFormattedViews(Music $music): Music
    {
        return $music;
    }

    public function getVideoInfoFromYouTube(string $youtubeId): array
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

    public function refreshVideoData(Music $music): Music
    {
        try {
            $videoInfo = $this->getVideoInfoFromYouTube($music->youtube_id);

            $formattedTitle = $this->formatMusicTitle($videoInfo['title']);

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

    public function createMusic(array $data): Music
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
                $videoInfo = $this->getVideoInfoFromYouTube($data['youtube_id']);
                Log::info('Informações obtidas do YouTube:', $videoInfo);

                $formattedTitle = $this->formatMusicTitle($videoInfo['title']);

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
            $data['title'] = $this->formatMusicTitle($data['title']);
        }

        Log::info('Criando novo registro de música com dados processados:', $data);
        return Music::create($data);
    }

    public function updateMusic(Music $music, array $data): Music
    {
        if (isset($data['youtube_id']) && $data['youtube_id'] !== $music->youtube_id) {
            try {
                $videoInfo = $this->getVideoInfoFromYouTube($data['youtube_id']);

                $formattedTitle = $this->formatMusicTitle($videoInfo['title']);

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
            $data['title'] = $this->formatMusicTitle($data['title']);
        }

        $music->update($data);
        return $music;
    }

    public function deleteMusic(Music $music): bool
    {
        try {
            return $music->delete();
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function validateCreateRequest(Request $request): array
    {
        if ($request->has('youtube_id') && !$request->has('title') && !$request->has('views') && !$request->has('thumbnail')) {
            $data = $request->validate([
                'youtube_id' => 'required|string',
            ]);

            return $data;
        }

        return $request->validate([
            'title' => 'required|string|max:255',
            'youtube_id' => 'required|string',
            'views' => 'required|integer|min:0',
            'likes' => 'nullable|integer|min:0',
            'thumbnail' => 'required|url',
        ], [
            'title.required' => 'O título é obrigatório.',
            'youtube_id.required' => 'O ID do YouTube é obrigatório.',
            'views.required' => 'O campo visualizações é obrigatório.',
            'thumbnail.required' => 'A URL da thumbnail é obrigatória.',
            'thumbnail.url' => 'A URL da thumbnail deve ser válida.',
        ]);
    }

    public function validateUpdateRequest(Request $request, Music $music): array
    {
        return $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'youtube_id' => "sometimes|required|string|unique:music,youtube_id,{$music->id}",
            'views' => 'sometimes|required|integer|min:0',
            'likes' => 'sometimes|nullable|integer|min:0',
            'thumbnail' => 'sometimes|required|url',
        ]);
    }

    public function createMusicFromSuggestion(Suggestion $suggestion): ?Music
    {
        try {
            $youtubeId = $suggestion->youtube_id;
            $videoInfo = $this->getVideoInfoFromYouTube($youtubeId);

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
