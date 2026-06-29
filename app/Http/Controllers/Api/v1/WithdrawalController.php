<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Withdrawal\ProcessWithdrawalAction;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Withdrawal\StoreWithdrawalRequest;
use App\Http\Resources\V1\WithdrawalResource;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Withdrawals
 *
 * APIs for managing organizer withdrawal requests
 */
final class WithdrawalController extends Controller
{
    public function __construct(
        private readonly ProcessWithdrawalAction $processWithdrawalAction,
    ) {}

    /**
     * List withdrawals
     *
     * @queryParam organizer_id Filter by organizer ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W2
     * @queryParam status Filter by status (pending, approved, rejected, paid). Example: pending
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": [
     *     {
     *       "id": "01HXE2K3M4N5P6Q7R8S9T0V1WA",
     *       "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *       "amount": 500000,
     *       "status": "pending",
     *       "statusLabel": "En attente",
     *       "paymentMethod": "bank_transfer",
     *       "organizer": {...},
     *       "createdAt": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Withdrawal::query()
            ->with('organizer')
            ->latest();

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->input('organizer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $withdrawals = $query->paginate(15);

        return WithdrawalResource::collection($withdrawals);
    }

    /**
     * Create a new withdrawal request
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Demande de retrait créée avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1WA",
     *     "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *     "amount": 500000,
     *     "status": "pending",
     *     "paymentMethod": "bank_transfer"
     *   }
     * }
     */
    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        $withdrawal = Withdrawal::create([
            ...$request->validated(),
            'status' => WithdrawalStatus::PENDING,
        ]);

        return $this->created(
            data: new WithdrawalResource($withdrawal->load('organizer')),
            message: 'Demande de retrait créée avec succès.',
        );
    }

    /**
     * Show a single withdrawal
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1WA",
     *     "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *     "amount": 500000,
     *     "status": "pending",
     *     "paymentMethod": "bank_transfer",
     *     "organizer": {...}
     *   }
     * }
     */
    public function show(string $id): JsonResponse
    {
        $withdrawal = Withdrawal::with('organizer')->findOrFail($id);

        return $this->success(
            data: new WithdrawalResource($withdrawal),
        );
    }

    /**
     * Process a withdrawal (approve, reject, or mark as paid)
     *
     * @bodyParam status string required The new status (approved, rejected, paid). Example: approved
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Retrait traité avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1WA",
     *     "status": "approved",
     *     "statusLabel": "Approuvé"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Ce retrait a déjà été payé."
     * }
     */
    public function process(Request $request, string $id): JsonResponse
    {
        $withdrawal = Withdrawal::findOrFail($id);

        $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,paid'],
        ]);

        try {
            /** @var array{withdrawal: Withdrawal, status: string} $data */
            $data = [
                'withdrawal' => $withdrawal,
                'status' => $request->input('status'),
            ];

            $withdrawal = $this->processWithdrawalAction->execute($data);

            return $this->success(
                data: new WithdrawalResource($withdrawal),
                message: 'Retrait traité avec succès.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Delete a withdrawal request
     *
     * Only pending withdrawals can be deleted.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Demande de retrait supprimée avec succès.",
     *   "data": null
     * }
     */
    public function destroy(string $id): JsonResponse
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== WithdrawalStatus::PENDING) {
            return $this->error(
                message: 'Seules les demandes en attente peuvent être supprimées.',
                status: 422,
            );
        }

        $withdrawal->delete();

        return $this->success(
            message: 'Demande de retrait supprimée avec succès.',
        );
    }
}
