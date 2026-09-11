<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PromotionResource;
use App\Models\TicketType;
use App\Support\CatalogueAudience;
use App\Support\TicketDateWindow;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

/**
 * @group Promotions
 *
 * A promotion is a reduced price on a ticket type, active for a date range.
 * Promotions are stored on the ticket type itself; these endpoints manage them
 * from a single back-office screen.
 *
 * @authenticated
 */
final class PromotionController extends Controller
{
    /**
     * List promotions
     *
     * Lists every ticket type that currently has a promotion configured.
     *
     * @queryParam state string Filter by state (active, scheduled, expired). Example: active
     * @queryParam event_id string Only promotions for this event. Example: 01HXE...
     * @queryParam search string Match ticket type name or event title. Example: VIP
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $now = now();

        $query = TicketType::query()
            ->whereNotNull('promotional_price')
            ->with('event')
            ->latest();

        // Cloisonnement : un organisateur ne voit que les promotions de ses
        // propres événements. L'administration voit tout.
        $scopedOrganizerId = CatalogueAudience::scopedOrganizerId($request);

        if ($scopedOrganizerId !== null) {
            $query->whereHas('event', fn (Builder $q) => $q->where('organizer_id', $scopedOrganizerId));
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        match ($request->input('state')) {
            'active' => $query
                ->where(fn (Builder $q) => $q->whereNull('promotion_start_date')->orWhere('promotion_start_date', '<=', $now))
                ->where(fn (Builder $q) => $q->whereNull('promotion_end_date')->orWhere('promotion_end_date', '>=', $now)),
            'scheduled' => $query->where('promotion_start_date', '>', $now),
            'expired' => $query->where('promotion_end_date', '<', $now),
            default => null,
        };

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('event', function (Builder $e) use ($search): void {
                        $e->where('title', 'like', "%{$search}%");
                    });
            });
        }

        return PromotionResource::collection($query->paginate(15));
    }

    /**
     * Set / update a promotion
     *
     * Configures the promotional price and window on a ticket type.
     *
     * @urlParam ticketType string required The ticket type ID (ULID).
     *
     * @bodyParam promotional_price number required The reduced price (must be lower than the normal price). Example: 5000
     * @bodyParam promotion_start_date date required When the promotion starts. Example: 2026-08-01
     * @bodyParam promotion_end_date date required When the promotion ends. Example: 2026-08-31
     */
    public function update(Request $request, TicketType $ticketType): JsonResponse
    {
        $this->authorize('update', $ticketType);

        $validator = Validator::make($request->all(), [
            'promotional_price' => ['required', 'numeric', 'min:0', 'lt:'.$ticketType->price],
            'promotion_start_date' => ['required', 'date'],
            'promotion_end_date' => ['required', 'date', 'after:promotion_start_date'],
        ], [
            'promotional_price.lt' => 'Le prix promotionnel doit être inférieur au prix normal.',
            'promotion_end_date.after' => 'La date de fin doit être après la date de début.',
        ]);

        // La promotion vit dans la fenêtre de vente du billet, elle-même dans la
        // période de l'événement.
        $validator->after(function (ValidatorContract $validator) use ($request, $ticketType): void {
            TicketDateWindow::check($validator, $ticketType->event, [
                'sale_start' => $ticketType->sale_start_date,
                'sale_end' => $ticketType->sale_end_date,
                'promo_start' => $request->date('promotion_start_date'),
                'promo_end' => $request->date('promotion_end_date'),
            ]);
        });

        /** @var array<string, mixed> $validated */
        $validated = $validator->validate();

        $ticketType->update($validated);

        return $this->success(new PromotionResource($ticketType->load('event')));
    }

    /**
     * Remove a promotion
     *
     * Clears the promotion from a ticket type (restores the normal price).
     *
     * @urlParam ticketType string required The ticket type ID (ULID).
     */
    public function destroy(TicketType $ticketType): JsonResponse
    {
        $this->authorize('delete', $ticketType);

        $ticketType->update([
            'promotional_price' => null,
            'promotion_start_date' => null,
            'promotion_end_date' => null,
        ]);

        return $this->noContent();
    }
}
