<?php

namespace App\Services\Suggestions;

use App\Models\Music;
use App\Models\Suggestion;
use Illuminate\Http\Request;

class ProcessSuggestionService
{
    protected ExtractVideoIdService $extractVideoIdService;
    protected GetVideoInfoService $getVideoInfoService;

    public function __construct(
        ExtractVideoIdService $extractVideoIdService,
        GetVideoInfoService $getVideoInfoService
    ) {
        $this->extractVideoIdService = $extractVideoIdService;
        $this->getVideoInfoService = $getVideoInfoService;
    }

    public function execute(Request $request): array
    {
        $url = $request->url;
        $youtube_id = $this->extractVideoIdService->execute($url);

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

        $existingMusic = Music::where('youtube_id', $youtube_id)->first();
        if ($existingMusic) {
            return [
                'success' => false,
                'message' => 'Este vídeo já existe na biblioteca de músicas',
                'status_code' => 422
            ];
        }

        try {
            $videoInfo = $this->getVideoInfoService->execute($youtube_id);

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
}
