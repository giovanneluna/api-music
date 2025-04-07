<?php

namespace App\Services\Suggestions;

class ExtractVideoIdService
{
    private const YOUTUBE_PATTERNS = [
        '/youtube\.com\/watch\?v=([^&]+)/',
        '/youtu\.be\/([^?]+)/',
        '/youtube\.com\/embed\/([^?]+)/',
    ];

    public function execute(string $url): ?string
    {
        foreach (self::YOUTUBE_PATTERNS as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
