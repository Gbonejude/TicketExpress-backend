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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('order_number', 10)->nullable()->unique();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, cancelled, refunded
            $table->string('payment_method')->nullable();
            $table->string('delivery_method')->default('email'); // email, whatsapp, both
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable()->comment('Date et heure de confirmation');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('email');
            $table->index('phone');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
