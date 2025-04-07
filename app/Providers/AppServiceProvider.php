<?php

namespace App\Providers;

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
use App\Services\MusicService;
use App\Services\Musics\RefreshVideoDataService;
use App\Services\Musics\UpdateMusicService;
use App\Services\Musics\YouTubeApiService;
use App\Services\SuggestionService;
use App\Services\Suggestions\ApproveSuggestionService;
use App\Services\Suggestions\ExtractVideoIdService;
use App\Services\Suggestions\GetVideoInfoService;
use App\Services\Suggestions\ProcessSuggestionService;
use App\Services\Suggestions\RejectSuggestionService;
use App\Services\Suggestions\UpdateSuggestionStatusService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(FormatViewsService::class, function ($app) {
            return new FormatViewsService();
        });

        $this->app->singleton(FormatLikesService::class, function ($app) {
            return new FormatLikesService();
        });

        $this->app->singleton(FormatMusicTitleService::class, function ($app) {
            return new FormatMusicTitleService();
        });

        $this->app->singleton(YouTubeApiService::class, function ($app) {
            return new YouTubeApiService();
        });

        $this->app->singleton(GetPaginatedMusicService::class, function ($app) {
            return new GetPaginatedMusicService();
        });

        $this->app->singleton(FormatCollectionService::class, function ($app) {
            return new FormatCollectionService();
        });

        $this->app->singleton(GetTopMusicsService::class, function ($app) {
            return new GetTopMusicsService();
        });

        $this->app->singleton(GetMusicWithFormattedViewsService::class, function ($app) {
            return new GetMusicWithFormattedViewsService();
        });

        $this->app->singleton(RefreshVideoDataService::class, function ($app) {
            return new RefreshVideoDataService(
                $app->make(YouTubeApiService::class),
                $app->make(FormatMusicTitleService::class)
            );
        });

        $this->app->singleton(CreateMusicService::class, function ($app) {
            return new CreateMusicService(
                $app->make(YouTubeApiService::class),
                $app->make(FormatMusicTitleService::class)
            );
        });

        $this->app->singleton(UpdateMusicService::class, function ($app) {
            return new UpdateMusicService(
                $app->make(YouTubeApiService::class),
                $app->make(FormatMusicTitleService::class)
            );
        });

        $this->app->singleton(DeleteMusicService::class, function ($app) {
            return new DeleteMusicService();
        });

        $this->app->singleton(CreateMusicFromSuggestionService::class, function ($app) {
            return new CreateMusicFromSuggestionService(
                $app->make(YouTubeApiService::class)
            );
        });

        $this->app->singleton(MusicService::class, function ($app) {
            return new MusicService(
                $app->make(GetPaginatedMusicService::class),
                $app->make(FormatCollectionService::class),
                $app->make(GetTopMusicsService::class),
                $app->make(GetMusicWithFormattedViewsService::class),
                $app->make(RefreshVideoDataService::class),
                $app->make(CreateMusicService::class),
                $app->make(UpdateMusicService::class),
                $app->make(DeleteMusicService::class),
                $app->make(CreateMusicFromSuggestionService::class),
                $app->make(FormatViewsService::class),
                $app->make(FormatLikesService::class),
                $app->make(FormatMusicTitleService::class),
                $app->make(YouTubeApiService::class)
            );
        });

        $this->app->singleton(ExtractVideoIdService::class, function ($app) {
            return new ExtractVideoIdService();
        });

        $this->app->singleton(GetVideoInfoService::class, function ($app) {
            return new GetVideoInfoService();
        });

        $this->app->singleton(RejectSuggestionService::class, function ($app) {
            return new RejectSuggestionService();
        });

        $this->app->singleton(ApproveSuggestionService::class, function ($app) {
            return new ApproveSuggestionService(
                $app->make(GetVideoInfoService::class)
            );
        });

        $this->app->singleton(UpdateSuggestionStatusService::class, function ($app) {
            return new UpdateSuggestionStatusService(
                $app->make(ApproveSuggestionService::class),
                $app->make(RejectSuggestionService::class)
            );
        });

        $this->app->singleton(ProcessSuggestionService::class, function ($app) {
            return new ProcessSuggestionService(
                $app->make(ExtractVideoIdService::class),
                $app->make(GetVideoInfoService::class)
            );
        });

        $this->app->singleton(SuggestionService::class, function ($app) {
            return new SuggestionService(
                $app->make(ProcessSuggestionService::class),
                $app->make(UpdateSuggestionStatusService::class),
                $app->make(ExtractVideoIdService::class),
                $app->make(GetVideoInfoService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
