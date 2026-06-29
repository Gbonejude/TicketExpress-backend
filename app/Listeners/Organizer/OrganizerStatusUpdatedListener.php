<?php

declare(strict_types=1);

namespace App\Listeners\Organizer;

use App\Enums\OrganizerStatus;
use App\Events\Organizer\OrganizerStatusUpdatedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\OrganizerApprovedMail;
use App\Mail\OrganizerRejectedMail;
use App\Models\Organizer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for OrganizerStatusUpdatedEvent.
 * Sends approval or rejection email based on status change.
 */
final class OrganizerStatusUpdatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'emails';

    /**
     * Handle the event.
     */
    public function handle(OrganizerStatusUpdatedEvent $event): void
    {
        try {
            // Extract organizer using Reflection (private readonly property)
            $reflection = new ReflectionClass($event);
            $property = $reflection->getProperty('organizer');
            $property->setAccessible(true);
            $organizer = $property->getValue($event);

            if (! $organizer) {
                Log::warning('Organizer not found in OrganizerStatusUpdatedEvent');

                return;
            }

            // Load user relation
            $organizer->load('user');

            // Send email based on status
            match ($organizer->status) {
                OrganizerStatus::APPROVED => $this->sendApprovalEmail($organizer),
                OrganizerStatus::REJECTED => $this->sendRejectionEmail($organizer),
                default => null,
            };
        } catch (Exception $e) {
            Log::error('Échec du traitement du changement de statut organisateur', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send approval email to organizer.
     */
    private function sendApprovalEmail(Organizer $organizer): void
    {
        try {
            SendEmailJob::dispatch(
                to: $organizer->user->email,
                mailableClass: OrganizerApprovedMail::class,
                mailableData: [$organizer],
            );

            Log::info('Email d\'approbation organisateur envoyé via Job', [
                'organizer_id' => $organizer->id,
                'email' => $organizer->user->email,
            ]);
        } catch (Exception $e) {
            Log::error('Échec envoi email d\'approbation', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send rejection email to organizer.
     */
    private function sendRejectionEmail(Organizer $organizer): void
    {
        try {
            // You can add a rejection_reason field to organizers table
            // For now, use a default message
            $rejectionReason = $organizer->rejection_reason ?? null;

            SendEmailJob::dispatch(
                to: $organizer->user->email,
                mailableClass: OrganizerRejectedMail::class,
                mailableData: [$organizer, $rejectionReason],
            );

            Log::info('Email de rejet organisateur envoyé', [
                'organizer_id' => $organizer->id,
                'email' => $organizer->user->email,
            ]);
        } catch (Exception $e) {
            Log::error('Échec envoi email de rejet', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
