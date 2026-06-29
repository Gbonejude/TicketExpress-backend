<?php

declare(strict_types=1);

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
        Schema::create('event_occurrences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('max_attendees')->nullable()
                ->comment('Maximum d\'attendees pour cette occurrence (null = illimité)');
            $table->integer('current_attendees')->default(0)
                ->comment('Nombre actuel de tickets vendus');
            $table->string('status')->default('active')
                ->comment('active, sold_out, cancelled');
            $table->text('notes')->nullable()
                ->comment('Notes spécifiques à cette session');
            $table->timestamps();

            $table->index('event_id');
            $table->index('start_date');
            $table->index('status');
        });

        // Add foreign key constraint to ticket_types.occurrence_id
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreign('occurrence_id')
                ->references('id')
                ->on('event_occurrences')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint first
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropForeign(['occurrence_id']);
        });

        Schema::dropIfExists('event_occurrences');
    }
};
