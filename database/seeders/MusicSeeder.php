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
                'title' => 'O Mineiro e o Italiano',
                'views' => 5200000,
                'youtube_id' => 's9kVG2ZaTS4',
                'thumbnail' => 'https://img.youtube.com/vi/s9kVG2ZaTS4/hqdefault.jpg'
            ],
            [
                'title' => 'Pagode em Brasília',
                'views' => 5000000,
                'youtube_id' => 'lpGGNA6_920',
                'thumbnail' => 'https://img.youtube.com/vi/lpGGNA6_920/hqdefault.jpg'
            ],
            [
                'title' => 'Rio de Lágrimas',
                'views' => 153000,
                'youtube_id' => 'FxXXvPL3JIg',
                'thumbnail' => 'https://img.youtube.com/vi/FxXXvPL3JIg/hqdefault.jpg'
            ],
            [
                'title' => 'Tristeza do Jeca',
                'views' => 154000,
                'youtube_id' => 'tRQ2PWlCcZk',
                'thumbnail' => 'https://img.youtube.com/vi/tRQ2PWlCcZk/hqdefault.jpg'
            ],
            [
                'title' => 'Terra roxa',
                'views' => 3300000,
                'youtube_id' => '4Nb89GFu2g4',
                'thumbnail' => 'https://img.youtube.com/vi/4Nb89GFu2g4/hqdefault.jpg'
            ],
            [
                'title' => 'Rei do Gado',
                'views' => 7100000,
                'youtube_id' => 'bv3593lmltY',
                'thumbnail' => 'https://img.youtube.com/vi/bv3593lmltY/hqdefault.jpg',
            ],
            [
                'title' => 'Pagode em Brasilia',
                'views' => 580000,
                'youtube_id' => 'xOtXyql4CyA',
                'thumbnail' => 'https://img.youtube.com/vi/xOtXyql4CyA/hqdefault.jpg',
            ],
            [
                'title' => 'Rei sem coroa',
                'views' => 193000,
                'youtube_id' => 'zVyPKMaaRyc',
                'thumbnail' => 'https://img.youtube.com/vi/zVyPKMaaRyc/hqdefault.jpg',
            ],
            [
                'title' => 'Boiada Cuiabana',
                'views' => 1200000,
                'youtube_id' => 's_pvnDB_xmw',
                'thumbnail' => 'https://img.youtube.com/vi/s_pvnDB_xmw/hqdefault.jpg',
            ],

            [
                'title' => 'Mineiro de Monte Belo',
                'views' => 716000,
                'youtube_id' => '_NVfHTg8NW0',
                'thumbnail' => 'https://img.youtube.com/vi/_NVfHTg8NW0/hqdefault.jpg',
            ],
            [
                'title' => 'A Coisa Tá Feia',
                'views' => 306000,
                'youtube_id' => 'kYyZByIaElE',
                'thumbnail' => 'https://img.youtube.com/vi/kYyZByIaElE/hqdefault.jpg',
            ],
        ];

        foreach ($musics as $music) {
            Music::create($music);
        }
    }
}
