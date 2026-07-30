<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
    }

    private function actingAsUser(): static
    {
        return $this->actingAs($this->user, config('api.auth_guard', 'sanctum'));
    }

    // ── Upload ────────────────────────────────────────────────────────────

    public function test_user_can_upload_a_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAsUser()->postJson('/api/v1/user/media', [
            'file'       => $file,
            'collection' => 'documents',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.original_name', 'document.pdf')
            ->assertJsonPath('data.collection', 'documents');

        $this->assertDatabaseHas('media', [
            'user_id'    => $this->user->id,
            'collection' => 'documents',
        ]);
    }

    public function test_upload_requires_a_file(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/user/media', [
            'collection' => 'documents',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_rejects_invalid_collection_name(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAsUser()->postJson('/api/v1/user/media', [
            'file'       => $file,
            'collection' => 'My Invalid Collection!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['collection']);
    }

    // ── Image thumbnails ──────────────────────────────────────────────────

    public function test_user_can_upload_an_image(): void
    {
        $image = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->actingAsUser()->postJson('/api/v1/user/media', [
            'file'       => $image,
            'collection' => 'avatars',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.is_image', true);
    }

    // ── List ──────────────────────────────────────────────────────────────

    public function test_user_can_list_media(): void
    {
        Media::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'disk'    => 'local',
        ]);

        $response = $this->actingAsUser()->getJson('/api/v1/user/media');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_can_filter_media_by_collection(): void
    {
        Media::factory()->count(2)->create(['user_id' => $this->user->id, 'collection' => 'avatars', 'disk' => 'local']);
        Media::factory()->count(4)->create(['user_id' => $this->user->id, 'collection' => 'documents', 'disk' => 'local']);

        $response = $this->actingAsUser()->getJson('/api/v1/user/media?collection=avatars');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    // ── URL generation ────────────────────────────────────────────────────

    public function test_user_can_get_signed_url_for_their_media(): void
    {
        $media = Media::factory()->create([
            'user_id' => $this->user->id,
            'disk'    => 'local',
            'path'    => 'media/1/documents/test.pdf',
        ]);

        // Store a fake file so Storage::url() works.
        Storage::disk('local')->put($media->path, 'content');

        $response = $this->actingAsUser()->getJson("/api/v1/user/media/{$media->id}/url");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['url', 'thumbnail', 'expires_in']]);
    }

    public function test_user_cannot_get_url_for_another_users_media(): void
    {
        $other = User::factory()->create();
        $media = Media::factory()->create(['user_id' => $other->id, 'disk' => 'local']);

        $response = $this->actingAsUser()->getJson("/api/v1/user/media/{$media->id}/url");

        $response->assertNotFound();
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function test_user_can_delete_their_media(): void
    {
        $media = Media::factory()->create([
            'user_id' => $this->user->id,
            'disk'    => 'local',
            'path'    => 'media/1/documents/test.pdf',
        ]);

        Storage::disk('local')->put($media->path, 'content');

        $response = $this->actingAsUser()->deleteJson("/api/v1/user/media/{$media->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    // ── Unauthenticated ───────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_media(): void
    {
        $this->getJson('/api/v1/user/media')->assertUnauthorized();
    }
}
