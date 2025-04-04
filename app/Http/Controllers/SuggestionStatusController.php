<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\SuggestionService;

class SuggestionStatusController extends Controller
{
    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }


    public function __invoke(Request $request, Suggestion $suggestion, string $status): JsonResponse
    {
        if (!in_array($status, [Suggestion::STATUS_APPROVED, Suggestion::STATUS_REJECTED])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status inválido',
            ], 422);
        }

        if ($status === Suggestion::STATUS_REJECTED) {
            $request->validate([
                'motivo' => 'required|string|max:500',
            ], [
                'motivo.required' => 'Um motivo deve ser fornecido ao rejeitar uma sugestão',
            ]);
        } else {
            $request->validate([
                'motivo' => 'nullable|string|max:500',
            ]);
        }

        $result = $this->suggestionService->updateStatus($request, $suggestion, $status);

        return response()->json(
            [
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
            ],
            $result['status_code']
        );
    }
}
