<?php

namespace Tests\Feature\Controllers;

use App\Models\LoyaltyCard;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyCardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_cards()
    {
        LoyaltyCard::factory()->count(3)->create();

        $response = $this->getJson('/api/loyalty/cards');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'card_number',
                        'status',
                        'points_balance',
                        'tier_progress'
                    ]
                ]
            ]);
    }

    public function test_can_create_card()
    {
        $customer = Customer::factory()->create();

        $cardData = [
            'customer_id' => $customer->id,
            'card_number' => '1234567890',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/loyalty/cards', $cardData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'card_number' => '1234567890',
                    'status' => 'active'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'card_number' => '1234567890',
            'status' => 'active'
        ]);
    }

    public function test_cannot_create_duplicate_card_number()
    {
        LoyaltyCard::factory()->create(['card_number' => '1234567890']);

        $cardData = [
            'card_number' => '1234567890',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/loyalty/cards', $cardData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['card_number']);
    }

    public function test_can_activate_card()
    {
        $card = LoyaltyCard::factory()->create(['status' => 'inactive']);

        $response = $this->postJson("/api/loyalty/cards/{$card->id}/activate");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'active'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'id' => $card->id,
            'status' => 'active'
        ]);
    }

    public function test_can_block_card()
    {
        $card = LoyaltyCard::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/loyalty/cards/{$card->id}/block");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'blocked'
                ]
            ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'id' => $card->id,
            'status' => 'blocked'
        ]);
    }

    public function test_can_add_points_to_card()
    {
        $card = LoyaltyCard::factory()->create(['points_balance' => 100]);

        $response = $this->postJson("/api/loyalty/cards/{$card->id}/add-points", [
            'points' => 50
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'points_balance' => 150
                ]
            ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'id' => $card->id,
            'points_balance' => 150
        ]);
    }

    public function test_can_deduct_points_from_card()
    {
        $card = LoyaltyCard::factory()->create(['points_balance' => 100]);

        $response = $this->postJson("/api/loyalty/cards/{$card->id}/deduct-points", [
            'points' => 50
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'points_balance' => 50
                ]
            ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'id' => $card->id,
            'points_balance' => 50
        ]);
    }

    public function test_cannot_deduct_more_points_than_available()
    {
        $card = LoyaltyCard::factory()->create(['points_balance' => 50]);

        $response = $this->postJson("/api/loyalty/cards/{$card->id}/deduct-points", [
            'points' => 100
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['points']);
    }

    public function test_can_update_tier_progress()
    {
        $card = LoyaltyCard::factory()->create(['tier_progress' => 0]);

        $response = $this->postJson("/api/loyalty/cards/{$card->id}/update-tier-progress", [
            'progress' => 500
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'tier_progress' => 500
                ]
            ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'id' => $card->id,
            'tier_progress' => 500
        ]);
    }

    public function test_can_check_card_status()
    {
        $card = LoyaltyCard::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDay()
        ]);

        $response = $this->getJson("/api/loyalty/cards/{$card->id}/status");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_active' => true,
                    'is_expired' => false
                ]
            ]);
    }
} 