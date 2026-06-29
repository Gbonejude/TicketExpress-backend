<?php

declare(strict_types=1);

namespace App\Actions\V1\Review;

use App\Actions\Contracts\Action;
use App\Models\Review;

final class UpdateReviewAction implements Action
{
    /**
     * @param  array{
     *     model: Review,
     *     rating?: int,
     *     comment?: string|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $review = $data['model'];
        $review->update(collect($data)->except(['model'])->toArray());

        return $review->fresh();
    }
}
