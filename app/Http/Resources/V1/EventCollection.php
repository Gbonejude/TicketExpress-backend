<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class EventCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'perPage' => $this->resource->perPage(),
                'currentPage' => $this->resource->currentPage(),
                'totalPages' => $this->resource->lastPage(),
            ],
        ];
    }
}
