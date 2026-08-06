<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Withdrawal\ProcessWithdrawalAction;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Withdrawal\StoreWithdrawalRequest;
use App\Http\Resources\V1\WithdrawalResource;
use App\Jobs\SendEmailJob;
use App\Mail\WithdrawalProcessedMail;
use App\Models\Organizer;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Payment\PayoutService;
use App\Support\Commission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * @group Withdrawals
 *
 * APIs for managing organizer withdrawal requests
 */
final class WithdrawalController extends Controller
{
    public function __construct(
        private readonly ProcessWithdrawalAction $processWithdrawalAction,
        private readonly PayoutService $payouts,
    ) {}

    /**
     * List withdrawals
     *
     * Un organisateur ne voit que ses propres demandes ; l'administration voit
     * tout. Sans ce cadrage, l'écran des retraits — accordé au rôle
     * organizer-manager — exposait les versements de tous les organisateurs.
     *
     * @queryParam organizer_id Filter by organizer ULID (admin only). Example: 01HXE2K3M4N5P6Q7R8S9T0V1W2
     * @queryParam status Filter by status (pending, approved, rejected, paid). Example: pending
     * @queryParam payment_method Filter by method (flooz, tmoney). Example: flooz
     * @queryParam search Match organizer company name or requester phone. Example: Yas
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
     *       "paymentMethod": "flooz",
     *       "paymentMethodLabel": "Flooz",
     *       "nextStatuses": [{"value": "approved", "label": "Approuvé"}],
     *       "organizer": {},
     *       "createdAt": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Withdrawal::query()
            ->with(['organizer', 'processedBy'])
            ->latest();

        $this->scopeToCaller($query, $request->user());

        if ($request->filled('organizer_id') && $this->isAdmin($request->user())) {
            $query->where('organizer_id', $request->input('organizer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('requester_phone', 'like', "%{$search}%")
                    ->orWhereHas('organizer', function (Builder $o) use ($search): void {
                        $o->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        // Les totaux accompagnent la liste : ils portent sur l'ensemble filtré,
        // pas sur la page affichée — un « en attente : 3 » calculé sur 15 lignes
        // ne veut rien dire.
        $totals = (clone $query)
            ->reorder()
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return WithdrawalResource::collection($query->paginate(15))
            ->additional([
                'stats' => collect(WithdrawalStatus::cases())
                    ->mapWithKeys(static fn (WithdrawalStatus $status): array => [
                        $status->value => [
                            'label' => $status->label(),
                            'count' => (int) ($totals[$status->value]->count ?? 0),
                            'total' => (float) ($totals[$status->value]->total ?? 0),
                        ],
                    ])
                    ->all(),
            ]);
    }

    /**
     * Organizer earnings & balance
     *
     * Returns the organizer's gross ticket revenue, the platform commission, the
     * net revenue, what has already been withdrawn, what is still awaiting a
     * decision, and the amount still available for withdrawal.
     *
     * @urlParam organizer string required The organizer ULID.
     */
    public function earnings(Request $request, string $organizerId): JsonResponse
    {
        $organizer = Organizer::findOrFail($organizerId);

        // Le solde d'un organisateur est une donnée financière : seule
        // l'administration peut lire celui d'autrui.
        if (! $this->isAdmin($request->user())
            && (string) ($request->user()?->organizer?->id) !== (string) $organizer->id) {
            return $this->error(message: 'Accès refusé.', status: 403);
        }

        return $this->success([
            'organizerId' => $organizer->id,
            'grossRevenue' => $organizer->grossRevenue(),
            'commissionRate' => Commission::rate(),
            'commissionAmount' => $organizer->platformCommission(),
            'netRevenue' => $organizer->netRevenue(),
            'totalWithdrawn' => $organizer->totalWithdrawn(),
            'pendingWithdrawn' => $organizer->pendingWithdrawn(),
            'availableBalance' => $organizer->availableBalance(),
        ]);
    }

    /**
     * Create a new withdrawal request
     *
     * Le montant est confronté au solde disponible (voir StoreWithdrawalRequest),
     * et un organisateur ne peut déposer une demande que pour lui-même.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Demande de retrait créée avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1WA",
     *     "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *     "amount": 500000,
     *     "status": "pending",
     *     "paymentMethod": "flooz"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Le montant dépasse le solde disponible de cet organisateur (12 500 FCFA)."
     * }
     */
    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (! $this->isAdmin($user)) {
            $own = $user?->organizer?->id;

            if ($own === null || (string) $data['organizer_id'] !== (string) $own) {
                return $this->error(
                    message: 'Vous ne pouvez demander un retrait que pour votre propre compte organisateur.',
                    status: 403,
                );
            }
        }

