<?php

namespace App\Services;

use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MusicService
{
    public function formatViews(int $number): string|int
    {

        if ($number >= 1_000_000_000) {
            return number_format($number / 1_000_000_000, 1) . 'B';
        }

        if ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 1) . 'M';
        }

        if ($number >= 1_000) {
            return number_format($number / 1_000, 1) . 'K';
        }

        return $number;
    }

    public function getTopMusics(int $limit = 5): Collection
    {
        $music = Music::query()
            ->orderByDesc('views')
            ->take($limit)
            ->get();

        $music->each(function ($music) {
            $music->views_formatted = $this->formatViews($music->views);
        });

        return $music;
    }

    public function getMusicWithFormattedViews(Music $music): Music
    {
        $music->views_formatted = $this->formatViews($music->views);
        return $music;
    }

    public function createMusic(array $data): Music
    {
        return Music::create($data);
    }

    public function updateMusic(Music $music, array $data): Music
    {
        $music->update($data);
        $music->views_formatted = $this->formatViews($music->views);
        return $music;
    }

    public function deleteMusic(Music $music): bool
    {
        return $music->delete();
    }

    public function validateCreateRequest(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'youtube_id' => 'required|string|unique:music,youtube_id',
            'views' => 'required|integer|min:0',
            'thumbnail' => 'required|url',
        ], [
            'youtube_id.unique' => 'Este vídeo já existe na base de dados.',
        ]);
    }

    public function validateUpdateRequest(Request $request, Music $music): array
    {
        return $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'youtube_id' => "sometimes|required|string|unique:music,youtube_id,{$music->id}",
            'views' => 'sometimes|required|integer|min:0',
            'thumbnail' => 'sometimes|required|url',
        ]);
    }
}
