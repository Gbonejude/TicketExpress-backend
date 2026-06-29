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
        Schema::dropIfExists(table: 'otp_codes');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            table: 'otp_codes',
            callback: function (Blueprint $table): void {
                $table->ulid(column: 'id')
                    ->primary()
                    ->unique();
                $table->string('phone', 20)->index();
                $table->string(column: 'code');
                $table->timestamp(column: 'expires_at')
                    ->nullable();
                $table->unsignedTinyInteger(column: 'attempts')
                    ->default(0);
                $table->timestamp(column: 'verified_at')
                    ->nullable();
                $table->timestamps();
            },
        );
    }
};
