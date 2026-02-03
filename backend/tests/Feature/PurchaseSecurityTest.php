<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameKey;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_own_purchases()
    {
        $userA = User::factory()->create(['role' => 'user']);
        $userB = User::factory()->create(['role' => 'user']);

        $game = Game::factory()->create();
        
        $keyA = GameKey::factory()->create([
            'game_id' => $game->id,
            'seller_id' => $userB->id,
            'price' => 10,
            'state' => 'vendida'
        ]);

        $purchaseA = Purchase::create([
            'user_id' => $userA->id,
            'key_id' => $keyA->id,
            'pay_method' => 'card',
            'total' => 10,
            'tax' => 1,
            'state' => 'completado'
        ]);

        $keyB = GameKey::factory()->create([
            'game_id' => $game->id,
            'seller_id' => $userA->id, // just valid user
            'price' => 20,
            'state' => 'vendida'
        ]);

        $purchaseB = Purchase::create([
            'user_id' => $userB->id,
            'key_id' => $keyB->id,
            'pay_method' => 'card',
            'total' => 20,
            'tax' => 2,
            'state' => 'completado'
        ]);

        // User A should see Purchase A but NOT Purchase B
        $response = $this->actingAs($userA)->getJson('/api/purchases');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $purchaseA->id]);
        $response->assertJsonMissing(['id' => $purchaseB->id]);
    }

    public function test_admin_can_see_all_purchases()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

         $game = Game::factory()->create();

        $keyA = GameKey::factory()->create([
            'game_id' => $game->id,
            'seller_id' => $userB->id,
            'price' => 10,
            'state' => 'vendida'
        ]);

         $purchaseA = Purchase::create([
            'user_id' => $userA->id,
            'key_id' => $keyA->id,
            'pay_method' => 'card',
            'total' => 10,
            'tax' => 1,
            'state' => 'completado'
        ]);
        
        $keyB = GameKey::factory()->create([
            'game_id' => $game->id,
            'seller_id' => $userA->id, 
            'price' => 10,
            'state' => 'vendida'
        ]);

        $purchaseB = Purchase::create([
            'user_id' => $userB->id,
            'key_id' => $keyB->id,
            'pay_method' => 'card',
            'total' => 20,
            'tax' => 2,
            'state' => 'completado'
        ]);

        $response = $this->actingAs($admin)->getJson('/api/purchases');

        $response->assertStatus(200);
        $response->assertJsonCount(2); 
    }

    public function test_user_cannot_view_others_purchase_details()
    {
        $userA = User::factory()->create(['role' => 'user']);
        $userB = User::factory()->create(['role' => 'user']);
        
        $game = Game::factory()->create();
        
        $key = GameKey::factory()->create([
            'game_id' => $game->id,
            'seller_id' => $userA->id,
            'price' => 20,
            'state' => 'vendida'
        ]);

        $purchaseB = Purchase::create([
            'user_id' => $userB->id,
            'key_id' => $key->id,
            'pay_method' => 'card',
            'total' => 20,
            'tax' => 2,
            'state' => 'completado'
        ]);

        // User A tries to view User B's purchase
        $response = $this->actingAs($userA)->getJson("/api/purchases/{$purchaseB->id}");
        $response->assertStatus(403);
    }
}
