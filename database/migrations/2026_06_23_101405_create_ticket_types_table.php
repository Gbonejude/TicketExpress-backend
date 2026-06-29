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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained('events')->onDelete('cascade');
            $table->ulid('occurrence_id')->nullable()->comment('Si null, ticket valide pour toutes les occurrences');
            $table->string('name'); // VIP, Standard, etc.
            $table->text('description')->nullable();
            $table->json('benefits')->nullable()->comment('Avantages inclus (JSON array)');
            $table->string('location_details')->nullable()->comment('Détails sur l\'emplacement (ex: Tribunes supérieures)');
            $table->boolean('is_featured')->default(false)->comment('Ticket mis en avant');
            $table->integer('sort_order')->default(0)->comment('Ordre d\'affichage (plus petit = affiché en premier)');
            $table->decimal('price', 10, 2);
            $table->decimal('promotional_price', 10, 2)->nullable()->comment('Prix promotionnel (si NULL, pas de promotion)');
            $table->dateTime('promotion_start_date')->nullable()->comment('Date de début de la promotion');
            $table->dateTime('promotion_end_date')->nullable()->comment('Date de fin de la promotion');
            $table->integer('quantity');
            $table->integer('sold_quantity')->default(0);
            $table->dateTime('sale_start_date')->nullable();
            $table->dateTime('sale_end_date')->nullable();
            $table->timestamps();

            $table->index('event_id');
            $table->index('occurrence_id');
            $table->index('promotion_start_date');
            $table->index('promotion_end_date');
            $table->index(['event_id', 'sort_order', 'is_featured'], 'idx_ticket_types_display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
