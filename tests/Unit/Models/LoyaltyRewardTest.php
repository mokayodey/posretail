<?php

namespace Tests\Unit\Models;

use App\Models\LoyaltyReward;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyRewardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_loyalty_reward()
    {
        $reward = LoyaltyReward::factory()->create([
            'name' => 'Birthday Discount',
            'points_cost' => 500,
            'description' => 'Special birthday reward',
            'status' => 'active'
        ]);

        $this->assertInstanceOf(LoyaltyReward::class, $reward);
        $this->assertEquals('Birthday Discount', $reward->name);
        $this->assertEquals(500, $reward->points_cost);
        $this->assertEquals('active', $reward->status);
    }

    public function test_reward_can_be_activated()
    {
        $reward = LoyaltyReward::factory()->create(['status' => 'inactive']);
        
        $reward->activate();
        
        $this->assertEquals('active', $reward->fresh()->status);
    }

    public function test_reward_can_be_deactivated()
    {
        $reward = LoyaltyReward::factory()->create(['status' => 'active']);
        
        $reward->deactivate();
        
        $this->assertEquals('inactive', $reward->fresh()->status);
    }

    public function test_can_check_if_reward_is_expired()
    {
        $expiredReward = LoyaltyReward::factory()->create([
            'expiry_date' => now()->subDay()
        ]);

        $activeReward = LoyaltyReward::factory()->create([
            'expiry_date' => now()->addDay()
        ]);

        $this->assertTrue($expiredReward->isExpired());
        $this->assertFalse($activeReward->isExpired());
    }

    public function test_can_check_if_reward_is_available()
    {
        $availableReward = LoyaltyReward::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDay()
        ]);

        $inactiveReward = LoyaltyReward::factory()->create([
            'status' => 'inactive',
            'expiry_date' => now()->addDay()
        ]);

        $expiredReward = LoyaltyReward::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->subDay()
        ]);

        $this->assertTrue($availableReward->isAvailable());
        $this->assertFalse($inactiveReward->isAvailable());
        $this->assertFalse($expiredReward->isAvailable());
    }

    public function test_can_get_active_rewards()
    {
        LoyaltyReward::factory()->count(3)->create(['status' => 'active']);
        LoyaltyReward::factory()->count(2)->create(['status' => 'inactive']);

        $activeRewards = LoyaltyReward::active()->get();

        $this->assertEquals(3, $activeRewards->count());
    }
} 