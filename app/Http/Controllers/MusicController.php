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
        $validated = $this->musicService->validateCreateRequest($request);

        $music = $this->musicService->createMusic($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Música adicionada com sucesso',
            'data' => new MusicResource($music),
        ], 201);
    }

    public function update(Request $request, Music $music): JsonResponse
    {
        $validated = $this->musicService->validateUpdateRequest($request, $music);

        $music = $this->musicService->updateMusic($music, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Música atualizada com sucesso',
            'data' => new MusicResource($music),
        ]);
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
}
