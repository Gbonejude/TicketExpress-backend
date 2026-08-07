<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Organizer\CreateOrganizerAction;
use App\Actions\V1\Organizer\DeleteOrganizerAction;
use App\Actions\V1\Organizer\UpdateOrganizerAction;
use App\Enums\OrganizerStatus;
use App\Events\Organizer\OrganizerStatusUpdatedEvent;
use App\Events\ResourceChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Organizer\StoreOrganizerRequest;
use App\Http\Requests\V1\Organizer\UpdateOrganizerRequest;
use App\Http\Resources\V1\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * @group Organizers
 *
 * APIs for managing event organizers
 */
final class OrganizerController extends Controller
{
    /**
     * List Organizers
     *
     * Get a listing of the organizers.
     *
     * @header Accept-Language en
     *
     * @apiResourceCollection \App\Http\Resources\V1\OrganizerResource
     *
     * @apiResourceModel \App\Models\Organizer
     */
    /**
     * My organizer profile
     *
     * La fiche de l'organisateur connecté. Distincte de `organizers/{id}` :
     * celle-là est réservée à l'administration, alors qu'un organisateur doit
     * pouvoir consulter la sienne sans avoir accès à l'écran de gestion.
     *
     * @authenticated
     *
     * @response 404 scenario="Compte sans profil organisateur" {
     *   "success": false,
     *   "message": "Aucun profil organisateur n'est rattaché à ce compte."
     * }
     */
    public function showMine(Request $request): JsonResponse
    {
        $organizer = $request->user()?->organizer;

        if ($organizer === null) {
            return $this->error(
                message: 'Aucun profil organisateur n\'est rattaché à ce compte.',
                status: 404,
            );
        }

        return $this->success(new OrganizerResource($organizer));
    }

