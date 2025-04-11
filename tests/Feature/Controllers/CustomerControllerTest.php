<?php

namespace Tests\Feature\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Reward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers()
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'loyalty_points',
                        'membership_tier'
                    ]
                ]
            ]);
    }

    public function test_can_create_customer()
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08012345678',
            'address' => '123 Main St',
            'birth_date' => '1990-01-01'
        ];

        $response = $this->postJson('/api/v1/customers', $customerData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Customer created successfully'
            ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);
    }

    public function test_can_add_points_to_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson("/api/v1/customers/{$customer->id}/points", [
            'points' => 100,
            'source' => 'purchase',
            'description' => 'Test purchase'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Points added successfully'
            ]);

        $this->assertEquals(100, $customer->fresh()->loyalty_points);
    }

    public function test_can_redeem_points()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 100]);

        $response = $this->postJson("/api/v1/customers/{$customer->id}/points/redeem", [
            'points' => 50,
            'description' => 'Test redemption'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Points redeemed successfully'
            ]);

        $this->assertEquals(50, $customer->fresh()->loyalty_points);
    }

    public function test_cannot_redeem_more_points_than_available()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 50]);

        $response = $this->postJson("/api/v1/customers/{$customer->id}/points/redeem", [
            'points' => 100,
            'description' => 'Test redemption'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient points'
            ]);

        $this->assertEquals(50, $customer->fresh()->loyalty_points);
    }

    public function test_can_get_loyalty_history()
    {
        $customer = Customer::factory()->create();
        LoyaltyTransaction::factory()->count(3)->create(['customer_id' => $customer->id]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/points/history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'points',
                        'type',
                        'source',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_can_create_reward()
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson("/api/v1/customers/{$customer->id}/rewards", [
            'name' => '10% Discount',
            'description' => 'Get 10% off your next purchase',
            'points_cost' => 500,
            'expires_at' => '2024-12-31'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Reward created successfully'
            ]);

        $this->assertDatabaseHas('rewards', [
            'customer_id' => $customer->id,
            'name' => '10% Discount'
        ]);
    }

    public function test_can_redeem_reward()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 1000]);
        $reward = Reward::factory()->create([
            'customer_id' => $customer->id,
            'points_cost' => 500,
            'status' => 'available'
        ]);

        $response = $this->postJson("/api/v1/customers/{$customer->id}/rewards/{$reward->id}/redeem");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reward redeemed successfully'
            ]);

        $this->assertEquals('redeemed', $reward->fresh()->status);
        $this->assertEquals(500, $customer->fresh()->loyalty_points);
    }

    public function test_cannot_redeem_expired_reward()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 1000]);
        $reward = Reward::factory()->create([
            'customer_id' => $customer->id,
            'points_cost' => 500,
            'status' => 'available',
            'expires_at' => now()->subDay()
        ]);

        $response = $this->postJson("/api/v1/customers/{$customer->id}/rewards/{$reward->id}/redeem");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Reward has expired'
            ]);
    }
} 