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
        Schema::create('check_ins', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignUlid('scanned_by')->constrained('users')->onDelete('restrict');
            $table->dateTime('scanned_at');
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->index('ticket_id');
            $table->index('scanned_by');
            $table->index('scanned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
