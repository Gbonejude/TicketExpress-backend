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
        Schema::create('event_coupon', function (Blueprint $table) {
            $table->foreignUlid('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignUlid('coupon_id')->constrained('coupons')->onDelete('cascade');

            $table->primary(['event_id', 'coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_coupon');
    }
};
