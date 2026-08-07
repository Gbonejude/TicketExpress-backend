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
        Schema::create('organizers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected

            $table->boolean('is_active')->default(true);

            $table->text('rejection_reason')->nullable();

            $table->decimal('checkin_open_hours_before', 5, 2)->nullable();
            $table->decimal('checkin_close_hours_after', 5, 2)->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
