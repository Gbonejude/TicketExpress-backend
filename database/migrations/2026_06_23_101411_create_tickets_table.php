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
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUlid('ticket_type_id')->constrained('ticket_types')->onDelete('restrict');
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->string('qr_code')->unique();
            $table->string('ticket_number')->unique();
            $table->string('status')->default('valid');
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->string('checked_in_by')->nullable();
            $table->string('access_method')->default('physical');
            $table->text('online_access_link')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('ticket_type_id');
            $table->index('qr_code');
            $table->index('ticket_number');
            $table->index('status');
            $table->index('attendee_email');
            $table->index('access_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
