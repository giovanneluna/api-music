<?php

namespace Tests\Feature;

use App\Models\Music;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuggestionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private string $baseEndpoint = '/api/suggestions';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    /**
     * Testa a listagem de sugestões com base no papel do usuário
     */
    public function test_listing_suggestions(): void
    {
        Suggestion::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        Suggestion::factory()->count(3)->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->getJson($this->baseEndpoint)
            ->assertStatus(401);

        Sanctum::actingAs($this->user);
        $this->getJson($this->baseEndpoint)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data.data');

        Sanctum::actingAs($this->admin);
        $this->getJson($this->baseEndpoint)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(5, 'data.data');
    }

    /**
     * Testa a criação de sugestões por usuários normais
     */
    public function test_user_can_create_suggestion(): void
    {
        $suggestionData = [
            'url' => 'https://www.youtube.com/watch?v=test123',
        ];

        $this->postJson($this->baseEndpoint, $suggestionData)
            ->assertStatus(401);

        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('processSuggestion')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Sugestão enviada com sucesso',
                    'data' => Suggestion::factory()->make([
                        'youtube_id' => 'test123',
                        'title' => 'Test Video',
                        'status' => 'pending',
                    ]),
                    'status_code' => 201
                ]);
        });

        Sanctum::actingAs($this->user);
        $this->postJson($this->baseEndpoint, $suggestionData)
            ->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Sugestão enviada com sucesso');
    }

    /**
     * Testa que administradores não podem criar sugestões
     */
    public function test_admin_cannot_create_suggestion(): void
    {
        $suggestionData = [
            'url' => 'https://www.youtube.com/watch?v=test123',
        ];

        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('processSuggestion')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'Administradores devem adicionar músicas diretamente',
                    'status_code' => 422
                ]);
        });

        Sanctum::actingAs($this->admin);
        $this->postJson($this->baseEndpoint, $suggestionData)
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Administradores devem adicionar músicas diretamente');
    }

    /**
     * Testa a visualização de sugestões com base no papel do usuário
     */
    public function test_viewing_suggestions(): void
    {
        $userSuggestion = Suggestion::factory()->create(['user_id' => $this->user->id]);
        $otherUserSuggestion = Suggestion::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->getJson("{$this->baseEndpoint}/{$userSuggestion->id}")
            ->assertStatus(401);

        Sanctum::actingAs($this->user);
        $this->getJson("{$this->baseEndpoint}/{$userSuggestion->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $userSuggestion->id);

        $this->getJson("{$this->baseEndpoint}/{$otherUserSuggestion->id}")
            ->assertStatus(403);

        Sanctum::actingAs($this->admin);
        $this->getJson("{$this->baseEndpoint}/{$otherUserSuggestion->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $otherUserSuggestion->id);
    }

    /**
     * Testa a exclusão de sugestões com base no papel do usuário e status da sugestão
     */
    public function test_deleting_suggestions(): void
    {
        $pendingSuggestion = Suggestion::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $approvedSuggestion = Suggestion::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
        ]);

        $otherUserSuggestion = Suggestion::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        $this->deleteJson("{$this->baseEndpoint}/{$pendingSuggestion->id}")
            ->assertStatus(401);

        Sanctum::actingAs($this->user);

        $this->deleteJson("{$this->baseEndpoint}/{$pendingSuggestion->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Sugestão excluída com sucesso');

        $this->assertDatabaseMissing('suggestions', [
            'id' => $pendingSuggestion->id,
        ]);

        $this->deleteJson("{$this->baseEndpoint}/{$approvedSuggestion->id}")
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->deleteJson("{$this->baseEndpoint}/{$otherUserSuggestion->id}")
            ->assertStatus(403);

        Sanctum::actingAs($this->admin);
        $this->deleteJson("{$this->baseEndpoint}/{$otherUserSuggestion->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Sugestão excluída com sucesso');

        $this->assertDatabaseMissing('suggestions', [
            'id' => $otherUserSuggestion->id,
        ]);
    }

    /**
     * Testa o formato de resposta da API
     */
    public function test_api_response_format(): void
    {
        $suggestion = Suggestion::factory()->create(['user_id' => $this->user->id]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson($this->baseEndpoint);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'youtube_id']
                    ]
                ]
            ]);

        $this->getJson("{$this->baseEndpoint}/{$suggestion->id}")
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'user_id',
                    'status'
                ]
            ]);
    }

    /**
     * Testa a validação de URL do YouTube
     */
    public function test_youtube_url_validation(): void
    {
        Sanctum::actingAs($this->user);

        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('processSuggestion')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Sugestão enviada com sucesso',
                    'data' => Suggestion::factory()->make([
                        'youtube_id' => 'test123',
                        'status' => 'pending',
                    ]),
                    'status_code' => 201
                ]);
        });

        $this->postJson($this->baseEndpoint, ['url' => 'https://www.youtube.com/watch?v=test123'])
            ->assertStatus(201)
            ->assertJsonPath('status', 'success');
    }

    /**
     * Testa a funcionalidade de obter informações de vídeo
     */
    public function test_get_video_info(): void
    {
        Sanctum::actingAs($this->user);

        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('extractVideoId')
                ->once()
                ->with('https://www.youtube.com/watch?v=test123')
                ->andReturn('test123');

            $mock->shouldReceive('getVideoInfo')
                ->once()
                ->with('test123')
                ->andReturn([
                    'titulo' => 'Test Video',
                    'visualizacoes' => 1000,
                    'youtube_id' => 'test123',
                    'thumb' => 'https://example.com/thumb.jpg',
                ]);
        });

        $this->postJson('/api/youtube/info', ['youtube_url' => 'https://www.youtube.com/watch?v=test123'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.titulo', 'Test Video');
    }
}
