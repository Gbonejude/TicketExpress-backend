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
            // Refund policy set by the organizer: whether refunds are allowed
            // and how many days before the event a refund can still be requested.
            $table->boolean('refund_allowed')->default(true)->after('online_url');
            $table->unsignedInteger('refund_days_before')->default(0)->after('refund_allowed');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['refund_allowed', 'refund_days_before']);
        });
    }
};
