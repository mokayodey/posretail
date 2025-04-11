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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('terminal_id')->after('payment_method')->nullable();
            $table->decimal('change_amount', 10, 2)->after('amount')->default(0);
            $table->string('receipt_number')->after('reference')->nullable();
            $table->string('transaction_code')->after('receipt_number')->nullable();
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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn([
                'terminal_id',
                'change_amount',
                'receipt_number',
                'transaction_code',
                'is_void',
                'voided_at',
                'voided_by'
            ]);
        });
    }
}; 