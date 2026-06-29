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
        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organizer_id')->constrained('organizers')->onDelete('cascade');
            $table->foreignUlid('category_id')->constrained('event_categories')->onDelete('restrict');
            $table->foreignUlid('venue_id')->nullable()->constrained('venues')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('max_attendees')->nullable();
            $table->string('status')->default('draft'); // draft, published, cancelled, finished
            $table->timestamp('published_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('organizer_id');
            $table->index('category_id');
            $table->index('venue_id');
            $table->index('slug');
            $table->index('status');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
