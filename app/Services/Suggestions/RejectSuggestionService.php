<?php

namespace App\Services\Suggestions;

use App\Models\Suggestion;
use Illuminate\Http\Request;

class RejectSuggestionService
{
    public function execute(Request $request, Suggestion $suggestion): array
    {
        $suggestion->status = Suggestion::STATUS_REJECTED;
        $suggestion->reason = $request->motivo;
        $suggestion->save();

        return [
            'success' => true,
            'message' => 'Sugestão rejeitada com sucesso',
            'data' => $suggestion,
            'status_code' => 200
        ];
    }
}
