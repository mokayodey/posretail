<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Reward;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_customer()
    {
        $customer = Customer::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'loyalty_points' => 0,
            'membership_tier' => 'bronze'
        ]);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('john@example.com', $customer->email);
        $this->assertEquals(0, $customer->loyalty_points);
        $this->assertEquals('bronze', $customer->membership_tier);
    }

    public function test_can_add_points()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 0]);
        
        $customer->addPoints(100, 'purchase', 'Test purchase');
        
        $this->assertEquals(100, $customer->fresh()->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', [
            'customer_id' => $customer->id,
            'points' => 100,
            'type' => 'earn',
            'source' => 'purchase'
        ]);
    }

    public function test_can_redeem_points()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 100]);
        
        $success = $customer->redeemPoints(50, 'Test redemption');
        
        $this->assertTrue($success);
        $this->assertEquals(50, $customer->fresh()->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', [
            'customer_id' => $customer->id,
            'points' => 50,
            'type' => 'redeem'
        ]);
    }

    public function test_cannot_redeem_more_points_than_available()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 50]);
        
        $success = $customer->redeemPoints(100, 'Test redemption');
        
        $this->assertFalse($success);
        $this->assertEquals(50, $customer->fresh()->loyalty_points);
    }

    public function test_membership_tier_updates_automatically()
    {
        $customer = Customer::factory()->create([
            'loyalty_points' => 0,
            'membership_tier' => 'bronze'
        ]);
        
        $customer->addPoints(1500, 'purchase');
        
        $this->assertEquals('silver', $customer->fresh()->membership_tier);
    }

    public function test_can_get_discount_rate()
    {
        $customer = Customer::factory()->create(['membership_tier' => 'gold']);
        
        $this->assertEquals(0.15, $customer->getDiscountRate());
    }

    public function test_can_get_birthday_reward()
    {
        $customer = Customer::factory()->create(['membership_tier' => 'platinum']);
        
        $this->assertEquals(1000, $customer->getBirthdayReward());
    }

    public function test_has_relationships()
    {
        $customer = Customer::factory()->create();
        
        $this->assertInstanceOf(LoyaltyTransaction::class, $customer->loyaltyTransactions()->make());
        $this->assertInstanceOf(Reward::class, $customer->rewards()->make());
        $this->assertInstanceOf(Sale::class, $customer->sales()->make());
    }
} 