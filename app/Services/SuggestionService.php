<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Services\Suggestions\ExtractVideoIdService;
use App\Services\Suggestions\GetVideoInfoService;
use App\Services\Suggestions\ProcessSuggestionService;
use App\Services\Suggestions\UpdateSuggestionStatusService;
use Illuminate\Http\Request;

class SuggestionService
{
    private ProcessSuggestionService $processSuggestionService;
    private UpdateSuggestionStatusService $updateSuggestionStatusService;
    private ExtractVideoIdService $extractVideoIdService;
    private GetVideoInfoService $getVideoInfoService;

    public function __construct(
        ProcessSuggestionService $processSuggestionService,
        UpdateSuggestionStatusService $updateSuggestionStatusService,
        ExtractVideoIdService $extractVideoIdService,
        GetVideoInfoService $getVideoInfoService
    ) {
        $this->processSuggestionService = $processSuggestionService;
        $this->updateSuggestionStatusService = $updateSuggestionStatusService;
        $this->extractVideoIdService = $extractVideoIdService;
        $this->getVideoInfoService = $getVideoInfoService;
    }

    public function processSuggestion(Request $request): array
    {
        return $this->processSuggestionService->execute($request);
    }

    public function updateStatus(Request $request, Suggestion $suggestion, string $status): array
    {
        return $this->updateSuggestionStatusService->execute($request, $suggestion, $status);
    }

    public function extractVideoId(string $url): ?string
    {
        return $this->extractVideoIdService->execute($url);
    }

    public function getVideoInfo(string $videoId): array
    {
        return $this->getVideoInfoService->execute($videoId);
    }
}
