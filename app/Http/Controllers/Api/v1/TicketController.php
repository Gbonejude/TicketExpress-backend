<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Ticket\CheckInTicketAction;
use App\Actions\V1\Ticket\RefundTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Ticket\RefundTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Tickets
 *
 * APIs for managing tickets and refunds
 */
final class TicketController extends Controller
{
    public function __construct(
        private readonly RefundTicketAction $refundTicketAction,
        private readonly CheckInTicketAction $checkInTicketAction,
    ) {}

    /**
     * Check in a ticket (scan QR code)
     *
     * Scan and validate a ticket for event entry.
     * - Ticket must be ACTIVE
     * - Cannot scan twice
     * - Records who performed the check-in
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ticket scanné avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W8",
     *     "ticketNumber": "TKT-2024-001",
     *     "status": "active",
     *     "checkedIn": true,
     *     "checkedInAt": "2024-01-15T10:00:00.000000Z",
     *     "checkedInBy": "Staff #123"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Ce ticket a déjà été scanné."
     * }
     */
    public function checkIn(Request $request, Ticket $ticket): JsonResponse
    {
        try {
            $checkedInTicket = $this->checkInTicketAction->execute([
                'ticket' => $ticket,
                'checked_in_by' => $request->input('checked_in_by'),
            ]);

            return $this->success(
                data: new TicketResource($checkedInTicket),
                message: 'Ticket scanné avec succès.',
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Refund a ticket
     *
     * Request a refund for a ticket. Subject to refund policy:
     * - Must be at least 30 days before event
     * - No coupon was used on the order
     * - Automatic refund if event is cancelled
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ticket remboursé avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W8",
     *     "ticketNumber": "TKT-2024-001",
     *     "status": "refunded",
     *     "statusLabel": "Remboursé",
     *     "refundReason": "Demande de remboursement client",
     *     "refundedAt": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Les remboursements doivent être demandés au moins 30 jours avant l'événement."
     * }
     */
    public function refund(RefundTicketRequest $request, Ticket $ticket): JsonResponse
    {
        try {
            $user = $request->user();
            $isAdmin = $user && $user->hasRole('admin');

            $refundedTicket = $this->refundTicketAction->execute([
                'ticket' => $ticket,
                'reason' => $request->input('reason'),
                'force_refund' => $isAdmin && $request->input('force_refund', false),
            ]);

            return $this->success(
                data: new TicketResource($refundedTicket),
                message: 'Ticket remboursé avec succès.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }
}
