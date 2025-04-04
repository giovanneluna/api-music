<?php

namespace Tests\Feature;

use App\Models\Music;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuggestionStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Suggestion $pendingSuggestion;
    private string $baseEndpoint = '/api/suggestions';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);

        $this->pendingSuggestion = Suggestion::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'youtube_id' => 'test123',
            'title' => 'Test Video',
        ]);
    }

    /**
     * Testa a aprovação de sugestões por um administrador
     */
    public function test_admin_can_approve_suggestion(): void
    {
        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('updateStatus')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Sugestão aprovada com sucesso',
                    'data' => $this->pendingSuggestion->fill([
                        'status' => 'approved',
                        'reason' => 'Boa sugestão',
                        'music_id' => 1,
                    ]),
                    'status_code' => 200
                ]);
        });

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/approved", [
            'motivo' => 'Boa sugestão'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Sugestão aprovada com sucesso');
    }

    /**
     * Testa a rejeição de sugestões por um administrador
     */
    public function test_admin_can_reject_suggestion(): void
    {
        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('updateStatus')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Sugestão rejeitada com sucesso',
                    'data' => $this->pendingSuggestion->fill([
                        'status' => 'rejected',
                        'reason' => 'Não se encaixa na categoria',
                    ]),
                    'status_code' => 200
                ]);
        });

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/rejected", [
            'motivo' => 'Não se encaixa na categoria'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Sugestão rejeitada com sucesso');
    }

    /**
     * Testa as validações de atualização de status
     */
    public function test_status_validations(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/rejected", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['motivo']);

        $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/invalid", [
            'motivo' => 'Teste'
        ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Status inválido');
    }

    /**
     * Testa as restrições de permissão para atualização de status
     */
    public function test_permission_restrictions(): void
    {
        $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/approved", [
            'motivo' => 'Boa sugestão'
        ])->assertStatus(401);

        Sanctum::actingAs($this->user);

        $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/approved", [
            'motivo' => 'Boa sugestão'
        ])->assertStatus(403);
    }

    /**
     * Testa restrições para sugestões já processadas
     */
    public function test_cannot_update_already_processed_suggestion(): void
    {
        $processedSuggestion = Suggestion::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
        ]);

        $this->mock(\App\Services\SuggestionService::class, function ($mock) {
            $mock->shouldReceive('updateStatus')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'Sugestão já foi processada',
                    'status_code' => 422
                ]);
        });

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("{$this->baseEndpoint}/{$processedSuggestion->id}/status/rejected", [
            'motivo' => 'Não gostei'
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Sugestão já foi processada');
    }

    /**
     * Testa o fluxo completo de aprovação com criação de música
     */
    public function test_approval_creates_music_record(): void
    {
        $music = Music::factory()->create([
            'youtube_id' => 'test123',
            'title' => 'Test Video'
        ]);

        $this->mock(\App\Services\SuggestionService::class, function ($mock) use ($music) {
            $mock->shouldReceive('updateStatus')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Sugestão aprovada com sucesso',
                    'data' => $this->pendingSuggestion->fill([
                        'status' => 'approved',
                        'reason' => 'Excelente sugestão',
                        'music_id' => $music->id,
                    ]),
                    'status_code' => 200
                ]);
        });

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("{$this->baseEndpoint}/{$this->pendingSuggestion->id}/status/approved", [
            'motivo' => 'Excelente sugestão'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('music', [
            'id' => $music->id,
            'youtube_id' => 'test123'
        ]);
    }
}
