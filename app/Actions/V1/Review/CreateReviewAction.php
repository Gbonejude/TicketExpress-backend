<?php

declare(strict_types=1);

namespace App\Actions\V1\Review;

use App\Actions\Contracts\Action;
use App\Events\Review\ReviewCreatedEvent;
use App\Models\Review;

final class CreateReviewAction implements Action
{
    /**
     * @param  array{
     *     event_id: string,
     *     user_id: string,
     *     rating: int,
     *     comment?: string|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $review = Review::create($data);

        // Dispatch ReviewCreatedEvent
        event(new ReviewCreatedEvent($review));

        return $review;
    }
}
