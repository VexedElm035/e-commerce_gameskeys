<?php

namespace Tests\Feature;

use App\Models\Support;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $response = $this->actingAs($buyer)->postJson('/api/support', [
            'user_id_seller' => $seller->id,
            'issue' => 'Refund needed',
            'description' => 'Key not working'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('support', [
            'user_id_buyer' => $buyer->id,
            'user_id_seller' => $seller->id,
            'state' => 'abierto'
        ]);
    }

    public function test_user_cannot_view_others_tickets()
    {
        $userA = User::factory()->create(['role' => 'user']);
        $userB = User::factory()->create(['role' => 'user']);
        $seller = User::factory()->create();

        $ticket = Support::create([
            'user_id_buyer' => $userA->id,
            'user_id_seller' => $seller->id,
            'issue' => 'Private Issue',
            'description' => 'Details',
            'state' => 'abierto'
        ]);

        $response = $this->actingAs($userB)->getJson("/api/support/{$ticket->id}");
        $response->assertStatus(403);
    }
}
