<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Organizer
 */
final class OrganizerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'companyName' => $this->company_name,
            'description' => $this->description,
            'logo' => $this->logo,
            'logoThumbnail' => $this->thumbnail,
            'website' => $this->website,
            'status' => $this->status->value,
            'statusLabel' => $this->status->label(),
            'isActive' => (bool) $this->is_active,
            'rejectionReason' => $this->rejection_reason,

            // Marges de contrôle d'accès appliquées par défaut à ses
            // événements. `null` = valeur d'usine ; le formulaire doit pouvoir
            // afficher ce vide plutôt qu'un zéro.
            'checkinOpenHoursBefore' => $this->checkin_open_hours_before,
            'checkinCloseHoursAfter' => $this->checkin_close_hours_after,
            'user' => new UserResource($this->whenLoaded('user')),
            'eventsCount' => $this->whenCounted('events'),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
