<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'service_type' => 'beat',
                'title' => 'New beat task',
                'details' => 'Please craft a fresh beat',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'New beat task');
        $response->assertJsonPath('data.service_type', 'beat');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'service_type' => 'beat',
            'title' => 'New beat task',
            'is_paid' => false,
            'status' => false,
        ]);

        $response->assertJsonPath('data.is_paid', false);
    }
}
