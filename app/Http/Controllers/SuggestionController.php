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

        if ($user->admin) {
            $suggestions = Suggestion::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            $suggestions = Suggestion::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $suggestions,
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
            if ($suggestion->status === Suggestion::STATUS_PENDING) {
                $suggestion->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Sugestão excluída com sucesso',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Não é possível excluir uma sugestão que já foi processada',
            ], 422);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Não autorizado a excluir esta sugestão',
        ], 403);
    }
}
