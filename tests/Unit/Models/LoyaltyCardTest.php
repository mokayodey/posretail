<?php

namespace Tests\Unit\Models;

use App\Models\LoyaltyCard;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_loyalty_card()
    {
        $card = LoyaltyCard::factory()->create([
            'card_number' => '1234567890',
            'status' => 'active',
            'points_balance' => 100,
            'tier_progress' => 500
        ]);

        $this->assertInstanceOf(LoyaltyCard::class, $card);
        $this->assertEquals('1234567890', $card->card_number);
        $this->assertEquals('active', $card->status);
        $this->assertEquals(100, $card->points_balance);
        $this->assertEquals(500, $card->tier_progress);
    }

    public function test_card_has_unique_number()
    {
        $card1 = LoyaltyCard::factory()->create(['card_number' => '1234567890']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        LoyaltyCard::factory()->create(['card_number' => '1234567890']);
    }

    public function test_can_activate_card()
    {
        $card = LoyaltyCard::factory()->create(['status' => 'inactive']);
        
        $card->activate();
        
        $this->assertEquals('active', $card->fresh()->status);
    }

    public function test_can_block_card()
    {
        $card = LoyaltyCard::factory()->create(['status' => 'active']);
        
        $card->block();
        
        $this->assertEquals('blocked', $card->fresh()->status);
    }

    public function test_can_check_if_card_is_active()
    {
        $activeCard = LoyaltyCard::factory()->create(['status' => 'active']);
        $inactiveCard = LoyaltyCard::factory()->create(['status' => 'inactive']);
        $blockedCard = LoyaltyCard::factory()->create(['status' => 'blocked']);

        $this->assertTrue($activeCard->isActive());
        $this->assertFalse($inactiveCard->isActive());
        $this->assertFalse($blockedCard->isActive());
    }

    public function test_can_add_points_to_card()
    {
        $card = LoyaltyCard::factory()->create(['points_balance' => 100]);
        
        $card->addPoints(50);
        
        $this->assertEquals(150, $card->fresh()->points_balance);
    }

    public function test_can_deduct_points_from_card()
    {
        $card = LoyaltyCard::factory()->create(['points_balance' => 100]);
        
        $success = $card->deductPoints(50);
        
        $this->assertTrue($success);
        $this->assertEquals(50, $card->fresh()->points_balance);
    }

    public function test_cannot_deduct_more_points_than_available()
    {
        $card = LoyaltyCard::factory()->create(['points_balance' => 50]);
        
        $success = $card->deductPoints(100);
        
        $this->assertFalse($success);
        $this->assertEquals(50, $card->fresh()->points_balance);
    }

    public function test_can_update_tier_progress()
    {
        $card = LoyaltyCard::factory()->create(['tier_progress' => 0]);
        
        $card->updateTierProgress(500);
        
        $this->assertEquals(500, $card->fresh()->tier_progress);
    }

    public function test_can_check_if_card_is_expired()
    {
        $expiredCard = LoyaltyCard::factory()->create([
            'expiry_date' => now()->subDay()
        ]);

        $validCard = LoyaltyCard::factory()->create([
            'expiry_date' => now()->addDay()
        ]);

        $this->assertTrue($expiredCard->isExpired());
        $this->assertFalse($validCard->isExpired());
    }
} 