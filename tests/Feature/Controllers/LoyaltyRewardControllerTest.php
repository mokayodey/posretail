<?php

namespace Tests\Feature\Controllers;

use App\Models\LoyaltyReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyRewardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_rewards()
    {
        LoyaltyReward::factory()->count(3)->create();

        $response = $this->getJson('/api/loyalty/rewards');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'points_cost',
                        'description',
                        'status',
                        'expiry_date'
                    ]
                ]
            ]);
    }

    public function test_can_create_reward()
    {
        $rewardData = [
            'name' => 'Birthday Discount',
            'points_cost' => 500,
            'description' => 'Special birthday reward',
            'status' => 'active',
            'expiry_date' => now()->addMonth()
        ];

        $response = $this->postJson('/api/loyalty/rewards', $rewardData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Birthday Discount',
                    'points_cost' => 500,
                    'status' => 'active'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_rewards', [
            'name' => 'Birthday Discount',
            'points_cost' => 500
        ]);
    }

    public function test_can_update_reward()
    {
        $reward = LoyaltyReward::factory()->create();

        $updateData = [
            'name' => 'Updated Reward',
            'points_cost' => 1000,
            'status' => 'inactive'
        ];

        $response = $this->putJson("/api/loyalty/rewards/{$reward->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Reward',
                    'points_cost' => 1000,
                    'status' => 'inactive'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_rewards', [
            'id' => $reward->id,
            'name' => 'Updated Reward'
        ]);
    }

    public function test_can_delete_reward()
    {
        $reward = LoyaltyReward::factory()->create();

        $response = $this->deleteJson("/api/loyalty/rewards/{$reward->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('loyalty_rewards', ['id' => $reward->id]);
    }

    public function test_can_activate_reward()
    {
        $reward = LoyaltyReward::factory()->create(['status' => 'inactive']);

        $response = $this->postJson("/api/loyalty/rewards/{$reward->id}/activate");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'active'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_rewards', [
            'id' => $reward->id,
            'status' => 'active'
        ]);
    }

    public function test_can_deactivate_reward()
    {
        $reward = LoyaltyReward::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/loyalty/rewards/{$reward->id}/deactivate");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'inactive'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_rewards', [
            'id' => $reward->id,
            'status' => 'inactive'
        ]);
    }

    public function test_can_get_active_rewards()
    {
        LoyaltyReward::factory()->count(3)->create(['status' => 'active']);
        LoyaltyReward::factory()->count(2)->create(['status' => 'inactive']);

        $response = $this->getJson('/api/loyalty/rewards/active');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_expired_rewards()
    {
        LoyaltyReward::factory()->count(2)->create([
            'expiry_date' => now()->subDay()
        ]);
        LoyaltyReward::factory()->count(3)->create([
            'expiry_date' => now()->addDay()
        ]);

        $response = $this->getJson('/api/loyalty/rewards/expired');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
} 