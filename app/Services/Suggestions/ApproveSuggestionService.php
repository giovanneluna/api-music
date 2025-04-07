<?php

namespace App\Services\Suggestions;

use App\Models\Music;
use App\Models\Suggestion;
use Illuminate\Http\Request;

class ApproveSuggestionService
{
    protected GetVideoInfoService $getVideoInfoService;

    public function __construct(GetVideoInfoService $getVideoInfoService)
    {
        $this->getVideoInfoService = $getVideoInfoService;
    }

    public function execute(Request $request, Suggestion $suggestion): array
    {
        try {
            if (!$suggestion->music_id) {
                $music = Music::where('youtube_id', $suggestion->youtube_id)->first();

                if (!$music) {
                    $videoInfo = $this->getVideoInfoService->execute($suggestion->youtube_id);

                    $music = Music::create([
                        'title' => $videoInfo['titulo'],
                        'views' => $videoInfo['visualizacoes'],
                        'likes' => $videoInfo['likes'],
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
}
