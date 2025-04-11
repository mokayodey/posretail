<?php

namespace Tests\Unit\Models;

use App\Models\LoyaltyTier;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_loyalty_tier()
    {
        $tier = LoyaltyTier::factory()->create([
            'name' => 'Gold',
            'points_required' => 5000,
            'discount_percentage' => 15,
            'status' => 'active'
        ]);

        $this->assertInstanceOf(LoyaltyTier::class, $tier);
        $this->assertEquals('Gold', $tier->name);
        $this->assertEquals(5000, $tier->points_required);
        $this->assertEquals(15, $tier->discount_percentage);
    }

    public function test_can_get_active_tiers()
    {
        LoyaltyTier::factory()->count(3)->create(['status' => 'active']);
        LoyaltyTier::factory()->count(2)->create(['status' => 'inactive']);

        $activeTiers = LoyaltyTier::active()->get();

        $this->assertEquals(3, $activeTiers->count());
    }

    public function test_can_get_tier_by_points()
    {
        $bronzeTier = LoyaltyTier::factory()->create([
            'name' => 'Bronze',
            'points_required' => 0
        ]);
        
        $silverTier = LoyaltyTier::factory()->create([
            'name' => 'Silver',
            'points_required' => 1000
        ]);
        
        $goldTier = LoyaltyTier::factory()->create([
            'name' => 'Gold',
            'points_required' => 5000
        ]);

        $this->assertEquals('Bronze', LoyaltyTier::getTierByPoints(500)->name);
        $this->assertEquals('Silver', LoyaltyTier::getTierByPoints(2000)->name);
        $this->assertEquals('Gold', LoyaltyTier::getTierByPoints(6000)->name);
    }

    public function test_can_check_if_tier_is_achievable()
    {
        $tier = LoyaltyTier::factory()->create([
            'points_required' => 5000,
            'status' => 'active'
        ]);

        $customerWithEnoughPoints = Customer::factory()->create([
            'loyalty_points' => 6000
        ]);

        $customerWithInsufficientPoints = Customer::factory()->create([
            'loyalty_points' => 4000
        ]);

        $this->assertTrue($tier->isAchievableBy($customerWithEnoughPoints));
        $this->assertFalse($tier->isAchievableBy($customerWithInsufficientPoints));
    }

    public function test_can_get_next_tier()
    {
        $bronzeTier = LoyaltyTier::factory()->create([
            'name' => 'Bronze',
            'points_required' => 0
        ]);
        
        $silverTier = LoyaltyTier::factory()->create([
            'name' => 'Silver',
            'points_required' => 1000
        ]);
        
        $goldTier = LoyaltyTier::factory()->create([
            'name' => 'Gold',
            'points_required' => 5000
        ]);

        $this->assertEquals('Silver', $bronzeTier->getNextTier()->name);
        $this->assertEquals('Gold', $silverTier->getNextTier()->name);
        $this->assertNull($goldTier->getNextTier());
    }

    public function test_can_calculate_points_needed_for_next_tier()
    {
        $bronzeTier = LoyaltyTier::factory()->create([
            'name' => 'Bronze',
            'points_required' => 0
        ]);
        
        $silverTier = LoyaltyTier::factory()->create([
            'name' => 'Silver',
            'points_required' => 1000
        ]);

        $customer = Customer::factory()->create(['loyalty_points' => 500]);

        $this->assertEquals(500, $bronzeTier->pointsNeededForNextTier($customer));
    }
} 