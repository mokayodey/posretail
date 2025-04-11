<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('branch_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('cashier_id')->after('branch_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->after('cashier_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->decimal('tax_rate', 5, 2)->after('discount_value')->default(0);
            $table->decimal('tax_amount', 10, 2)->after('tax_rate')->default(0);
            $table->string('location')->after('tax_amount')->nullable();
            $table->text('notes')->after('location')->nullable();
            $table->string('transaction_code')->after('notes')->nullable();
            $table->boolean('is_void')->after('transaction_code')->default(false);
            $table->timestamp('voided_at')->after('is_void')->nullable();
            $table->foreignId('voided_by')->after('voided_at')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['cashier_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['voided_by']);
            $table->dropColumn([
                'branch_id',
                'cashier_id',
                'customer_id',
                'tax_rate',
                'tax_amount',
                'location',
                'notes',
                'transaction_code',
                'is_void',
                'voided_at',
                'voided_by'
            ]);
        });
    }
}; 