    /**
     * Update my check-in window
     *
     * Les heures d'ouverture et de fermeture du contrôle d'accès appliquées par
     * défaut à tous ses événements.
     *
     * Volontairement limité à ces deux champs : le nom, le logo et surtout le
     * *statut* d'un organisateur relèvent de l'administration, et les exposer
     * ici laisserait un organisateur s'approuver lui-même.
     *
     * @authenticated
     *
     * @bodyParam checkin_open_hours_before number Hours before an event starts when check-in opens; null to fall back on the factory value. Example: 6
     * @bodyParam checkin_close_hours_after number Hours after an event ends when check-in closes. Example: 4
     */
    public function updateMine(Request $request): JsonResponse
    {
        $organizer = $request->user()?->organizer;

        if ($organizer === null) {
            return $this->error(
                message: 'Aucun profil organisateur n\'est rattaché à ce compte.',
                status: 404,
            );
        }

        $validated = $request->validate([
            'checkin_open_hours_before' => ['present', 'nullable', 'numeric', 'min:0', 'max:168'],
            'checkin_close_hours_after' => ['present', 'nullable', 'numeric', 'min:0', 'max:168'],
        ], [
            'checkin_open_hours_before.max' => 'L\'ouverture anticipée ne peut pas dépasser 168 heures (7 jours).',
            'checkin_close_hours_after.max' => 'La tolérance après la fin ne peut pas dépasser 168 heures (7 jours).',
        ]);

        $organizer->update($validated);

        return $this->success(
            new OrganizerResource($organizer->fresh()),
            'Contrôle d\'accès mis à jour.',
        );
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Organizer::query()
            ->with('user')
            ->withCount('events')
            ->latest();

        // Côté visiteur, la liste ne contient que les organisateurs approuvés et
        // actifs — comme la liste des événements, qui écarte déjà ceux d'un
        // organisateur désactivé. Un dossier encore en attente, ou refusé, n'a
        // rien à faire dans l'annuaire public : il y apparaissait comme une fiche
        // sans le moindre événement, et le refus était rendu public alors qu'il
        // ne concerne que le demandeur et l'administration.
        //
        // Le back-office, lui, continue de tout voir : c'est là qu'on approuve.
        if ($request->user() === null) {
            $query->where('status', OrganizerStatus::APPROVED)
                ->where('is_active', true);
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        // `per_page` est accepté (borné à 100) pour les listes déroulantes du
        // back-office : le formulaire de retrait ne pouvait choisir que parmi
        // les 15 premiers organisateurs, les suivants étaient inatteignables.
        $perPage = min((int) $request->input('per_page', 15), 100);

        $organizers = $query->paginate(max($perPage, 1));

        return OrganizerResource::collection($organizers);
    }

    /**
     * Store Organizer
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Organizer created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "userId": "01jkp5yy...",
     *     "companyName": "EventPro Inc"
     *   }
     * }
     */
    public function store(StoreOrganizerRequest $request, CreateOrganizerAction $action): JsonResponse
    {
        /** @var array{user_id: string, company_name: string, description?: string|null, website?: string|null, status?: string, logo?: UploadedFile|null} $data */
        $data = $request->validated();

        $organizer = $action->execute($data);

        return $this->created(new OrganizerResource($organizer));
    }

    /**
     * Show Organizer
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @apiResource \App\Http\Resources\V1\OrganizerResource
     *
     * @apiResourceModel \App\Models\Organizer
     */
    public function show(Organizer $id): JsonResponse
    {
        $id->load('user')->loadCount('events');

        return $this->success(new OrganizerResource($id));
    }

    /**
     * Update Organizer
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Organizer updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "companyName": "EventPro Inc Updated"
     *   }
     * }
     */
    public function update(UpdateOrganizerRequest $request, Organizer $id, UpdateOrganizerAction $action): JsonResponse
    {
        /** @var array{organizer: Organizer, user_id?: string, company_name?: string, description?: string|null, website?: string|null, status?: string, logo?: UploadedFile|null} $data */
        $data = [
            'organizer' => $id,
            ...$request->validated(),
        ];

        $organizer = $action->execute($data);

        ResourceChangedEvent::dispatch('organizers', 'updated', $organizer->id, $organizer->company_name);

        return $this->success(new OrganizerResource($organizer));
    }

    /**
     * Delete Organizer
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Organizer $id, DeleteOrganizerAction $action): JsonResponse
    {
        $organizerId = $id->id;
        $action->execute(['organizer' => $id]);

        ResourceChangedEvent::dispatch('organizers', 'deleted', $organizerId);

        return $this->noContent();
    }

    /**
     * Approve Organizer
     *
     * Approves a pending organizer registration. Clears any previous rejection
     * reason.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     */
    public function approve(Organizer $id): JsonResponse
    {
        $id->update([
            'status' => OrganizerStatus::APPROVED,
            'rejection_reason' => null,
            // Approving is what opens the back-office; leaving `is_active`
            // false would send them an e-mail inviting them into a dead end.
            'is_active' => true,
        ]);

        // This is what sends the approval e-mail — the one carrying the
        // dashboard URL. `OrganizerStatusUpdatedListener` has always known how
        // to send it; nothing was dispatching the event, so it never fired.
        OrganizerStatusUpdatedEvent::dispatch($id->fresh()->load('user'));

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }

    /**
     * Reject Organizer
     *
     * Rejects an organizer registration with an optional reason shown to the
     * organizer.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @bodyParam reason string The reason for the rejection. Example: Documents manquants.
     */
    public function reject(Request $request, Organizer $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $id->update([
            'status' => OrganizerStatus::REJECTED,
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        // Sends the rejection e-mail, with the reason the administrator gave.
        OrganizerStatusUpdatedEvent::dispatch($id->fresh()->load('user'));

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }

    /**
     * Activate Organizer
     *
     * Re-activates a deactivated organizer. Their events become visible on the
     * client side again.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     */
    public function activate(Organizer $id): JsonResponse
    {
        $id->update(['is_active' => true]);

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }

    /**
     * Deactivate Organizer
     *
     * Deactivates an organizer. Their data is kept but their events are hidden
     * from the client side until re-activated.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     */
    public function deactivate(Organizer $id): JsonResponse
    {
        $id->update(['is_active' => false]);

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }
}
