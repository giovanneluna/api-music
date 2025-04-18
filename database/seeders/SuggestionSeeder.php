<?php

namespace Database\Seeders;

use App\Models\Suggestion;
use App\Models\User;
use App\Models\Music;
use Illuminate\Database\Seeder;

class SuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        $music = Music::create([
            'title' => 'Boi Soberano',
            'views' => 47000000,
            'likes' => 350000,
            'youtube_id' => 'lkQaLTnmNFw',
            'thumbnail' => 'https://img.youtube.com/vi/lkQaLTnmNFw/hqdefault.jpg',
        ]);

        Suggestion::create([
            'url' => 'https://www.youtube.com/watch?v=lkQaLTnmNFw',
            'youtube_id' => 'lkQaLTnmNFw',
            'title' => 'Boi Soberano',
            'status' => Suggestion::STATUS_APPROVED,
            'reason' => 'Ótima música, aprovada!',
            'user_id' => $users->first()->id,
            'music_id' => $music->id,
        ]);

        Suggestion::create([
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ',
            'title' => 'Rick Astley - Never Gonna Give You Up',
            'status' => Suggestion::STATUS_REJECTED,
            'reason' => 'Não se enquadra no estilo musical do site.',
            'user_id' => $users->skip(1)->first()->id,
        ]);

        Suggestion::create([
            'url' => 'https://www.youtube.com/watch?v=qfjbe4RKbwQ',
            'youtube_id' => 'qfjbe4RKbwQ',
            'title' => 'Ludacris - Act A Fool (Official Music Video)',
            'status' => Suggestion::STATUS_PENDING,
            'user_id' => $users->skip(2)->first()->id,
        ]);
    }
}
