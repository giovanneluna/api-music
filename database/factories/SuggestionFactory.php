<?php

namespace Database\Factories;

use App\Models\Music;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class SuggestionFactory extends Factory
{

    public function definition(): array
    {
        static $usedIds = [];
        do {
            $youtubeId = fake()->regexify('[a-zA-Z0-9_-]{11}');
        } while (in_array($youtubeId, $usedIds) || Music::where('youtube_id', $youtubeId)->exists());

        $usedIds[] = $youtubeId;

        return [
            'url' => "https://www.youtube.com/watch?v={$youtubeId}",
            'youtube_id' => $youtubeId,
            'title' => fake()->sentence(4),
            'status' => Suggestion::STATUS_PENDING,
            'reason' => null,
            'user_id' => User::factory(),
            'music_id' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $music = Music::factory()->realYoutubeIds()->create();

            return [
                'status' => Suggestion::STATUS_APPROVED,
                'reason' => 'Aprovada automaticamente pelo sistema',
                'music_id' => $music->id,
            ];
        });
    }


    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Suggestion::STATUS_REJECTED,
                'reason' => 'Não se encaixa na categoria do site',
            ];
        });
    }

    public function forMusic(Music $music): static
    {
        return $this->state(function (array $attributes) use ($music) {
            return [
                'youtube_id' => $music->youtube_id,
                'url' => "https://www.youtube.com/watch?v={$music->youtube_id}",
                'title' => $music->title,
            ];
        });
    }
}
