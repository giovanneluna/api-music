<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Services\SuggestionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestionController extends Controller
{
    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }


    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 15);
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = Suggestion::with('user')->orderBy($sortBy, $sortDirection);

        if (!$user->admin) {
            $query->where('user_id', $user->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $suggestions = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $suggestions->items(),
                'meta' => [
                    'current_page' => $suggestions->currentPage(),
                    'last_page' => $suggestions->lastPage(),
                    'per_page' => $suggestions->perPage(),
                    'total' => $suggestions->total()
                ],
                'links' => [
                    'first' => $suggestions->url(1),
                    'last' => $suggestions->url($suggestions->lastPage()),
                    'next' => $suggestions->nextPageUrl(),
                    'prev' => $suggestions->previousPageUrl()
                ]
            ],
            'is_admin' => $user->admin
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $validation = $request->validate([
            'url' => 'required|url',
        ], [
            'url.required' => 'A URL do YouTube é obrigatória',
            'url.url' => 'Uma URL válida deve ser fornecida'
        ]);

        $result = $this->suggestionService->processSuggestion($request);

        return response()->json(
            [
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
            ],
            $result['status_code']
        );
    }


    public function show(Suggestion $suggestion): JsonResponse
    {
        $user = Auth::user();

        if ($user->admin || $suggestion->user_id === $user->id) {
            return response()->json([
                'status' => 'success',
                'data' => $suggestion->load('user'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Não autorizado a visualizar esta sugestão',
        ], 403);
    }


    public function destroy(Suggestion $suggestion): JsonResponse
    {
        $user = Auth::user();

        if ($user->admin || $suggestion->user_id === $user->id) {
            // Permite a exclusão independente do status
            // Usa soft delete para manter referências em Music, mas remove das listagens
            $suggestion->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Sugestão excluída com sucesso',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Não autorizado a excluir esta sugestão',
        ], 403);
    }

    public function getVideoInfo(Request $request): JsonResponse
    {
        $validation = $request->validate([
            'youtube_url' => 'required|url',
        ], [
            'youtube_url.required' => 'A URL do YouTube é obrigatória',
            'youtube_url.url' => 'Uma URL válida deve ser fornecida'
        ]);

        try {
            $youtubeId = $this->suggestionService->extractVideoId($request->youtube_url);

            if (!$youtubeId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'URL do YouTube inválida',
                ], 422);
            }

            $videoInfo = $this->suggestionService->getVideoInfo($youtubeId);

            // Garantir que o youtube_id está presente na resposta
            if (!isset($videoInfo['youtube_id'])) {
                $videoInfo['youtube_id'] = $youtubeId;
            }

            return response()->json([
                'status' => 'success',
                'data' => $videoInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
