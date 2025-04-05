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
    }

    public function down(): void
    {
        Schema::dropIfExists('music');
    }
};
