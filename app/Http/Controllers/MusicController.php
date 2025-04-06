<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Services\MusicService;
use App\Http\Resources\MusicResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicController extends Controller
{
    public function __construct(
        protected MusicService $musicService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $musics = $this->musicService->getPaginatedMusic($request);

        return response()->json([
            'status' => 'success',
            'data' => MusicResource::collection($musics->items()),
            'meta' => [
                'current_page' => $musics->currentPage(),
                'last_page' => $musics->lastPage(),
                'per_page' => $musics->perPage(),
                'total' => $musics->total()
            ],
            'links' => [
                'first' => $musics->url(1),
                'last' => $musics->url($musics->lastPage()),
                'next' => $musics->nextPageUrl(),
                'prev' => $musics->previousPageUrl()
            ]
        ]);
    }

    public function show(Music $music): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new MusicResource($music),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->musicService->validateCreateRequest($request);

            if (isset($validated['youtube_id'])) {
                $existingSuggestion = \App\Models\Suggestion::where('youtube_id', $validated['youtube_id'])
                    ->where('status', \App\Models\Suggestion::STATUS_PENDING)
                    ->first();

                if ($existingSuggestion) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Erro de validação',
                        'errors' => [
                            'youtube_id' => ['Este vídeo já existe como sugestão pendente. Por favor, aprove a sugestão em vez de criar uma nova música.']
                        ]
                    ], 422);
                }
            }

            $music = $this->musicService->createMusic($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Música adicionada com sucesso',
                'data' => new MusicResource($music),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (strpos($message, 'já existe na base de dados') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erro de validação',
                    'errors' => [
                        'youtube_id' => ['Este vídeo já existe na base de dados.']
                    ]
                ], 422);
            }

            if (strpos($message, 'Erro ao obter informações do vídeo') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'errors' => [
                        'youtube_id' => [$message]
                    ]
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao adicionar música: ' . $message,
            ], 500);
        }
    }

    public function update(Request $request, Music $music): JsonResponse
    {
        try {
            $validated = $this->musicService->validateUpdateRequest($request, $music);

            $music = $this->musicService->updateMusic($music, $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Música atualizada com sucesso',
                'data' => new MusicResource($music),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar música: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Music $music): JsonResponse
    {
        $result = $this->musicService->deleteMusic($music);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao excluir a música',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Música excluída com sucesso',
        ]);
    }

    public function refresh(Music $music): JsonResponse
    {
        $updatedMusic = $this->musicService->refreshVideoData($music);

        return response()->json([
            'status' => 'success',
            'message' => 'Dados do vídeo atualizados com sucesso',
            'data' => new MusicResource($updatedMusic),
        ]);
    }
}
