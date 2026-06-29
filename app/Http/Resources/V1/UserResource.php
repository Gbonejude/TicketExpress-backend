<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'lastName' => $this->last_name,
            'firstName' => $this->first_name,
            'fullName' => $this->first_name.' '.$this->last_name,
            'birthday' => $this->birthday,
            'gender' => $this->gender,
            'role' => $this->getRoleNames()->first(),
            'address' => $this->address,
            'image' => $this->getFirstMediaUrl(collectionName: 'users'),
            'thumbnail' => $this->getFirstMediaUrl(
                collectionName: 'users',
                conversionName: 'thumb',
            ),
            'organizer' => $this->whenLoaded('organizer', fn () => new OrganizerResource($this->organizer)),

            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
        ];
    }
}
