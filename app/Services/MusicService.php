<?php

namespace App\Services;

use App\Models\Music;
use App\Models\Suggestion;
use App\Services\Musics\CreateMusicFromSuggestionService;
use App\Services\Musics\CreateMusicService;
use App\Services\Musics\DeleteMusicService;
use App\Services\Musics\FormatCollectionService;
use App\Services\Musics\FormatLikesService;
use App\Services\Musics\FormatMusicTitleService;
use App\Services\Musics\FormatViewsService;
use App\Services\Musics\GetMusicWithFormattedViewsService;
use App\Services\Musics\GetPaginatedMusicService;
use App\Services\Musics\GetTopMusicsService;
use App\Services\Musics\RefreshVideoDataService;
use App\Services\Musics\UpdateMusicService;
use App\Services\Musics\YouTubeApiService;
use App\Http\Requests\Music\CreateMusicRequest;
use App\Http\Requests\Music\UpdateMusicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MusicService
{
    private GetPaginatedMusicService $getPaginatedMusicService;
    private FormatCollectionService $formatCollectionService;
    private GetTopMusicsService $getTopMusicsService;
    private GetMusicWithFormattedViewsService $getMusicWithFormattedViewsService;
    private RefreshVideoDataService $refreshVideoDataService;
    private CreateMusicService $createMusicService;
    private UpdateMusicService $updateMusicService;
    private DeleteMusicService $deleteMusicService;
    private CreateMusicFromSuggestionService $createMusicFromSuggestionService;
    private FormatViewsService $formatViewsService;
    private FormatLikesService $formatLikesService;
    private FormatMusicTitleService $formatMusicTitleService;
    private YouTubeApiService $youtubeApiService;

    public function __construct(
        GetPaginatedMusicService $getPaginatedMusicService,
        FormatCollectionService $formatCollectionService,
        GetTopMusicsService $getTopMusicsService,
        GetMusicWithFormattedViewsService $getMusicWithFormattedViewsService,
        RefreshVideoDataService $refreshVideoDataService,
        CreateMusicService $createMusicService,
        UpdateMusicService $updateMusicService,
        DeleteMusicService $deleteMusicService,
        CreateMusicFromSuggestionService $createMusicFromSuggestionService,
        FormatViewsService $formatViewsService,
        FormatLikesService $formatLikesService,
        FormatMusicTitleService $formatMusicTitleService,
        YouTubeApiService $youtubeApiService
    ) {
        $this->getPaginatedMusicService = $getPaginatedMusicService;
        $this->formatCollectionService = $formatCollectionService;
        $this->getTopMusicsService = $getTopMusicsService;
        $this->getMusicWithFormattedViewsService = $getMusicWithFormattedViewsService;
        $this->refreshVideoDataService = $refreshVideoDataService;
        $this->createMusicService = $createMusicService;
        $this->updateMusicService = $updateMusicService;
        $this->deleteMusicService = $deleteMusicService;
        $this->createMusicFromSuggestionService = $createMusicFromSuggestionService;
        $this->formatViewsService = $formatViewsService;
        $this->formatLikesService = $formatLikesService;
        $this->formatMusicTitleService = $formatMusicTitleService;
        $this->youtubeApiService = $youtubeApiService;
    }

    public function formatViews(int $number): string
    {
        return $this->formatViewsService->format($number);
    }

    public function formatLikes(int $number): string
    {
        return $this->formatLikesService->format($number);
    }

    public function formatMusicTitle(string $title): string
    {
        return $this->formatMusicTitleService->format($title);
    }

    public function getPaginatedMusic(Request $request): LengthAwarePaginator
    {
        return $this->getPaginatedMusicService->execute($request);
    }

    public function formatCollection(Collection $collection): Collection
    {
        return $this->formatCollectionService->execute($collection);
    }

    public function getTopMusics(int $limit = 5, string $sortDirection = 'desc'): Collection
    {
        return $this->getTopMusicsService->execute($limit, $sortDirection);
    }

    public function getMusicWithFormattedViews(Music $music): Music
    {
        return $this->getMusicWithFormattedViewsService->execute($music);
    }

    public function getVideoInfoFromYouTube(string $youtubeId): array
    {
        return $this->youtubeApiService->getVideoInfo($youtubeId);
    }

    public function refreshVideoData(Music $music): Music
    {
        return $this->refreshVideoDataService->execute($music);
    }

    public function createMusic(array $data): Music
    {
        return $this->createMusicService->execute($data);
    }

    public function updateMusic(Music $music, array $data): Music
    {
        return $this->updateMusicService->execute($music, $data);
    }

    public function deleteMusic(Music $music): bool
    {
        return $this->deleteMusicService->execute($music);
    }

    public function validateCreateRequest(Request $request): array
    {
        $formRequest = new CreateMusicRequest();
        $formRequest->setContainer(app())->setRedirector(app()->make('redirect'));
        $formRequest->replace($request->all());
        $formRequest->validateResolved();

        return $formRequest->validated();
    }

    public function validateUpdateRequest(Request $request, Music $music): array
    {
        $formRequest = new UpdateMusicRequest();
        $formRequest->setContainer(app())->setRedirector(app()->make('redirect'));
        $formRequest->replace($request->all());

        $formRequest->route()->setParameter('music', $music);

        $formRequest->validateResolved();

        return $formRequest->validated();
    }

    public function createMusicFromSuggestion(Suggestion $suggestion): ?Music
    {
        return $this->createMusicFromSuggestionService->execute($suggestion);
    }
}
