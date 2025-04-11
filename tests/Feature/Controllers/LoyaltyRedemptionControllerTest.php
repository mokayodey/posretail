<?php

namespace Tests\Feature\Controllers;

use App\Models\LoyaltyRedemption;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyRedemptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_redemptions()
    {
        LoyaltyRedemption::factory()->count(3)->create();

        $response = $this->getJson('/api/loyalty/redemptions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'customer_id',
                        'reward_id',
                        'branch_id',
                        'points_used',
                        'status'
                    ]
                ]
            ]);
    }

    public function test_can_create_redemption()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 1000]);
        $reward = LoyaltyReward::factory()->create(['points_cost' => 500]);
        $branch = Branch::factory()->create();

        $redemptionData = [
            'customer_id' => $customer->id,
            'reward_id' => $reward->id,
            'branch_id' => $branch->id,
            'points_used' => 500
        ];

        $response = $this->postJson('/api/loyalty/redemptions', $redemptionData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'points_used' => 500,
                    'status' => 'completed'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_redemptions', [
            'customer_id' => $customer->id,
            'reward_id' => $reward->id,
            'points_used' => 500
        ]);
    }

    public function test_cannot_redeem_with_insufficient_points()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 100]);
        $reward = LoyaltyReward::factory()->create(['points_cost' => 500]);
        $branch = Branch::factory()->create();

        $redemptionData = [
            'customer_id' => $customer->id,
            'reward_id' => $reward->id,
            'branch_id' => $branch->id,
            'points_used' => 500
        ];

        $response = $this->postJson('/api/loyalty/redemptions', $redemptionData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['points_used']);
    }

    public function test_can_get_redemptions_by_status()
    {
        LoyaltyRedemption::factory()->count(3)->create(['status' => 'completed']);
        LoyaltyRedemption::factory()->count(2)->create(['status' => 'pending']);

        $response = $this->getJson('/api/loyalty/redemptions?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_customer_redemptions()
    {
        $customer = Customer::factory()->create();
        LoyaltyRedemption::factory()->count(3)->create([
            'customer_id' => $customer->id
        ]);

        $response = $this->getJson("/api/loyalty/customers/{$customer->id}/redemptions");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_mark_redemption_as_completed()
    {
        $redemption = LoyaltyRedemption::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/loyalty/redemptions/{$redemption->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'completed'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_redemptions', [
            'id' => $redemption->id,
            'status' => 'completed'
        ]);
    }

    public function test_can_mark_redemption_as_cancelled()
    {
        $redemption = LoyaltyRedemption::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/loyalty/redemptions/{$redemption->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'cancelled'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_redemptions', [
            'id' => $redemption->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_can_get_redemptions_by_date_range()
    {
        LoyaltyRedemption::factory()->count(2)->create([
            'redemption_date' => now()->subDays(5)
        ]);
        LoyaltyRedemption::factory()->count(3)->create([
            'redemption_date' => now()->subDays(15)
        ]);

        $response = $this->getJson('/api/loyalty/redemptions?start_date=' . now()->subDays(7)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_get_branch_redemptions()
    {
        $branch = Branch::factory()->create();
        LoyaltyRedemption::factory()->count(3)->create([
            'branch_id' => $branch->id
        ]);

        $response = $this->getJson("/api/loyalty/branches/{$branch->id}/redemptions");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
} 