<?php

namespace App\Services\Suggestions;

use App\Models\Suggestion;
use Illuminate\Http\Request;

class UpdateSuggestionStatusService
{
    protected ApproveSuggestionService $approveSuggestionService;
    protected RejectSuggestionService $rejectSuggestionService;

    public function __construct(
        ApproveSuggestionService $approveSuggestionService,
        RejectSuggestionService $rejectSuggestionService
    ) {
        $this->approveSuggestionService = $approveSuggestionService;
        $this->rejectSuggestionService = $rejectSuggestionService;
    }

    public function execute(Request $request, Suggestion $suggestion, string $status): array
    {
        if (!$request->user()->is_admin) {
            return [
                'success' => false,
                'message' => 'Não autorizado',
                'status_code' => 403
            ];
        }

        if ($suggestion->status !== Suggestion::STATUS_PENDING && $suggestion->status === $status) {
            return [
                'success' => false,
                'message' => 'Sugestão já foi processada com este status',
                'status_code' => 422
            ];
        }

        if ($status === Suggestion::STATUS_APPROVED) {
            return $this->approveSuggestionService->execute($request, $suggestion);
        } else if ($status === Suggestion::STATUS_REJECTED) {
            return $this->rejectSuggestionService->execute($request, $suggestion);
        } else {
            return [
                'success' => false,
                'message' => 'Status inválido',
                'status_code' => 422
            ];
        }
    }
}