        $withdrawal = Withdrawal::create([
            ...$data,
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
     *     "paymentMethod": "flooz",
     *     "organizer": {}
     *   }
     * }
     */
    public function show(string $id): JsonResponse
    {
        $withdrawal = Withdrawal::with(['organizer', 'processedBy'])->findOrFail($id);

        $this->authorize('view', $withdrawal);

        return $this->success(
            data: new WithdrawalResource($withdrawal),
        );
    }

    /**
     * Process a withdrawal (approve, reject, or mark as paid)
     *
     * Administration uniquement, et seules les transitions du circuit sont
     * acceptées : en attente → approuvé → payé, avec un rejet possible tant que
     * rien n'est payé.
     *
     * @bodyParam status string required The new status (approved, rejected, paid). Example: approved
     * @bodyParam notes string Reason or reference, shown to the organizer. Example: Virement Flooz du 12/08
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
     *   "message": "Ce retrait est payé : son statut ne change plus."
     * }
     */
    public function process(Request $request, string $id): JsonResponse
    {
        $withdrawal = Withdrawal::findOrFail($id);

        $this->authorize('process', $withdrawal);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,paid'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // Obligatoire pour clore un retrait : « payé » sans référence de
            // transfert est une affirmation invérifiable.
            'payout_reference' => [
                Rule::requiredIf(fn (): bool => $request->input('status') === 'paid'),
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'payout_reference.required' => 'La référence du transfert est requise pour marquer le retrait payé.',
        ]);

        // Approuver engage la plateforme à verser : on regarde d'abord si le
        // solde marchand le permet. `covers()` rend null quand le solde est
        // illisible (IP non whitelistée) — on laisse alors passer plutôt que de
        // bloquer sur une information qu'on n'a pas.
        if ($validated['status'] === WithdrawalStatus::APPROVED->value
            && $this->payouts->covers($withdrawal) === false) {
            return $this->error(
                message: 'Le solde mobile money de la plateforme ne couvre pas ce montant. Approvisionnez le compte avant d\'approuver.',
                status: 422,
            );
        }

        try {
            $withdrawal = $this->processWithdrawalAction->execute([
                'withdrawal' => $withdrawal,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'payout_reference' => $validated['payout_reference'] ?? null,
                'actor' => $request->user(),
            ]);

            // Le versement à faire est consigné dès l'approbation ; il devient
            // un appel d'API le jour où PayGate en ouvre un (voir PayoutService).
            if ($withdrawal->status === WithdrawalStatus::APPROVED) {
                $this->payouts->transfer($withdrawal);
            }

            $this->notifyOrganizer($withdrawal);

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

        $this->authorize('delete', $withdrawal);

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

    /**
     * Prévient l'organisateur du sort de sa demande.
     *
     * Sans ça, il découvre l'approbation en regardant son téléphone, et un refus
     * jamais — d'où des relances qu'un mail évite. Le mail part sur les trois
     * issues, avec le motif ou la référence du virement.
     */
    private function notifyOrganizer(Withdrawal $withdrawal): void
    {
        $email = $withdrawal->organizer?->user?->email;

        if ($email === null || $email === '') {
            return;
        }

        SendEmailJob::dispatch($email, WithdrawalProcessedMail::class, [$withdrawal]);
    }

    /**
     * Restreint la requête aux retraits que l'appelant a le droit de voir.
     *
     * @param  Builder<Withdrawal>  $query
     */
    private function scopeToCaller(Builder $query, ?User $user): void
    {
        if ($this->isAdmin($user)) {
            return;
        }

        // `whereRaw('1 = 0')` plutôt qu'un 403 : un organisateur sans compte
        // organisateur voit une liste vide, ce qui est la vérité, au lieu d'une
        // erreur sur un écran auquel il a accès.
        $organizerId = $user?->organizer?->id;

        $organizerId === null
            ? $query->whereRaw('1 = 0')
            : $query->where('organizer_id', $organizerId);
    }

    private function isAdmin(?User $user): bool
    {
        return $user !== null && $user->hasAnyRole(['admin', 'super-admin']);
    }
}
