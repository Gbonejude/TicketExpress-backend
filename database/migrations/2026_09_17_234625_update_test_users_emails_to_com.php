<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappings = [
            'superadmin@test.tg' => 'superadmin@test.com',
            'admin@test.tg' => 'admin@test.com',
            'organizer@test.tg' => 'organizer@test.com',
            'client@test.tg' => 'client@test.com',
        ];

        foreach ($mappings as $oldEmail => $newEmail) {
            $oldUser = DB::table('users')->where('email', $oldEmail)->first();
            $newExists = DB::table('users')->where('email', $newEmail)->exists();

            if ($oldUser && ! $newExists) {
                DB::table('users')->where('id', $oldUser->id)->update([
                    'email' => $newEmail,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $mappings = [
            'superadmin@test.com' => 'superadmin@test.tg',
            'admin@test.com' => 'admin@test.tg',
            'organizer@test.com' => 'organizer@test.tg',
            'client@test.com' => 'client@test.tg',
        ];

        foreach ($mappings as $currentEmail => $revertedEmail) {
            $currentUser = DB::table('users')->where('email', $currentEmail)->first();
            $revertedExists = DB::table('users')->where('email', $revertedEmail)->exists();

            if ($currentUser && ! $revertedExists) {
                DB::table('users')->where('id', $currentUser->id)->update([
                    'email' => $revertedEmail,
                ]);
            }
        }
    }
};
