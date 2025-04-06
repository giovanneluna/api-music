<?php

namespace Tests\Feature;

use App\Models\Music;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\MusicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MusicControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private string $baseEndpoint = '/api/musics';
    private User $admin;
    private User $user;
    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();

        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;
        $this->userToken = $this->user->createToken('user-token')->plainTextToken;
    }

    /**
     * List Music Tests
     */
    public function test_can_list_musics(): void
    {
        Music::query()->delete();
        Music::factory()->count(3)->create();

        $response = $this->getJson($this->baseEndpoint);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertNotEmpty($response->json('data'));
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Show Music Tests
     */
    public function test_can_show_music_details(): void
    {
        $music = Music::factory()->create([
            'views' => 5800,
            'likes' => 1200
        ]);

        $response = $this->getJson("{$this->baseEndpoint}/{$music->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $music->id)
            ->assertJsonPath('data.title', $music->title)
            ->assertJsonPath('data.youtube_id', $music->youtube_id)
            ->assertJsonPath('data.views_formatted', '5.8K')
            ->assertJsonPath('data.likes_formatted', '1.2K');
    }

    public function test_returns_404_for_nonexistent_music(): void
    {
        $nonExistentId = 9999;

        $response = $this->getJson("{$this->baseEndpoint}/{$nonExistentId}");

        $response->assertStatus(404);
    }

    /**
     * Authorization Tests
     */
    public function test_authorization_rules(): void
    {
        $music = Music::factory()->create();
        $youtubeId = $this->faker->regexify('[a-zA-Z0-9_-]{11}');

        $musicData = [
            'title' => 'Test Music',
            'youtube_id' => $youtubeId,
            'views' => 1000,
            'likes' => 500,
            'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
        ];

        $this->postJson($this->baseEndpoint, $musicData)->assertStatus(401);
        $this->patchJson("{$this->baseEndpoint}/{$music->id}", ['title' => 'Updated'])->assertStatus(401);
        $this->deleteJson("{$this->baseEndpoint}/{$music->id}")->assertStatus(401);
        $this->postJson("{$this->baseEndpoint}/{$music->id}/refresh")->assertStatus(401);

        Sanctum::actingAs($this->user);
        $this->postJson($this->baseEndpoint, $musicData)->assertStatus(403);
        $this->patchJson("{$this->baseEndpoint}/{$music->id}", ['title' => 'Updated'])->assertStatus(403);
        $this->deleteJson("{$this->baseEndpoint}/{$music->id}")->assertStatus(403);
        $this->postJson("{$this->baseEndpoint}/{$music->id}/refresh")->assertStatus(403);
    }

    /**
     * Create Music Tests
     */
    public function test_admin_can_create_music(): void
    {
        $youtubeId = $this->faker->regexify('[a-zA-Z0-9_-]{11}');

        $musicData = [
            'title' => 'Test Music',
            'youtube_id' => $youtubeId,
            'views' => 1000,
            'likes' => 500,
            'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
        ];

        Sanctum::actingAs($this->admin);

        $response = $this->postJson($this->baseEndpoint, $musicData);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Música adicionada com sucesso')
            ->assertJsonPath('data.title', $musicData['title']);

        $this->assertDatabaseHas('music', [
            'title' => $musicData['title'],
            'youtube_id' => $musicData['youtube_id'],
            'likes' => 500,
        ]);
    }

    public function test_admin_can_create_music_with_only_youtube_id(): void
    {
        $youtubeId = $this->faker->regexify('[a-zA-Z0-9_-]{11}');

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'snippet' => [
                            'title' => 'Test Music from API',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/high.jpg'],
                            ],
                        ],
                        'statistics' => [
                            'viewCount' => '5000',
                            'likeCount' => '1000',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson($this->baseEndpoint, [
            'youtube_id' => $youtubeId,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Música adicionada com sucesso')
            ->assertJsonPath('data.title', 'Test Music from API');

        $this->assertDatabaseHas('music', [
            'youtube_id' => $youtubeId,
            'title' => 'Test Music from API',
            'views' => 5000,
            'likes' => 1000,
        ]);
    }

    /**
     * Validation Tests
     */
    public function test_music_validation(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson($this->baseEndpoint, ['title' => 'Test Music']);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['youtube_id', 'views', 'thumbnail']);

        $invalidData = [
            'title' => str_repeat('A', 300),
            'views' => -50,
            'likes' => -10,
            'youtube_id' => $this->faker->regexify('[a-zA-Z0-9_-]{11}'),
            'thumbnail' => 'invalid-url',
        ];

        $response = $this->postJson($this->baseEndpoint, $invalidData);
        $response->assertStatus(422);

        $existingMusic = Music::factory()->create();
        $duplicateData = [
            'title' => 'Another Music',
            'youtube_id' => $existingMusic->youtube_id,
            'views' => 1000,
            'likes' => 500,
            'thumbnail' => 'https://example.com/image.jpg',
        ];

        $response = $this->postJson($this->baseEndpoint, $duplicateData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['youtube_id']);
    }

    /**
     * Update Music Tests
     */
    public function test_admin_can_update_music(): void
    {
        $music = Music::factory()->create();

        $updateData = [
            'title' => 'Updated Music Title',
            'views' => 5000,
            'likes' => 1200,
        ];

        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("{$this->baseEndpoint}/{$music->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Música atualizada com sucesso')
            ->assertJsonPath('data.title', 'Updated Music Title')
            ->assertJsonPath('data.views', 5000)
            ->assertJsonPath('data.likes', 1200);

        $this->assertDatabaseHas('music', [
            'id' => $music->id,
            'title' => 'Updated Music Title',
            'views' => 5000,
            'likes' => 1200,
        ]);
    }

    public function test_admin_can_update_music_youtube_id_with_api_data(): void
    {
        $music = Music::factory()->create();
        $newYoutubeId = $this->faker->regexify('[a-zA-Z0-9_-]{11}');

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'snippet' => [
                            'title' => 'Updated Music from API',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/updated-high.jpg'],
                            ],
                        ],
                        'statistics' => [
                            'viewCount' => '10000',
                            'likeCount' => '2000',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("{$this->baseEndpoint}/{$music->id}", [
            'youtube_id' => $newYoutubeId,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Música atualizada com sucesso')
            ->assertJsonPath('data.title', 'Updated Music from API')
            ->assertJsonPath('data.youtube_id', $newYoutubeId)
            ->assertJsonPath('data.views', 10000)
            ->assertJsonPath('data.likes', 2000);

        $this->assertDatabaseHas('music', [
            'id' => $music->id,
            'youtube_id' => $newYoutubeId,
            'title' => 'Updated Music from API',
            'views' => 10000,
            'likes' => 2000,
        ]);
    }

    /**
     * Delete Music Tests
     */
    public function test_admin_can_delete_music(): void
    {
        $music = Music::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("{$this->baseEndpoint}/{$music->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Música excluída com sucesso');

        $this->assertDatabaseMissing('music', [
            'id' => $music->id,
        ]);
    }

    /**
     * Refresh Music Tests
     */
    public function test_admin_can_refresh_music_data(): void
    {
        $music = Music::factory()->create([
            'title' => 'Old Title',
            'views' => 1000,
            'likes' => 500,
        ]);

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'snippet' => [
                            'title' => 'Fresh Title from API',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/fresh-thumbnail.jpg'],
                            ],
                        ],
                        'statistics' => [
                            'viewCount' => '25000',
                            'likeCount' => '3500',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("{$this->baseEndpoint}/{$music->id}/refresh");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Dados do vídeo atualizados com sucesso')
            ->assertJsonPath('data.title', 'Fresh Title from API')
            ->assertJsonPath('data.views', 25000)
            ->assertJsonPath('data.likes', 3500);

        $this->assertDatabaseHas('music', [
            'id' => $music->id,
            'title' => 'Fresh Title from API',
            'views' => 25000,
            'likes' => 3500,
        ]);
    }

    /**
     * Unit Tests for Services
     */
    public function test_view_formatting_directly(): void
    {
        $musicService = app(MusicService::class);

        $this->assertEquals(800, $musicService->formatViews(800));
        $this->assertEquals('5.8K', $musicService->formatViews(5800));
        $this->assertEquals('2.5M', $musicService->formatViews(2500000));
        $this->assertEquals('1.0B', $musicService->formatViews(1000000000));

        $this->assertEquals(999, $musicService->formatViews(999));
        $this->assertEquals('1.0K', $musicService->formatViews(1000));
        $this->assertEquals('999.9K', $musicService->formatViews(999900));
        $this->assertEquals('1.0M', $musicService->formatViews(1000000));
    }

    public function test_likes_formatting_directly(): void
    {
        $musicService = app(MusicService::class);

        $this->assertEquals(800, $musicService->formatLikes(800));
        $this->assertEquals('5.8K', $musicService->formatLikes(5800));
        $this->assertEquals('2.5M', $musicService->formatLikes(2500000));
        $this->assertEquals('1.0B', $musicService->formatLikes(1000000000));
    }

    public function test_youtube_api_integration(): void
    {
        $youtubeId = 'dQw4w9WgXcQ';

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'snippet' => [
                            'title' => 'Test Video Title',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/high.jpg'],
                                'medium' => ['url' => 'https://example.com/medium.jpg'],
                                'default' => ['url' => 'https://example.com/default.jpg'],
                            ],
                        ],
                        'statistics' => [
                            'viewCount' => '1000000',
                            'likeCount' => '50000',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $musicService = app(MusicService::class);
        $videoInfo = $musicService->getVideoInfoFromYouTube($youtubeId);

        $this->assertEquals('Test Video Title', $videoInfo['title']);
        $this->assertEquals(1000000, $videoInfo['views']);
        $this->assertEquals(50000, $videoInfo['likes']);
        $this->assertEquals($youtubeId, $videoInfo['youtube_id']);
        $this->assertEquals('https://example.com/high.jpg', $videoInfo['thumbnail']);
    }
}
