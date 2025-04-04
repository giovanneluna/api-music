<?php

namespace Database\Seeders;

use App\Models\Music;
use Illuminate\Database\Seeder;

class MusicSeeder extends Seeder
{
    public function run(): void
    {
        $musics = [
            [
                'title' => 'Audioslave - Like a Stone (Official Video)',
                'views' => 1334514385,
                'youtube_id' => '7QU1nvuxaMA',
                'thumbnail' => 'https://img.youtube.com/vi/7QU1nvuxaMA/hqdefault.jpg',
            ],
            [
                'title' => 'New Divide (Official Music Video) [4K Upgrade] - Linkin Park',
                'views' => 641000000,
                'youtube_id' => 'ysSxxIqKNN0',
                'thumbnail' => 'https://img.youtube.com/vi/ysSxxIqKNN0/hqdefault.jpg',
            ],
            [
                'title' => 'Numb (Official Music Video) [4K UPGRADE] – Linkin Park',
                'views' => 2473389715,
                'youtube_id' => 'kXYiU_JCYtU',
                'thumbnail' => 'https://img.youtube.com/vi/kXYiU_JCYtU/hqdefault.jpg',
            ],
        ];

        foreach ($musics as $music) {
            Music::create($music);
        }
    }
}
