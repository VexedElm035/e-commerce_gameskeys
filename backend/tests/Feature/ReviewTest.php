<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_review()
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $game = Game::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'game_id' => $game->id,
            'seller_id' => $seller->id,
            'rate' => 5,
            'rate_ux' => 4,
            'rate_time' => 5,
            'commentary' => 'Great game!'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seller_id' => $seller->id,
            'rate' => 5
        ]);
    }

    public function test_user_cannot_update_others_review()
    {
        $userA = User::factory()->create(['role' => 'user']);
        $userB = User::factory()->create(['role' => 'user']);
        $seller = User::factory()->create();
        $game = Game::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $userA->id,
            'seller_id' => $seller->id,
            'game_id' => $game->id
        ]);

        $response = $this->actingAs($userB)->putJson("/api/reviews/{$review->id}", [
            'rate' => 1,
            'commentary' => 'Bad review'
        ]);

        $response->assertStatus(403);
    }
}
