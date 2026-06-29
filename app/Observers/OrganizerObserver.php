<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Organizer;
use Illuminate\Support\Facades\Log;

final class OrganizerObserver
{
    public function created(Organizer $organizer): void
    {
        Log::info('Organizer created', ['organizer_id' => $organizer->id]);
        // Dispatch events, invalidate cache, etc.
    }

    public function updated(Organizer $organizer): void
    {
        Log::info('Organizer updated', ['organizer_id' => $organizer->id]);
    }

    public function deleted(Organizer $organizer): void
    {
        Log::info('Organizer deleted', ['organizer_id' => $organizer->id]);
    }
}
