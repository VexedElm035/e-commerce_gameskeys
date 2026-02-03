<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_their_inbox()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        // Message between A and B
        $messageAB = Message::create([
            'sender_id' => $userA->id,
            'receiver_id' => $userB->id,
            'subject' => 'Hello',
            'content' => 'Test content',
            'type' => 'text'
        ]);

        // Message between C and B
        $messageCB = Message::create([
            'sender_id' => $userC->id,
            'receiver_id' => $userB->id,
            'subject' => 'Hi',
            'content' => 'Other content',
            'type' => 'text'
        ]);

        // User A should only see messageAB
        $response = $this->actingAs($userA)->getJson('/api/messages');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $messageAB->id]);
        $response->assertJsonMissing(['id' => $messageCB->id]);
        
        // User C should only see messageCB
        $response = $this->actingAs($userC)->getJson('/api/messages');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $messageCB->id]);
        $response->assertJsonMissing(['id' => $messageAB->id]);

        // User B should see both
        $response = $this->actingAs($userB)->getJson('/api/messages');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $messageAB->id]);
        $response->assertJsonFragment(['id' => $messageCB->id]);
    }

    public function test_user_cannot_view_others_message_details()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        $message = Message::create([
            'sender_id' => $userA->id,
            'receiver_id' => $userB->id,
            'subject' => 'Private',
            'content' => 'Keep out',
            'type' => 'text'
        ]);

        // User C tries to view message between A and B
        $response = $this->actingAs($userC)->getJson("/api/messages/{$message->id}");
        $response->assertStatus(403);
    }
}
