<?php

namespace Tests\Unit\Models;

use App\Models\LoyaltyRedemption;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyRedemptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_loyalty_redemption()
    {
        $customer = Customer::factory()->create();
        $reward = LoyaltyReward::factory()->create();
        $branch = Branch::factory()->create();

        $redemption = LoyaltyRedemption::factory()->create([
            'customer_id' => $customer->id,
            'reward_id' => $reward->id,
            'branch_id' => $branch->id,
            'points_used' => 500,
            'status' => 'completed'
        ]);

        $this->assertInstanceOf(LoyaltyRedemption::class, $redemption);
        $this->assertEquals(500, $redemption->points_used);
        $this->assertEquals('completed', $redemption->status);
    }

    public function test_redemption_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $redemption = LoyaltyRedemption::factory()->create([
            'customer_id' => $customer->id
        ]);

        $this->assertInstanceOf(Customer::class, $redemption->customer);
        $this->assertEquals($customer->id, $redemption->customer->id);
    }

    public function test_redemption_belongs_to_reward()
    {
        $reward = LoyaltyReward::factory()->create();
        $redemption = LoyaltyRedemption::factory()->create([
            'reward_id' => $reward->id
        ]);

        $this->assertInstanceOf(LoyaltyReward::class, $redemption->reward);
        $this->assertEquals($reward->id, $redemption->reward->id);
    }

    public function test_redemption_belongs_to_branch()
    {
        $branch = Branch::factory()->create();
        $redemption = LoyaltyRedemption::factory()->create([
            'branch_id' => $branch->id
        ]);

        $this->assertInstanceOf(Branch::class, $redemption->branch);
        $this->assertEquals($branch->id, $redemption->branch->id);
    }

    public function test_can_get_redemptions_by_status()
    {
        LoyaltyRedemption::factory()->count(3)->create(['status' => 'completed']);
        LoyaltyRedemption::factory()->count(2)->create(['status' => 'pending']);

        $completedRedemptions = LoyaltyRedemption::byStatus('completed')->get();
        $pendingRedemptions = LoyaltyRedemption::byStatus('pending')->get();

        $this->assertEquals(3, $completedRedemptions->count());
        $this->assertEquals(2, $pendingRedemptions->count());
    }

    public function test_can_mark_redemption_as_completed()
    {
        $redemption = LoyaltyRedemption::factory()->create([
            'status' => 'pending'
        ]);

        $redemption->markAsCompleted();

        $this->assertEquals('completed', $redemption->fresh()->status);
    }

    public function test_can_mark_redemption_as_cancelled()
    {
        $redemption = LoyaltyRedemption::factory()->create([
            'status' => 'pending'
        ]);

        $redemption->markAsCancelled();

        $this->assertEquals('cancelled', $redemption->fresh()->status);
    }

    public function test_can_get_redemptions_by_date_range()
    {
        LoyaltyRedemption::factory()->count(2)->create([
            'redemption_date' => now()->subDays(5)
        ]);
        LoyaltyRedemption::factory()->count(3)->create([
            'redemption_date' => now()->subDays(15)
        ]);

        $recentRedemptions = LoyaltyRedemption::whereBetween('redemption_date', [
            now()->subDays(7),
            now()
        ])->get();

        $this->assertEquals(2, $recentRedemptions->count());
    }

    public function test_can_get_customer_redemptions()
    {
        $customer = Customer::factory()->create();
        LoyaltyRedemption::factory()->count(3)->create([
            'customer_id' => $customer->id
        ]);

        $customerRedemptions = LoyaltyRedemption::forCustomer($customer->id)->get();

        $this->assertEquals(3, $customerRedemptions->count());
    }
} 