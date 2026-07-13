<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Order;
use App\Services\TicketDownloadService;
use App\Support\Commission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
final class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get download link if exists
        $downloadLink = $this->resource->relationLoaded('downloadLink')
            ? $this->downloadLink
            : $this->resource->downloadLink;

        $downloadData = null;

        if ($downloadLink) {
            $downloadService = app(TicketDownloadService::class);

            $downloadData = [
                'downloadUrl' => $downloadService->getDownloadUrl($downloadLink),
                'qrImageUrls' => $downloadService->getQrImageUrls($downloadLink),
                'whatsappLink' => $downloadService->getWhatsAppLink($this->resource, $downloadLink),
                'expiresAt' => new DateTimeResource($downloadLink->expires_at),
                'downloadCount' => $downloadLink->download_count,
                'maxDownloads' => $downloadLink->max_downloads,
                'isValid' => $downloadLink->isValid(),
                'isExpired' => $downloadLink->isExpired(),
            ];
        }

        return [
            'id' => $this->id,
            'orderNumber' => $this->order_number,
            'userId' => $this->user_id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'fullName' => $this->first_name.' '.$this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'totalAmount' => $this->total_amount,
            'commissionRate' => Commission::rate(),
            'commissionAmount' => Commission::amountFor((float) $this->total_amount),
            'netAmount' => Commission::netFor((float) $this->total_amount),
            'status' => $this->resource->status->value,
            'statusLabel' => $this->resource->status->label(),
            'paymentMethod' => $this->payment_method,
            'deliveryMethod' => $this->resource->delivery_method->value,
            'deliveryMethodLabel' => $this->resource->delivery_method->label(),
            'downloads' => $downloadData,
            'user' => new UserResource($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'itemsCount' => $this->whenCounted('items'),
            'ticketsCount' => $this->whenCounted('tickets'),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
