<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Physical events use a venue; online events use online_url instead.
            $table->string('event_type')->default('physical')->after('status');
            $table->string('online_url')->nullable()->after('event_type');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['event_type', 'online_url']);
        });
    }
};
