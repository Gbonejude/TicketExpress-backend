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
        DB::transaction(function () {
            foreach (DB::table('notifications')->get() as $notification) {
                $data = json_decode($notification->data, true);
                if (!is_array($data)) {
                    continue;
                }

                $changed = false;

                if (isset($data['action']) && $data['action'] === 'view_organizer') {
                    $data['url'] = '/organizers';
                    $changed = true;
                } elseif (isset($data['action']) && $data['action'] === 'view_event' && isset($data['event_id'])) {
                    $data['url'] = '/events/' . $data['event_id'];
                    $changed = true;
                } elseif (isset($data['action']) && $data['action'] === 'view_order' && isset($data['order_id'])) {
                    $data['url'] = '/orders/' . $data['order_id'];
                    $changed = true;
                } elseif (isset($data['url'])) {
                    $clean = preg_replace('/^\/(?:admin|organizer)(?=\/|$)/', '', $data['url']);
                    if (str_starts_with($clean, '/organizers')) {
                        $clean = '/organizers';
                    }
                    if ($clean !== $data['url']) {
                        $data['url'] = $clean;
                        $changed = true;
                    }
                }

                if ($changed) {
                    DB::table('notifications')->where('id', $notification->id)->update([
                        'data' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive data normalization does not require reversal
    }
};
