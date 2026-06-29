<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'personal_access_tokens');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            table: 'personal_access_tokens',
            callback: function (Blueprint $table): void {
                $table->ulid(column: 'id')
                    ->primary();
                $table->ulidMorphs(name: 'tokenable');
                $table->string(column: 'name');
                $table->string(
                    column: 'token',
                    length: 64,
                )->unique();
                $table->text(column: 'abilities')
                    ->nullable();
                $table->timestamp(column: 'last_used_at')
                    ->nullable();
                $table->timestamp(column: 'expires_at')
                    ->nullable();
                $table->timestamps();
            },
        );
    }
};
