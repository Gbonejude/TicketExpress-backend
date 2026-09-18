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
            'superadmin@test.com' => 'superadmin@gmail.com',
            'superadmin@test.tg' => 'superadmin@gmail.com',
            'admin@test.com' => 'admin@gmail.com',
            'admin@test.tg' => 'admin@gmail.com',
            'organizer@test.com' => 'organizer@gmail.com',
            'organizer@test.tg' => 'organizer@gmail.com',
            'client@test.com' => 'client@gmail.com',
            'client@test.tg' => 'client@gmail.com',
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
            'superadmin@gmail.com' => 'superadmin@test.com',
            'admin@gmail.com' => 'admin@test.com',
            'organizer@gmail.com' => 'organizer@test.com',
            'client@gmail.com' => 'client@test.com',
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
