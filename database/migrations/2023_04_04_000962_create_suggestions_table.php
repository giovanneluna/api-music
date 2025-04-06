<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('url')
                ->comment('URL completa do vídeo do YouTube');

            $table->string('youtube_id')
                ->index()
                ->comment('ID do vídeo extraído da URL');

            $table->string('title')
                ->nullable()
                ->comment('Título do vídeo obtido via scraping');

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->comment('Status atual da sugestão');

            $table->text('reason')
                ->nullable()
                ->comment('Motivo da aprovação ou rejeição');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Usuário que enviou a sugestão');

            $table->foreignId('music_id')
                ->nullable()
                ->constrained('music')
                ->onDelete('set null')
                ->comment('Música associada após aprovação');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'youtube_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
