<?php

namespace App\Http\Controllers;

use App\Http\Requests\Suggestion\UpdateStatusRequest;
use App\Models\Suggestion;
use App\Services\SuggestionService;
use Illuminate\Http\JsonResponse;

class SuggestionStatusController extends Controller
{
    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    public function __invoke(UpdateStatusRequest $request, Suggestion $suggestion, string $status): JsonResponse
    {
        if (!in_array($status, [Suggestion::STATUS_APPROVED, Suggestion::STATUS_REJECTED])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status inválido',
            ], 422);
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
