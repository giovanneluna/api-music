<?php

namespace App\Http\Controllers;

use App\Models\Music;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    private function formatViews(int $number): string|int
    {
        if ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 1) . 'M';
        }

        if ($number >= 1_000) {
            return number_format($number / 1_000, 1) . 'K';
        }

        return $number;
    }

    public function index(): JsonResponse
    {
        $music = Music::query()
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $music->each(function($music) {
            $music->views_formatted = $this->formatViews($music->views);
        });

        return response()->json([
            'status' => 'success',
            'data' => $music,
        ]);
    }

    public function show(Music $music): JsonResponse
    {
        $music->views_formatted = $this->formatViews($music->views);

        return response()->json([
            'status' => 'success',
            'data' => $music,
        ]);
    }
}
