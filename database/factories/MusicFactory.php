<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Music>
 */
class MusicFactory extends Factory
{

    protected array $realYoutubeIds = [
        's9kVG2ZaTS4', // O Mineiro e o Italiano
        'lpGGNA6_920', // Pagode em Brasília
        'FxXXvPL3JIg', // Rio de Lágrimas
        'tRQ2PWlCcZk', // Tristeza do Jeca
        '4Nb89GFu2g4', // Terra Roxa
    ];


    public function definition(): array
    {

        static $usedIds = [];
        do {
            $youtubeId = fake()->regexify('[a-zA-Z0-9_-]{11}');
        } while (in_array($youtubeId, $usedIds));

        $usedIds[] = $youtubeId;

        return [
            'title' => fake()->sentence(4),
            'views' => fake()->numberBetween(1000, 2000000),
            'likes' => fake()->numberBetween(100, 100000),
            'youtube_id' => $youtubeId,
            'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function realYoutubeIds(): static
    {
        return $this->state(function () {
            $youtubeId = fake()->randomElement($this->realYoutubeIds);

            return [
                'youtube_id' => $youtubeId,
                'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
            ];
        });
    }

    public function withViews(int $viewCount): static
    {
        return $this->state(function (array $attributes) use ($viewCount) {
            return [
                'views' => $viewCount,
            ];
        });
    }

    public function withLikes(int $likeCount): static
    {
        return $this->state(function (array $attributes) use ($likeCount) {
            return [
                'likes' => $likeCount,
            ];
        });
    }
}
