<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Services\MusicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    public function __construct(
        protected MusicService $musicService
    ) {
    }

    private function formatViews(int $number): string|int
    {
        if ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 1) . 'M';
        }

        if ($number >= 1_000) {
            return number_format($number / 1_000, 1) . 'K';
        }

        return $number;
    }

    public function index(): JsonResponse
    {
        $music = $this->musicService->getTopMusics();

        return response()->json([
            'status' => 'success',
            'data' => $music,
        ]);
    }

    public function show(Music $music): JsonResponse
    {
        $music = $this->musicService->getMusicWithFormattedViews($music);

        return response()->json([
            'status' => 'success',
            'data' => $music,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->musicService->validateCreateRequest($request);

        $music = $this->musicService->createMusic($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Música adicionada com sucesso',
            'data' => $music,
        ], 201);
    }

    public function update(Request $request, Music $music): JsonResponse
    {
        $validated = $this->musicService->validateUpdateRequest($request, $music);

        $music = $this->musicService->updateMusic($music, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Música atualizada com sucesso',
            'data' => $music,
        ]);
    }

    public function destroy(Music $music): JsonResponse
    {
        $this->musicService->deleteMusic($music);

        return response()->json([
            'status' => 'success',
            'message' => 'Música excluída com sucesso',
        ]);
    }
}
