<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->timestamp('last_purchase_at')->nullable();
            $table->enum('membership_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->date('birth_date')->nullable();
            $table->date('anniversary_date')->nullable();
            $table->json('preferences')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
}; 