<?php

namespace Tests\Feature;

use App\Models\Npc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NpcApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_npcs_with_pagination(): void
    {
        Npc::create([
            'name' => 'Geralt',
            'role' => 'Witcher',
            'description' => 'Monster hunter',
        ]);

        $response = $this->getJson('/api/npcs');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'current_page',
                     'last_page',
                     'total',
                 ]);
    }

    public function test_can_search_npcs_by_name_or_role(): void
    {
        Npc::create(['name' => 'Eldrin', 'role' => 'Mage']);
        Npc::create(['name' => 'Boris', 'role' => 'Blacksmith']);

        $response = $this->getJson('/api/npcs?search=Eldrin');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Eldrin', $response->json('data.0.name'));
    }

    public function test_can_create_npc_with_image(): void
    {
        Storage::fake('public');

        // Menggunakan create() dengan mime type image/jpeg agar tidak membutuhkan ekstensi GD
        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/npcs', [
            'name'        => 'Thorin',
            'role'        => 'Warrior',
            'description' => 'King under the mountain',
            'image'       => $file,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('npcs', [
            'name' => 'Thorin',
            'role' => 'Warrior',
        ]);

        // Pastikan file tersimpan di storage/app/public/npcs
        $npc = Npc::first();
        $this->assertNotNull($npc->image);
        Storage::disk('public')->assertExists($npc->image);
    }
}
