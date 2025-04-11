<?php

namespace Tests\Unit\Models;

use App\Models\LoyaltyTransaction;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_loyalty_transaction()
    {
        $customer = Customer::factory()->create();
        $transaction = LoyaltyTransaction::factory()->create([
            'customer_id' => $customer->id,
            'points' => 100,
            'type' => 'earn',
            'status' => 'completed',
            'description' => 'Purchase points'
        ]);

        $this->assertInstanceOf(LoyaltyTransaction::class, $transaction);
        $this->assertEquals(100, $transaction->points);
        $this->assertEquals('earn', $transaction->type);
        $this->assertEquals('completed', $transaction->status);
    }

    public function test_transaction_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $transaction = LoyaltyTransaction::factory()->create([
            'customer_id' => $customer->id
        ]);

        $this->assertInstanceOf(Customer::class, $transaction->customer);
        $this->assertEquals($customer->id, $transaction->customer->id);
    }

    public function test_can_get_transactions_by_type()
    {
        LoyaltyTransaction::factory()->count(3)->create(['type' => 'earn']);
        LoyaltyTransaction::factory()->count(2)->create(['type' => 'redeem']);

        $earnTransactions = LoyaltyTransaction::byType('earn')->get();
        $redeemTransactions = LoyaltyTransaction::byType('redeem')->get();

        $this->assertEquals(3, $earnTransactions->count());
        $this->assertEquals(2, $redeemTransactions->count());
    }

    public function test_can_get_transactions_by_status()
    {
        LoyaltyTransaction::factory()->count(3)->create(['status' => 'completed']);
        LoyaltyTransaction::factory()->count(2)->create(['status' => 'pending']);

        $completedTransactions = LoyaltyTransaction::byStatus('completed')->get();
        $pendingTransactions = LoyaltyTransaction::byStatus('pending')->get();

        $this->assertEquals(3, $completedTransactions->count());
        $this->assertEquals(2, $pendingTransactions->count());
    }

    public function test_can_get_transactions_by_date_range()
    {
        LoyaltyTransaction::factory()->count(2)->create([
            'created_at' => now()->subDays(5)
        ]);
        LoyaltyTransaction::factory()->count(3)->create([
            'created_at' => now()->subDays(15)
        ]);

        $recentTransactions = LoyaltyTransaction::whereBetween('created_at', [
            now()->subDays(7),
            now()
        ])->get();

        $this->assertEquals(2, $recentTransactions->count());
    }

    public function test_can_calculate_points_balance()
    {
        $customer = Customer::factory()->create();
        
        LoyaltyTransaction::factory()->create([
            'customer_id' => $customer->id,
            'points' => 100,
            'type' => 'earn',
            'status' => 'completed'
        ]);

        LoyaltyTransaction::factory()->create([
            'customer_id' => $customer->id,
            'points' => 50,
            'type' => 'redeem',
            'status' => 'completed'
        ]);

        $balance = LoyaltyTransaction::calculatePointsBalance($customer->id);
        
        $this->assertEquals(50, $balance);
    }

    public function test_can_mark_transaction_as_completed()
    {
        $transaction = LoyaltyTransaction::factory()->create([
            'status' => 'pending'
        ]);

        $transaction->markAsCompleted();

        $this->assertEquals('completed', $transaction->fresh()->status);
    }

    public function test_can_mark_transaction_as_cancelled()
    {
        $transaction = LoyaltyTransaction::factory()->create([
            'status' => 'pending'
        ]);

        $transaction->markAsCancelled();

        $this->assertEquals('cancelled', $transaction->fresh()->status);
    }
} 