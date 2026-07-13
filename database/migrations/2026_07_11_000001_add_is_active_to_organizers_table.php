<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table): void {
            // Deactivated organizers keep their data but their events are
            // hidden from the public/client side. Reversible from the dashboard.
            $table->boolean('is_active')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
