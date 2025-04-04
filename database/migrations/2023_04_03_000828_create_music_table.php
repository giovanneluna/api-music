<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Título da música');
            $table->bigInteger('views')->default(0)->comment('Número de visualizações no YouTube');
            $table->string('youtube_id')->unique()->comment('ID do vídeo no YouTube');
            $table->string('thumbnail')->comment('URL da miniatura do vídeo');
            $table->timestamps();
        });

        DB::table('music')->insert([
            [
                'title' => 'O Mineiro e o Italiano',
                'views' => 5200000,
                'youtube_id' => 's9kVG2ZaTS4',
                'thumbnail' => 'https://img.youtube.com/vi/s9kVG2ZaTS4/hqdefault.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Pagode em Brasília',
                'views' => 5000000,
                'youtube_id' => 'lpGGNA6_920',
                'thumbnail' => 'https://img.youtube.com/vi/lpGGNA6_920/hqdefault.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Rio de Lágrimas',
                'views' => 153000,
                'youtube_id' => 'FxXXvPL3JIg',
                'thumbnail' => 'https://img.youtube.com/vi/FxXXvPL3JIg/hqdefault.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Tristeza do Jeca',
                'views' => 154000,
                'youtube_id' => 'tRQ2PWlCcZk',
                'thumbnail' => 'https://img.youtube.com/vi/tRQ2PWlCcZk/hqdefault.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Terra roxa',
                'views' => 3300000,
                'youtube_id' => '4Nb89GFu2g4',
                'thumbnail' => 'https://img.youtube.com/vi/4Nb89GFu2g4/hqdefault.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('music');
    }
};
