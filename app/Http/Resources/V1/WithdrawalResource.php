<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Withdrawal
 */
final class WithdrawalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->resource->status;

        return [
            'id' => $this->id,
            'organizerId' => $this->organizer_id,
            'requesterPhone' => $this->requester_phone,
            'amount' => $this->amount,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'paymentMethod' => $this->payment_method,
            // Libellé calculé ici, comme pour les paiements : la liste affichait
            // « flooz » et « tmoney » bruts, en minuscules.
            'paymentMethodLabel' => $this->paymentMethodLabel(),

            // Le circuit est décidé par le domaine, pas par l'interface : celle-ci
            // n'a plus qu'à proposer ce que cette liste contient. Un retrait payé
            // ou rejeté la renvoie vide, donc plus aucune action possible.
            'nextStatuses' => array_map(
                static fn (\App\Enums\WithdrawalStatus $next): array => [
                    'value' => $next->value,
                    'label' => $next->label(),
                ],
                $status->nextStatuses(),
            ),
            'isFinal' => $status->isFinal(),

            'notes' => $this->notes,
            'payoutReference' => $this->payout_reference,
            'processedAt' => $this->processed_at ? new DateTimeResource($this->processed_at) : null,
            'processedBy' => $this->whenLoaded(
                'processedBy',
                fn (): ?string => $this->processedBy === null
                    ? null
                    : $this->processedBy->first_name.' '.$this->processedBy->last_name,
            ),

            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }

    /**
     * Les deux opérateurs mobiles acceptés (voir `StoreWithdrawalRequest`).
     * « Mix by Yas » est le nom commercial de l'ancien T-Money.
     */
    private function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'flooz' => 'Flooz',
            'tmoney' => 'Mix by Yas',
            default => (string) $this->payment_method,
        };
    }
}
