<?php

declare(strict_types=1);

use App\Enums\Gender;
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
        Schema::dropIfExists(table: 'users');
        Schema::dropIfExists(table: 'password_reset_tokens');
        Schema::dropIfExists(table: 'sessions');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            table: 'users',
            callback: function (Blueprint $table): void {
                $table->ulid(column: 'id')->primary()->unique();
                $table->string(column: 'last_name')->nullable();
                $table->string(column: 'first_name')->nullable();
                $table->date(column: 'birthday')->nullable();
                $table->enum(
                    column: 'gender',
                    allowed: array_column(
                        array: Gender::cases(),
                        column_key: 'value',
                    ),
                )->nullable();
                $table->string(column: 'email')
                    ->nullable()
                    ->unique();
                $table->timestamp(column: 'email_verified_at')
                    ->nullable();
                $table->string(column: 'password')->nullable();
                $table->string(column: 'phone')
                    ->unique()
                    ->index();
                $table->string(column: 'address')
                    ->nullable();
                $table->boolean('is_available')
                    ->default(true);
                $table->json('revoked_screens')->nullable();
                $table->rememberToken();
                $table->timestamps();
            },
        );

        Schema::create(
            table: 'password_reset_tokens',
            callback: function (Blueprint $table): void {
                $table->string('email')
                    ->primary();
                $table->string('token');
                $table->timestamp('created_at')
                    ->nullable();
            },
        );

        Schema::create(
            table: 'sessions',
            callback: function (Blueprint $table): void {
                $table->string(column: 'id')
                    ->primary();
                $table->foreignId(column: 'user_id')
                    ->nullable()
                    ->index();
                $table->string(
                    column: 'ip_address',
                    length: 45,
                )
                    ->nullable();
                $table->text(column: 'user_agent')
                    ->nullable();
                $table->longText(column: 'payload');
                $table->integer(column: 'last_activity')
                    ->index();
            },
        );
    }
};
